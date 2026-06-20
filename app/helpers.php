<?php

if (! function_exists('rupiah')) {
    /**
     * Format an integer Rupiah amount for display, e.g. 150000 => "Rp 150.000".
     */
    function rupiah(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}

if (! function_exists('rupiah_clean')) {
    /**
     * Parse a masked Rupiah string back to an integer, e.g. "Rp 150.000" => 150000.
     */
    function rupiah_clean(string $value): int
    {
        return (int) preg_replace('/\D/', '', $value);
    }
}
