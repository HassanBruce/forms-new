@php $u = auth()->user(); $c = $company; $roles = $u->hasRole('admin') ? \App\Models\User::ROLES : array_intersect_key(\App\Models\User::ROLES, array_flip(['team_lead', 'agent'])); @endphp
<x-layouts.app title="Team & roles">
<div x-data="{ modal: null, edit: {} }" class="space-y-6">
    <x-page-header title="Team & roles" sub="Who can log in, which brand they belong to and what they can see">
        @if($u->canManageUsers())
            <button @click="modal = 'team'" class="btn btn-secondary"><x-icon name="users" class="size-[18px]"/>Add team</button>
            <button @click="modal = 'member'; edit = { role: 'agent', brand_id: '{{ $u->brand_id }}', team_id: '' }" class="btn btn-primary"><x-icon name="plus" class="size-[18px]"/>Add member</button>
        @endif
    </x-page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[1fr_560px]">
        <section class="card space-y-4 p-6">
            <div><h3 class="text-[15px] font-semibold">Organisation hierarchy</h3><p class="text-[13px] text-ink-500">Data is scoped top-down: each level sees everything below it</p></div>
            @php $node = function ($indent, $name, $sub, $ini, $tone, $level, $meta = null, $dark = false) { return compact('indent','name','sub','ini','tone','level','meta','dark'); };
                $nodes = [$node(0, $c->name, 'Parent company · Admin login', strtoupper(substr($c->name,0,2)), 'brand', 'Admin', Str::plural('brand', $brands->count(), true), true)];
                foreach ($brands as $br) {
                    $nodes[] = $node(1, $br->name, 'Sub-brand'.($br->director ? ' · '.$br->director->name : ''), $br->initials(), 'success', 'Director', Str::plural('user', $br->users_count, true));
                    foreach ($br->teams as $t) {
                        $nodes[] = $node(2, $t->name, $t->lead->name ?? 'No lead yet', $t->lead?->initials() ?? 'T', 'warning', 'Team lead', Str::plural('agent', $t->members->where('role','agent')->count(), true));
                        foreach ($t->members->where('role', 'agent') as $m) $nodes[] = $node(3, $m->name, $m->email, $m->initials(), 'info', 'Agent', 'Own bookings only');
                    }
                } @endphp
            @foreach($nodes as $n)
                <div class="flex items-center gap-3" style="padding-left: {{ $n['indent'] * 28 }}px">
                    @if($n['indent'])<span class="h-0.5 w-3.5 bg-line"></span>@endif
                    <div class="flex min-w-0 items-center gap-3 rounded-xl border px-3 py-2.5 {{ $n['dark'] ? 'border-nav bg-nav' : 'border-line bg-white' }}">
                        <x-avatar :text="$n['ini']" :tone="$n['tone']"/>
                        <div class="min-w-0"><p class="truncate text-sm font-semibold {{ $n['dark'] ? 'text-white' : '' }}">{{ $n['name'] }}</p><p class="truncate text-xs {{ $n['dark'] ? 'text-nav-text' : 'text-ink-500' }}">{{ $n['sub'] }}</p></div>
                        <x-badge status="none" :label="$n['level']" class="before:hidden! {{ ['brand'=>'bg-brand-50! text-brand-600!','success'=>'bg-green-50! text-green-600!','warning'=>'bg-amber-50! text-amber-600!','info'=>'bg-sky-50! text-sky-600!'][$n['tone']] }}"/>
                    </div>
                    @if($n['meta'])<span class="hidden truncate text-[13px] text-ink-500 sm:inline">{{ $n['meta'] }}</span>@endif
                </div>
            @endforeach
        </section>

        <section class="card overflow-hidden">
            <div class="p-5"><h3 class="text-[15px] font-semibold">Role permissions</h3><p class="text-[13px] text-ink-500">Built-in access per role</p></div>
            <table class="w-full">
                <thead class="bg-line-soft"><tr><th class="th">Permission</th>@foreach(['Admin','Dir.','Lead','Agent','Accts'] as $h)<th class="th px-1 text-center">{{ $h }}</th>@endforeach</tr></thead>
                <tbody>
                @foreach(\App\Models\User::PERMISSIONS as $perm => $allowed)
                    <tr class="border-t border-line-soft"><td class="px-5 py-2.5 text-[13px] text-ink-700">{{ $perm }}</td>
                        @foreach(array_keys(\App\Models\User::ROLES) as $r)
                            <td class="py-2.5 text-center">@if(in_array($r, $allowed))<span class="inline-grid size-5 place-items-center rounded-md bg-green-50 text-green-600"><x-icon name="check" class="size-3.5"/></span>@else<span class="inline-block h-0.5 w-2.5 rounded bg-line"></span>@endif</td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    </div>

    <h2 class="text-lg font-semibold">Members</h2>
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[760px]">
            <thead class="bg-line-soft"><tr><th class="th">Member</th><th class="th">Role</th><th class="th">Brand</th><th class="th">Team</th><th class="th">Status</th><th class="th"></th></tr></thead>
            <tbody>
            @foreach($members as $m)
                <tr class="border-t border-line-soft">
                    <td class="td"><div class="flex items-center gap-3"><x-avatar :text="$m->initials()" :tone="$m->tone()" size="size-[34px]"/><div><p class="font-semibold text-ink-900">{{ $m->name }}</p><p class="text-xs text-ink-500">{{ $m->email }}</p></div></div></td>
                    <td class="td"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ ['brand'=>'bg-brand-50 text-brand-600','success'=>'bg-green-50 text-green-600','warning'=>'bg-amber-50 text-amber-600','info'=>'bg-sky-50 text-sky-600'][$m->tone()] }}">{{ $m->roleLabel() }}</span></td>
                    <td class="td">{{ $m->hasRole('admin','accounts') ? 'All brands' : ($m->brand->name ?? '—') }}</td>
                    <td class="td">{{ $m->team->name ?? '—' }}</td>
                    <td class="td"><x-badge :status="$m->status"/></td>
                    <td class="td text-right">@if($u->canManageUsers() && ($u->hasRole('admin') || $m->hasRole('team_lead','agent')))
                        <button @click="modal = 'member'; edit = @js(['id' => $m->id, 'name' => $m->name, 'email' => $m->email, 'role' => $m->role, 'brand_id' => (string) $m->brand_id, 'team_id' => (string) $m->team_id, 'status' => $m->status])" class="btn btn-ghost btn-sm">Edit</button>@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if($u->canManageUsers())
    {{-- Member modal --}}
    <div x-cloak x-show="modal === 'member'" class="fixed inset-0 z-50 grid place-items-center bg-ink-900/50 p-4" @keydown.escape.window="modal = null">
        <form method="POST" :action="edit.id ? '{{ url('team/members') }}/' + edit.id : '{{ route('team.members.store') }}'" class="card w-full max-w-lg space-y-4 p-6" @click.outside="modal = null">
            @csrf <template x-if="edit.id"><input type="hidden" name="_method" value="PUT"></template>
            <h3 class="text-lg font-semibold" x-text="edit.id ? 'Edit member' : 'Add member'"></h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="label">Full name</label><input name="name" x-model="edit.name" required class="input"></div>
                <div><label class="label">Email</label><input type="email" name="email" x-model="edit.email" required class="input"></div>
                <div><label class="label">Role</label><select name="role" x-model="edit.role" class="input">@foreach($roles as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                @if($u->hasRole('admin'))
                <div x-show="!['admin','accounts'].includes(edit.role)"><label class="label">Sub-brand</label><select name="brand_id" x-model="edit.brand_id" class="input">@foreach($brands as $br)<option value="{{ $br->id }}">{{ $br->name }}</option>@endforeach</select></div>
                @endif
                <div x-show="['team_lead','agent'].includes(edit.role)"><label class="label">Team</label><select name="team_id" x-model="edit.team_id" class="input"><option value="">No team</option>@foreach($teams as $t)<option value="{{ $t->id }}">{{ $t->name }} · {{ $t->brand->code }}</option>@endforeach</select></div>
                <div x-show="edit.id"><label class="label">Status</label><select name="status" x-model="edit.status" class="input"><option value="active">Active</option><option value="disabled">Disabled</option></select></div>
                <div class="sm:col-span-2"><label class="label" x-text="edit.id ? 'New password (leave blank to keep)' : 'Temporary password'"></label><input type="text" name="password" :required="!edit.id" minlength="8" class="input" autocomplete="new-password"></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" @click="modal = null" class="btn btn-secondary">Cancel</button><button class="btn btn-primary">Save</button></div>
        </form>
    </div>
    {{-- Team modal --}}
    <div x-cloak x-show="modal === 'team'" class="fixed inset-0 z-50 grid place-items-center bg-ink-900/50 p-4">
        <form method="POST" action="{{ route('team.teams.store') }}" class="card w-full max-w-md space-y-4 p-6" @click.outside="modal = null">
            @csrf
            <h3 class="text-lg font-semibold">Add team</h3>
            <div><label class="label">Team name</label><input name="name" required class="input" placeholder="Team Ahmed"></div>
            <div><label class="label">Sub-brand</label><select name="brand_id" class="input">@foreach($brands as $br)<option value="{{ $br->id }}">{{ $br->name }}</option>@endforeach</select></div>
            <p class="text-xs text-ink-500">Then add a member with role <b>Team lead</b> to this team.</p>
            <div class="flex justify-end gap-2"><button type="button" @click="modal = null" class="btn btn-secondary">Cancel</button><button class="btn btn-primary">Create team</button></div>
        </form>
    </div>
    @endif
</div>
</x-layouts.app>
