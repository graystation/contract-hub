<?php

if (! function_exists('fmt_amount')) {
    /**
     * Format an integer amount as Japanese yen: ¥50,000
     */
    function fmt_amount(int|float|null $amount): string
    {
        return '¥' . number_format((int) ($amount ?? 0));
    }
}
