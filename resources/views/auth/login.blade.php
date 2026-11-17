<x-guest-layout>
    <div class="flex gap-6 border-b border-ink/10 mb-6">
        <a href="{{ route('login') }}" class="pb-3 type-h4 border-b-2 border-brand -mb-px">Log In</a>
        <a href="{{ route('register') }}" class="pb-3 type-h4 text-ink/40 hover:text-ink">Register</a>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <label class="block">
            <span class="type-body font-bold">Email</span>
            <input id="email" name="email" type="email" required autofocus autocomplete="username"
                   value="{{ old('email') }}" class="input-underline mt-1">
            @error('email') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
        </label>

        <label class="block" x-data="{ show: false }">
            <span class="type-body font-bold">Password</span>
            <div class="relative mt-1">
                <input id="password" name="password" :type="show ? 'text' : 'password'" required
                       autocomplete="current-password" class="input-underline pr-14">
                <button type="button" @click="show = !show"
                        class="absolute right-0 top-1/2 -translate-y-1/2 type-body text-xs font-bold text-ink/50 hover:text-brand">
                    <span x-text="show ? 'Hide' : 'Show'"></span>
                </button>
            </div>
            @error('password') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
        </label>

        <label class="inline-flex items-center gap-2 type-body">
            <input type="checkbox" name="remember" class="rounded border-ink/40 text-brand focus:ring-brand">
            <span>Remember me</span>
        </label>

        <button type="submit" class="btn-primary w-full">Log In</button>

        @if (Route::has('password.request'))
            <a class="block text-center type-body text-brand hover:underline" href="{{ route('password.request') }}">
                Forgot your password?
            </a>
        @endif
    </form>
</x-guest-layout>
