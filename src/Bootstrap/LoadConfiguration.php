<?php

namespace CrCms\Microservice\Bootstrap;

use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration as BaseLoadConfiguration;

class LoadConfiguration extends BaseLoadConfiguration
{
    /**
     * Get all of the configuration files for the application.
     *
     * @param  Application  $app
     * @return array
     */
    protected function getConfigurationFiles(Application $app)
    {
        $paths = $files = [];

        $defaultConfigPath = $app->defaultConfigPath();
        $configPath = $app->configPath();

        file_exists($defaultConfigPath) && $paths[] = $defaultConfigPath;
        file_exists($configPath) && $paths[] = $configPath;

        foreach (Finder::create()->files()->name('*.php')->in($paths) as $file) {
            /* @var \Symfony\Component\Finder\SplFileInfo $file */
            $directory = $this->getNestedDirectory($file, $paths);

            $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }
}
