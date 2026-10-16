<x-layouts.guest title="Sign in">
<form method="POST" action="{{ route('login') }}" class="w-full max-w-[400px] space-y-5">
    @csrf
    <div><h1 class="text-2xl font-bold">Welcome back</h1><p class="mt-1 text-sm text-ink-500">Sign in to your agency workspace</p></div>
    @error('email')<div class="rounded-[10px] bg-red-50 px-3 py-2.5 text-sm text-red-600">{{ $message }}</div>@enderror
    <div><label class="label" for="email">Work email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input" placeholder="you@agency.co.uk"></div>
    <div><label class="label" for="password">Password</label><input id="password" name="password" type="password" required class="input" placeholder="••••••••"></div>
    <label class="flex items-center gap-2 text-[13px] text-ink-700"><input type="checkbox" name="remember" class="size-4 rounded accent-brand-600"> Remember me</label>
    <button class="btn btn-primary w-full py-3">Sign in</button>
    <p class="text-center text-[13px] text-ink-500">New agency? <a href="{{ route('register') }}" class="font-semibold text-brand-600">Start a 14-day free trial</a></p>
</form>
</x-layouts.guest>
