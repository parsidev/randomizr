<?php namespace Parsidev\Support\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Config;

class RandomizrPublisherCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'randomizr:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish various Randomizr assets (config)';

    /**
     * Publishable assets.
     *
     * @var array
     */
    protected $assets = array('config');

    /**
     * Relative path to the packages root directory.
     *
     * @var string
     */
    protected $relative_path;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->relative_path = str_replace(base_path(), '', __DIR__.'/..');

        $asset = $this->argument('asset') ?: false;

        if ( ! $asset)
        {
            foreach ( $this->assets as $asset ) $this->{'publish'.ucfirst($asset)}();
        }
        else
        {
            // Undefined asset
            if ( ! in_array($asset, $this->assets)) $this->error("Unrecorgnized asset `$asset`.");

            // Publishable asset
            else $this->{'publish'.ucfirst($asset)}();
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('asset', InputArgument::OPTIONAL, 'Name of the asset you want to publish (config)'),
        );
    }


    //////////////////////////////////////////////////////////////////////////
    // Helpers
    //////////////////////////////////////////////////////////////////////////

    /**
     * Publish the Randomizr config file.
     *
     * @return void
     */
    private function publishConfig()
    {
        $this->call("config:publish", array(
            'package'   => 'parsidev/randomizr',
            '--path'    => "$this->relative_path/Config/",
        ));
    }

}
