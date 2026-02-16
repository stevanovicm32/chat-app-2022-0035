<?php

if (! function_exists('config_path')) {
    /**
     * Get the path to the configuration directory.
     *
     * This overrides the default to point at src/Infrastructure/Config so the
     * Laravel bootstrap loads the custom config files that live outside of the
     * normal config directory.
     */
    function config_path($path = '')
    {
        $configDir = app()->basePath('src/Infrastructure/Config');

        return $configDir.($path ? DIRECTORY_SEPARATOR.$path : '');
    }
}
