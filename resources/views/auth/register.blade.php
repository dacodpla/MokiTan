<x-guest-layout>
    <div class="flex gap-6 border-b border-ink/10 mb-6">
        <a href="{{ route('login') }}" class="pb-3 type-h4 text-ink/40 hover:text-ink">Log In</a>
        <a href="{{ route('register') }}" class="pb-3 type-h4 border-b-2 border-brand -mb-px">Register</a>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <label class="block">
            <span class="type-body font-bold">Name</span>
            <input id="name" name="name" type="text" required autofocus autocomplete="name"
                   value="{{ old('name') }}" class="input-underline mt-1">
            @error('name') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
        </label>

        <label class="block">
            <span class="type-body font-bold">Email</span>
            <input id="email" name="email" type="email" required autocomplete="username"
                   value="{{ old('email') }}" class="input-underline mt-1">
            @error('email') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
        </label>

        <label class="block">
            <span class="type-body font-bold">Password</span>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="input-underline mt-1">
            @error('password') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
        </label>

        <label class="block">
            <span class="type-body font-bold">Confirm Password</span>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   autocomplete="new-password" class="input-underline mt-1">
        </label>

        <button type="submit" class="btn-primary w-full">Register</button>

        <a class="block text-center type-body text-brand hover:underline" href="{{ route('login') }}">
            Already registered? Log in
        </a>
    </form>
</x-guest-layout>
