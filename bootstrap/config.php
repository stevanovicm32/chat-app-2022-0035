<?php

/**
 * Get the path to the configuration directory.
 * Override Laravel's default config path to use src/Infrastructure/Config
 */
if (!function_exists('config_path')) {
    function config_path($path = '')
    {
        return app()->basePath('src/Infrastructure/Config').($path ? DIRECTORY_SEPARATOR.$path : '');
    }
}

