<x-layouts.app title="Settings">
    <x-page-header title="Settings" sub="Your profile{{ auth()->user()->canManageCompany() ? ' and company workspace' : '' }}"/>
    <div class="grid items-start gap-5 xl:grid-cols-2">
        <form method="POST" action="{{ route('settings.update') }}" class="card space-y-4 p-6">
            @csrf @method('PUT') <input type="hidden" name="section" value="profile">
            <x-card-head icon="user" title="Your profile" :sub="$user->email.' · '.$user->roleLabel()"/>
            <div><label class="label">Name</label><input name="name" value="{{ old('name', $user->name) }}" required class="input"></div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="label">Current password</label><input type="password" name="current_password" class="input" autocomplete="current-password"></div>
                <div><label class="label">New password</label><input type="password" name="password" class="input" autocomplete="new-password"></div>
                <div><label class="label">Confirm</label><input type="password" name="password_confirmation" class="input"></div>
            </div>
            <button class="btn btn-primary">Save profile</button>
        </form>
        @if($user->canManageCompany())
        <form method="POST" action="{{ route('settings.update') }}" class="card space-y-4 p-6">
            @csrf @method('PUT') <input type="hidden" name="section" value="company">
            <x-card-head icon="building" title="Company workspace" :sub="'Plan: '.ucfirst($company->plan).($company->trial_ends_at ? ' · trial ends '.$company->trial_ends_at->format('d M Y') : '')"/>
            <div><label class="label">Company name</label><input name="name" value="{{ old('name', $company->name) }}" required class="input"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="label">Currency</label><select name="currency" class="input">@foreach(['GBP','USD','EUR','SAR','PKR'] as $cur)<option @selected($company->currency === $cur)>{{ $cur }}</option>@endforeach</select></div>
                <div><label class="label">Booking ref prefix</label><input name="booking_prefix" value="{{ $company->booking_prefix }}" maxlength="6" class="input uppercase"><p class="mt-1 text-xs text-ink-500">Next: {{ $company->booking_prefix }}-{{ $company->next_booking_no }}</p></div>
            </div>
            <button class="btn btn-primary">Save company</button>
        </form>
        @endif
    </div>
</x-layouts.app>
