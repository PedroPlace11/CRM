<?php

namespace App\Http\Controllers;

use App\Models\AutomationRule;
use App\Models\DealStage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AutomationRuleController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        return Inertia::render('automations/Index', [
            'rules' => AutomationRule::with('creator:id,name')
                ->when(! $isAdmin, fn ($q) => $q->where('created_by', $user->id))
                ->latest()->get(),
            'stages' => DealStage::orderBy('position')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:191'],
            'active'  => ['boolean'],
            'trigger' => ['required', 'array'],
            'action'  => ['required', 'array'],
        ]);

        AutomationRule::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);
        return back();
    }

    public function update(Request $request, AutomationRule $rule)
    {
        $this->authorizeRuleOwner($request, $rule);
        $rule->update($request->only(['name', 'active', 'trigger', 'action']));
        return back();
    }

    public function destroy(AutomationRule $rule)
    {
        $this->authorizeRuleOwner(request(), $rule);
        $rule->delete();
        return back();
    }

    private function authorizeRuleOwner(Request $request, AutomationRule $rule): void
    {
        $user = $request->user();
        if ($rule->created_by !== $user->id && ! $user->hasAnyRole(['admin', 'manager'])) {
            abort(403);
        }
    }
}
