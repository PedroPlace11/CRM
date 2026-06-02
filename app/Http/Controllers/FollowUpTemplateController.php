<?php

namespace App\Http\Controllers;

use App\Models\FollowUpTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FollowUpTemplateController extends Controller
{
    public function index()
    {
        return Inertia::render('followups/Templates', [
            'templates' => FollowUpTemplate::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:191'],
            'subject' => ['required', 'string', 'max:191'],
            'body'    => ['required', 'string'],
            'active'  => ['boolean'],
        ]);

        FollowUpTemplate::create($data);
        return back()->with('success', 'Template criado.');
    }

    public function update(Request $request, FollowUpTemplate $template)
    {
        $data = $request->validate([
            'name'    => ['sometimes', 'string', 'max:191'],
            'subject' => ['sometimes', 'string', 'max:191'],
            'body'    => ['sometimes', 'string'],
            'active'  => ['boolean'],
        ]);

        $template->update($data);
        return back()->with('success', 'Template atualizado.');
    }

    public function destroy(FollowUpTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Template removido.');
    }
}
