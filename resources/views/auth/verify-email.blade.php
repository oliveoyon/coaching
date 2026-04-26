<x-guest-layout>
    <x-slot name="title">Verify Email</x-slot>
    <x-slot name="heading">Verify Your Email</x-slot>
    <x-slot name="subheading">Please confirm your email address before continuing.</x-slot>

    <p class="text-muted mb-4">
        Thanks for signing up. Please verify your email address by clicking the link we emailed you. If you did not receive it, we can send another.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="d-grid gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-100">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100">Log Out</button>
        </form>
    </div>
</x-guest-layout>
