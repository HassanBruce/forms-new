<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $brands = Brand::with(['teams.lead', 'teams.members', 'users'])->withCount('users')
            ->when($u->hasRole('director'), fn ($q) => $q->whereKey($u->brand_id))->orderBy('name')->get();
        $members = User::where('company_id', $u->company_id)->with(['brand', 'team'])
            ->when($u->hasRole('director'), fn ($q) => $q->where('brand_id', $u->brand_id))
            ->orderByRaw("case role when 'admin' then 0 when 'director' then 1 when 'accounts' then 2 when 'team_lead' then 3 else 4 end")->orderBy('name')->get();
        $teams = Team::with('brand')->orderBy('name')->get();
        return view('team.index', ['brands' => $brands, 'members' => $members, 'teams' => $teams, 'company' => $u->company]);
    }

    public function storeMember(Request $request)
    {
        $u = $request->user();
        abort_unless($u->canManageUsers(), 403);
        $d = $this->validated($request, $u);
        $d['company_id'] = $u->company_id;
        $d['status'] = 'active';
        $member = User::create($d);
        $this->syncLead($member);
        return back()->with('ok', "{$member->name} added as ".$member->roleLabel().'. Share their email and password with them.');
    }

    public function updateMember(Request $request, User $user)
    {
        $u = $request->user();
        abort_unless($u->canManageUsers() && $user->company_id === $u->company_id, 403);
        abort_if($u->hasRole('director') && $user->brand_id !== $u->brand_id, 403);
        $d = $this->validated($request, $u, $user);
        if (empty($d['password'])) unset($d['password']);
        if ($user->id === $u->id) { unset($d['role'], $d['status']); }
        $user->update($d);
        $this->syncLead($user);
        return back()->with('ok', "{$user->name} updated.");
    }

    public function storeTeam(Request $request)
    {
        $u = $request->user();
        abort_unless($u->canManageUsers(), 403);
        $d = $request->validate([
            'name' => 'required|string|max:80',
            'brand_id' => ['required', Rule::exists('brands', 'id')->where('company_id', $u->company_id)],
        ]);
        if ($u->hasRole('director')) $d['brand_id'] = $u->brand_id;
        Team::create($d);
        return back()->with('ok', "Team {$d['name']} created.");
    }

    private function validated(Request $r, User $actor, ?User $target = null): array
    {
        $roles = $actor->hasRole('admin') ? array_keys(User::ROLES) : ['team_lead', 'agent'];
        $d = $r->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($target?->id)],
            'role' => ['required', Rule::in($roles)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('company_id', $actor->company_id)],
            'team_id' => ['nullable', Rule::exists('teams', 'id')->where('company_id', $actor->company_id)],
            'status' => 'nullable|in:active,disabled',
            'password' => [$target ? 'nullable' : 'required', Password::min(8)],
        ]);
        if ($actor->hasRole('director')) $d['brand_id'] = $actor->brand_id;
        if (in_array($d['role'], ['admin', 'accounts'], true)) $d['team_id'] = null;
        if ($d['role'] === 'director') $d['team_id'] = null;
        if (! empty($d['team_id'])) $d['brand_id'] = Team::find($d['team_id'])->brand_id;
        unset($d['status']) ; if ($r->filled('status')) $d['status'] = $r->input('status');
        return $d;
    }

    private function syncLead(User $m): void
    {
        if ($m->role === 'team_lead' && $m->team_id) Team::whereKey($m->team_id)->update(['lead_id' => $m->id]);
    }
}
