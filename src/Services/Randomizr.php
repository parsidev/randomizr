<?php namespace Parsidev\Support\Services;

use Parsidev\Support\Exceptions\RandomizrException;

class Randomizr {

    /**
     * Contains all available charsets.
     *
     * @var array
     */
	protected $charsets = array();

    /**
     * Contains all available aliases.
     *
     * @var array
     */
    protected $aliases = array();

    /**
     * Stores already resolved aliases.
     *
     * @var array
     */
    protected $resolved_aliases = array();

    /**
     * Holds the currently used/composed charset.
     *
     * @var string
     */
    protected $composed_charset = '';

    /**
     * Contains the transformer configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Contains an instance of the Laravel DB query builder (if in a Laravel
     * context).
     *
     * @var \Illuminate\Database\DatabaseManager|bool
     */
    protected $db;

    /**
     * Construct the class.
     *
     * @param array $config
     * @param \Illuminate\Database\DatabaseManager|bool $db
     */
	public function __construct( $config, $db = false )
    {
        $this->config   = $config;
        $this->db       = $db;

        // Register all available character sets
        $this->charsets = $this->config['charsets'];
        $this->aliases  = $this->config['aliases'];
    }


    //////////////////////////////////////////////////////////////////////////
    // Charset requests
    //////////////////////////////////////////////////////////////////////////

    /**
     * Catch undefined methods to enable magic methods.
     *
     * @param $method
     * @param $args
     * @return $this
     */
    public function __call( $method, $args )
    {
        $this->composed_charset = '';

        $this->resolveRequestedCharset( $method );

        return $this;
    }

    /**
     * Resolves the requested charsets (type) by splitting on camel or snake
     * cases. This means combining charsets and aliases is possible:
     * 'alpha_num' will merge the 'alpha' alias with the 'num' charset.
     *
     * @param $requested
     * @return void
     * @throws RandomizrException
     */
    protected function resolveRequestedCharset( $requested )
    {
        $charsets = explode('_', snake_case($requested));

        foreach ( $charsets as $name )
        {
            if ( $this->isCharset($name) ) $this->composed_charset .= $this->charsets[$name];

            else if ( $this->isAlias($name) ) $this->composed_charset .= $this->resolveAlias($name);

            else throw new RandomizrException('undefinedCharsetOrAlias', array($name));
        }
    }


    //////////////////////////////////////////////////////////////////////////
    // Aliases
    //////////////////////////////////////////////////////////////////////////

    /**
     * Resolves the alias piping.
     *
     * @param string $name
     * @return string
     */
    private function resolveAlias( $name )
    {
        // Check if the alias has already been resolved
        if ( ! isset($this->resolved_aliases[$name]))
        {
            $resolved_alias = '';

            foreach ( explode('|', $this->aliases[$name]) as $components )
            {
                $resolved_alias .= $this->resolveAliasComponent($components);
            }

            // Remember the resolved alias
            $this->resolved_aliases[$name] = $resolved_alias;
        }

        return $this->resolved_aliases[$name];
    }

    /**
     * Resolves the alias components (strings, functions, aliases, ...)
     *
     * @param $component
     * @return string
     * @throws RandomizrException
     */
    private function resolveAliasComponent( $component )
    {
        foreach (array('String', 'Function', 'Alias', 'Charset') as $handler)
        {
            $resolved = $this->{'handle'.$handler.'inAlias'}($component);

            if ( $resolved !== false ) return $resolved;
        }

        throw new RandomizrException('couldNotResolveAliasComponent', array($component));
    }

    /**
     * Handles a raw string given in an alias config value.
     *
     * @param $charset
     * @return string|bool
     */
    protected function handleStringInAlias( $charset )
    {
        if ( str_starts_with("'", $charset) && str_ends_with("'", $charset) )
        {
            return rtrim( ltrim($charset, "'"), "'" );
        }

        // Numbers don't need to be wrapped between quotes
        if ( is_numeric($charset) ) return $charset;

        return false;
    }

    /**
     * Handles string functions (accepting 1 argument) requests in an alias
     * config value.
     *
     * @param $alias
     * @return string|bool
     */
    protected function handleFunctionInAlias( $alias )
    {
        if ( count($method = explode(':', $alias)) > 1 )
        {
            list($method, $args) = $method;

            foreach ($args = explode(',', $args) as $key => $arg)
            {
                // Functions can transform strings, aliases or charsets, so we need
                // to resolve its components like we did for the current alias
                $args[$key] = $this->resolveAliasComponent($arg);
            }

            return call_user_func_array($method, $args);
        }

        return false;
    }

