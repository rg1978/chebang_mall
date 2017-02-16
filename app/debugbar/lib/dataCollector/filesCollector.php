<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */


use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class debugbar_dataCollector_filesCollector extends DataCollector implements Renderable
{
    /** @var \Illuminate\Contracts\Foundation\Application */
    protected $app;
    protected $basePath;

    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct()
    {
        $this->basePath = ROOT_DIR;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $files = $this->getIncludedFiles();
        $compiled = $this->getCompiledFiles();

        $included = array();
        $alreadyCompiled = array();
        foreach ($files as $file) {
            // Skip the files from Debugbar, they are only loaded for Debugging and confuse the output.
            // Of course some files are stil always loaded (ServiceProvider, Facade etc)
            if (strpos($file, 'vendor/maximebf/debugbar/src') !== false || strpos(
                    $file,
                    'debugbar_'
                ) !== false
            ) {
                continue;
            } elseif (!in_array($file, $compiled)) {
                $included[] = array(
                    'message' => "'" . $this->stripBasePath($file) . "',",
                    // Use PHP syntax so we can copy-paste to compile config file.
                    'is_string' => true,
                );
            } else {
                $alreadyCompiled[] = array(
                    'message' => "* '" . $this->stripBasePath($file) . "',",
                    // Mark with *, so know they are compiled anyways.
                    'is_string' => true,
                );
            }
        }

        // First the included files, then those that are going to be compiled.
        $messages = array_merge($included, $alreadyCompiled);

        return array(
            'messages' => $messages,
            'count' => count($included),
        );
    }

    /**
     * Get the files included on load.
     *
     * @return array
     */
    protected function getIncludedFiles()
    {
        return get_included_files();
    }

    /**
     * Get the files that are going to be compiled, so they aren't as important.
     *
     * @return array
     */
    protected function getCompiledFiles()
    {
        return array();
    }

    /**
     * Remove the basePath from the paths, so they are relative to the base
     *
     * @param $path
     * @return string
     */
    protected function stripBasePath($path)
    {
        return ltrim(str_replace($this->basePath, '', $path), '/');
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        $name = $this->getName();
        return array(
            "$name" => array(
                "icon" => "files-o",
                "widget" => "PhpDebugBar.Widgets.MessagesWidget",
                "map" => "$name.messages",
                "default" => "{}"
            ),
            "$name:badge" => array(
                "map" => "$name.count",
                "default" => "null"
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'files';
    }
}

