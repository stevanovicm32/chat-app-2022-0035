<?php

/**
 * Override Laravel's config_path helper to use src/Infrastructure/Config
 */
if (!function_exists('config_path')) {
    function config_path($path = '')
    {
        return base_path('src/Infrastructure/Config').($path ? DIRECTORY_SEPARATOR.$path : '');
    }
}

