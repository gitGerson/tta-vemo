<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah($number)
    {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
    function formatNominal($number)
    {
        return number_format($number, 0, ',', '.');
    }
}
