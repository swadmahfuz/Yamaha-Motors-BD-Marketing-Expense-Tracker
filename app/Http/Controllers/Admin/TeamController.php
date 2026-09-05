<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MonthlyBudget;
use App\Models\Team;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        return view('admin.teams.index', ['teams' => Team::orderBy('name')->paginate(20)]);
    }

    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:teams,code'],
        ]);

        $team = Team::create($data + ['is_active' => true]);
        $audit->log('team.created', $team, null, $data);

        return back()->with('success', 'Team created.');
    }

    public function update(Request $request, Team $team, AuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:teams,code,'.$team->id],
            'is_active' => ['boolean'],
        ]);

        $old = $team->only(['name', 'code', 'is_active']);
        $team->update($data);
        $audit->log('team.updated', $team, $old, $data);

        return back()->with('success', 'Team updated.');
    }
}
