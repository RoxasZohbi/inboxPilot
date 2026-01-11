<?php

if (!function_exists('text_to_html')) {
    /**
     * Convert plain text with \r\n formatting to HTML
     *
     * @param string $text
     * @return string
     */
    function text_to_html(string $text): string
    {
        return \App\Services\TextFormatter::toHtml($text);
    }
}
