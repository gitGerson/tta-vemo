<?php

if (!function_exists('codeUnique')) {
    function codeUnique()
    {
        $timestamp = time();
        return 'MT-' . $timestamp;
    }
}
