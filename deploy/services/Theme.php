<?php

namespace App\Services;

class Theme
{
    public static function colors(string $selectedTheme = 'default')
    {
        return match ($selectedTheme) {
            'default' => [
                'bg-red-500',
                'bg-yellow-500',
                'bg-green-500',
                'bg-blue-500',
                'bg-pink-500',
                'bg-purple-500',
                'bg-red-800',
                'bg-yellow-800',
                'bg-green-800',
                'bg-blue-800',
                'bg-purple-800',
                'bg-pink-800',
            ],
            'subdued' => [],
            default => [],
        };
    }
}
