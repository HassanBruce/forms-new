<x-layouts.guest title="Start free trial">
<form method="POST" action="{{ route('register') }}" class="w-full max-w-[440px] space-y-4">
    @csrf
    <div><h1 class="text-2xl font-bold">Create your agency workspace</h1><p class="mt-1 text-sm text-ink-500">14 days free. No card needed.</p></div>
    @if($errors->any())<div class="rounded-[10px] bg-red-50 px-3 py-2.5 text-sm text-red-600">{{ $errors->first() }}</div>@endif
    <div class="grid grid-cols-[1fr_120px] gap-3">
        <div><label class="label">Agency / company name</label><input name="company" value="{{ old('company') }}" required class="input" placeholder="Al-Noor Travel Ltd"></div>
        <div><label class="label">Code</label><input name="brand_code" value="{{ old('brand_code') }}" required maxlength="10" class="input uppercase" placeholder="ALN"></div>
    </div>
    <div><label class="label">Your name</label><input name="name" value="{{ old('name') }}" required class="input"></div>
    <div><label class="label">Work email</label><input name="email" type="email" value="{{ old('email') }}" required class="input"></div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="label">Password</label><input name="password" type="password" required class="input"></div>
        <div><label class="label">Confirm</label><input name="password_confirmation" type="password" required class="input"></div>
    </div>
    <div><label class="label">Currency</label><select name="currency" class="input">@foreach(['GBP','USD','EUR','SAR','PKR'] as $c)<option @selected(old('currency','GBP')===$c)>{{ $c }}</option>@endforeach</select></div>
    <button class="btn btn-primary w-full py-3">Create workspace</button>
    <p class="text-center text-[13px] text-ink-500">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-brand-600">Sign in</a></p>
</form>
</x-layouts.guest>
