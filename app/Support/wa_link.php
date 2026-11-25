<?php

namespace App\Support;

if (! function_exists('App\Support\wa_link')) {
    /**
     * Namespaced alias for wa_link() so both wa_link() and \App\Support\wa_link() work everywhere.
     */
    function wa_link(?string $to, ?string $text = null): string
    {
        return \wa_link($to, $text);
    }
}