    /**
     * Handles aliases in aliases.
     *
     * @param $alias
     * @return bool|string
     */
    protected function handleAliasInAlias( $alias )
    {
        if ( $this->isAlias($alias) ) return $this->resolveAlias($alias);

        return false;
    }

    /**
     * Handles charsets in aliases.
     *
     * @param $alias
     * @return bool|string
     */
    protected function handleCharsetInAlias( $alias )
    {
        if ( $this->isCharset($alias) ) return $this->charsets[$alias];

        return false;
    }


    //////////////////////////////////////////////////////////////////////////
    // Accessors
    //////////////////////////////////////////////////////////////////////////

    /**
     * Makes a new random and returns it.
     *
     * @param int $max
     * @param int $min
     * @return string
     */
    public function make( $max = 20, $min = 5 )
    {
        return $this->shuffle( $max, $min );
    }

    /**
     * Makes a new random which is unique for a given DB table.
     * Laravel only.
     *
     * @param string $in
     * @param int $max
     * @param int $min
     * @return string
     * @throws RandomizrException
     */
    public function unique( $in, $max = 20, $min = 5 )
    {
        // Make a random
        $random = $this->make( $max, $min );

        // Set unique context
        if ( is_dir($in) ) $unique = $this->isUniqueInDir($in, $random);

        else if ( count($db = explode('@', $in)) == 2) $unique = $this->isUniqueInTable($db[0], $db[1], $random );

        else throw new RandomizrException('notADirectoryOrTable', $db);

        // Check if unique
        if ( ! $unique) return $this->unique($in, $max, $min);

        // Return the unique random
        return $random;
    }


    //////////////////////////////////////////////////////////////////////////
    // Checks
    //////////////////////////////////////////////////////////////////////////

    /**
     * Check if the given charset name is an alias.
     *
     * @param $name
     * @return bool
     */
    public function isAlias( $name )
    {
        return isset($this->aliases[$name]);
    }

    /**
     * Check if the given charset name is an existing charset.
     *
     * @param $name
     * @return bool
     */
    public function isCharset( $name )
    {
        return isset($this->charsets[$name]);
    }

    /**
     * Check if a given random is unique in a table for a given field. This
     * method requires the package to run in a Laravel application.
     *
     * @param string $table
     * @param string $field
     * @param string $random
     * @return bool
     * @throws RandomizrException
     */
    private function isUniqueInTable( $table, $field, $random )
    {
        if ( ! $this->db) throw new RandomizrException('dbQueryBuilderIsLaravelOnly');

        $exists = $this->db->table( $table )->where( $field, '=', $random )->pluck( $field );

        return is_null($exists);
    }

    /**
     * Check whether a given random filename (regardless of its extension) is
     * unique within a given directory.
     *
     * @param string $path
     * @param string $random
     * @return bool
     */
    private function isUniqueInDir( $path, $random )
    {
        $matching_files = glob("$path/$random.*");

        return count($matching_files) <= 0;
    }


    //////////////////////////////////////////////////////////////////////////
    // Helpers
    //////////////////////////////////////////////////////////////////////////

    /**
     * Shuffles the composed charset to randomize the result.
     *
     * @param $max
     * @param $min
     * @return string
     */
    protected function shuffle( $max, $min )
    {
        $length = mt_rand($min, $max);
        $random = '';
        $i      = 0;

        while ( $i < $length )
        {
            $shuffled = str_shuffle($this->composed_charset);
            $index = mt_rand(0, strlen($this->composed_charset) - 1);
            $random .= substr($shuffled, $index, 1);
            $i++;
        }

        return $random;
    }

    /**
     * Return all available aliases and charsets.
     *
     * @return array
     */
    public function available()
    {
        return array_merge($this->charsets(), $this->aliases());
    }

    /**
     * Return all available charsets.
     *
     * @return array
     */
    public function charsets()
    {
        return $this->charsets;
    }

    /**
     * Return all available (resolved) aliases.
     *
     * @return array
     */
    public function aliases()
    {
        $aliases = array();

        foreach ( $this->aliases as $name => $component )
        {
            $aliases[$name] = $this->resolveAlias($name);
        }

        return $aliases;
    }

}