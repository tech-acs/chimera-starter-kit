<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Http\Requests\FaqRequest;
use Uneca\Chimera\Models\Faq;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class FaqManagementController extends Controller
{
    public function index(Request $request)
    {
        try {
            $records = Faq::orderBy('rank')->get();
        } catch (QueryException $exception) {
            $request->session()->flash('message', 'There is something wrong with the FAQs database table. Please make sure the migration has already been run.');
            $records = collect([]);
        }
        return view('faq.manage.index', compact('records'));
    }

    public function create()
    {
        return view('faq.manage.create');
    }

    public function store(FaqRequest $request)
    {
        Faq::create($request->only(['question', 'answer', 'rank']));
        return redirect()->route('manage.faq.index')
            ->withMessage('The new FAQ has been added to the list');
    }

    public function edit(Faq $faq)
    {
        return view('faq.manage.edit', compact('faq'));
    }

    public function update(FaqRequest $request, Faq $faq)
    {
        $faq->update($request->only(['question', 'answer', 'rank']));
        return redirect()->route('manage.faq.index')
            ->withMessage('The FAQ has been updated');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return redirect()->route('manage.faq.index')
            ->withMessage('The FAQ has been removed from the list');
    }
}
