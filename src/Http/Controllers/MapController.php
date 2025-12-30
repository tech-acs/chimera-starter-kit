<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Uneca\Chimera\Enums\PageableTypes;
use Uneca\Chimera\Models\Page;
use Uneca\Chimera\Services\APCA;
use Uneca\Chimera\Services\ColorPalette;

class MapController extends Controller
{
    public function index()
    {
        $currentPalette = ColorPalette::current()->colors;
        $totalColors = count($currentPalette);
        $pages = Page::for(PageableTypes::MapIndicators)
            ->get()
            ->map(function ($page, $index) use ($totalColors, $currentPalette) {
                return [
                    'title' => $page->title,
                    'description' => $page->description,
                    'link' => route('map.page', $page),
                    'slug' => $page->slug,
                    'bg-color' => $currentPalette[$index % $totalColors],
                    'fg-color' => APCA::decideBlackOrWhiteTextColor($currentPalette[$index]),
                ];
            })
            ->all();

        return view('chimera::map.index', compact('pages'));
    }

    public function show()
    {
        return view('chimera::map.show');
    }
}
