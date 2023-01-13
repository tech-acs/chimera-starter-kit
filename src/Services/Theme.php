<?php

namespace Uneca\Chimera\Services;

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
                'bg-zinc-400',
                'bg-green-800',
                'bg-blue-800',
                'bg-purple-800',
                'bg-pink-800',
                'bg-black',
                'bg-orange-600',
                'bg-lime-600',
                'bg-cyan-600',
                'bg-violet-600',
                'bg-rose-500',
                'bg-sky-500'
            ],
            'subdued' => [],
            default => [],
        };
    }
}
