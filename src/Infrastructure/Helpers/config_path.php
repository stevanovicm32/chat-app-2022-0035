<?php

/**
 * Override Laravel's config_path helper to use src/Infrastructure/Config
 */
if (!function_exists('config_path')) {
    function config_path($path = '')
    {
        $base = base_path('src/Infrastructure/Config');
        return $path ? $base . DIRECTORY_SEPARATOR . $path : $base;
    }
}

