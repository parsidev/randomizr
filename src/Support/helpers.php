<?php

if ( ! function_exists('randomizr'))
{
    /**
     * Get the Randomizr binding.
     *
     * @param $type
     * @return Parsidev\Support\Services\Randomizr
     */
    function randomizr( $type = false )
    {
        $binding = app('Parsidev\Support\Services\Randomizr');

        return $type ? $binding->{$type}() : $binding;
    }
}

if ( ! function_exists('str_starts_with'))
{
    /**
     * Check if a given string starts with a (group of) character(s).
     *
     * @param $needle
     * @param $haystack
     * @return bool
     */
    function str_starts_with( $needle, $haystack )
    {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
}

if ( ! function_exists('str_ends_with'))
{
    /**
     * Check if a given string ends with a (group of) character(s).
     *
     * @param $needle
     * @param $haystack
     * @return bool
     */
    function str_ends_with( $needle, $haystack )
    {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}
