<x-guest-layout>
    <x-slot name="title">Confirm Password</x-slot>
    <x-slot name="heading">Confirm Password</x-slot>
    <x-slot name="subheading">This is a secure area. Please confirm your password to continue.</x-slot>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Confirm</button>
    </form>
</x-guest-layout>
