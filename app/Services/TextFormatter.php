<?php

namespace App\Services;

class TextFormatter
{
    /**
     * Convert plain text with \r\n formatting to HTML
     *
     * @param string $text
     * @return string
     */
    public static function toHtml(string $text): string
    {
        // Escape HTML special characters first
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Convert horizontal separator lines (----------) to <hr> tags
        $text = preg_replace('/^-{5,}$/m', '<hr class="my-4 border-gray-300">', $text);

        // Convert URLs to clickable links
        $text = preg_replace_callback(
            '/(https?:\/\/[^\s<]+)/',
            function ($matches) {
                $url = $matches[1];
                // Remove any trailing punctuation that's likely not part of the URL
                $url = rtrim($url, '.,;:!?)');
                return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline">' . $url . '</a>';
            },
            $text
        );

        // Convert email addresses to mailto links
        $text = preg_replace(
            '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/',
            '<a href="mailto:$1" class="text-blue-600 hover:text-blue-800 underline">$1</a>',
            $text
        );

        // Convert \r\n line breaks to <br> tags
        $text = str_replace(["\r\n", "\r", "\n"], '<br>', $text);

        // Remove multiple consecutive <br> tags (more than 2) and replace with paragraph spacing
        $text = preg_replace('/(<br>\s*){3,}/', '<br><br>', $text);

        return $text;
    }

    /**
     * Convert plain text to HTML with custom options
     *
     * @param string $text
     * @param array $options
     * @return string
     */
    public static function toHtmlWithOptions(string $text, array $options = []): string
    {
        $defaults = [
            'convert_links' => true,
            'convert_emails' => true,
            'convert_separators' => true,
            'escape_html' => true,
            'link_class' => 'text-blue-600 hover:text-blue-800 underline',
            'hr_class' => 'my-4 border-gray-300',
        ];

        $options = array_merge($defaults, $options);

        // Optionally escape HTML
        if ($options['escape_html']) {
            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }

        // Optionally convert horizontal separators
        if ($options['convert_separators']) {
            $text = preg_replace('/^-{5,}$/m', '<hr class="' . $options['hr_class'] . '">', $text);
        }

        // Optionally convert URLs to links
        if ($options['convert_links']) {
            $text = preg_replace_callback(
                '/(https?:\/\/[^\s<]+)/',
                function ($matches) use ($options) {
                    $url = $matches[1];
                    $url = rtrim($url, '.,;:!?)');
                    return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="' . $options['link_class'] . '">' . $url . '</a>';
                },
                $text
            );
        }

        // Optionally convert email addresses
        if ($options['convert_emails']) {
            $text = preg_replace(
                '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/',
                '<a href="mailto:$1" class="' . $options['link_class'] . '">$1</a>',
                $text
            );
        }

        // Convert line breaks
        $text = str_replace(["\r\n", "\r", "\n"], '<br>', $text);

        // Clean up multiple consecutive line breaks
        $text = preg_replace('/(<br>\s*){3,}/', '<br><br>', $text);

        return $text;
    }
}
