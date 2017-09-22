<?php namespace Parsidev\Support\Exceptions;

use Exception;

class RandomizrException extends Exception {

    /**
     * Contains all exception messages.
     *
     * @var array
     */
    protected $messages = array(
        'undefinedCharsetOrAlias' => "Could not find the charset or alias named `:0`.",
        'couldNotResolveAliasComponent' => "Could not resolve `:0` as either a string, method, alias or charset.",
        'notADirectoryOrTable' => "`:0` could not be resolved as a directory or database table (using the `table@field` format).",
        'dbQueryBuilderIsLaravelOnly' => "Laravel DB query builder requested outside a Laravel context.",
    );

    /**
     * Construct the Exception class.
     *
     * @param string $message
     * @param array $args
     * @param int $code
     * @param Exception $previous
     */
    public function __construct( $message, $args = array(), $code = 0, Exception $previous = null )
    {
        parent::__construct($this->fillMessage($message, $args), $code, $previous);
    }

    /**
     * Fills the arguments of the exception message.
     *
     * @param string $message
     * @param array $args
     * @return string
     */
    protected function fillMessage($message, $args = array())
    {
        $message = isset($this->messages[$message]) ? $this->messages[$message] : $message;

        foreach ( $args as $key => $arg ) $message = str_replace(":$key", $arg, $message);

        return $message;
    }

}