<section>
    <div class="mb-4">
        <h2 class="h5 text-danger mb-1">Delete Account</h2>
        <p class="text-muted mb-0">This action is permanent. Enter your password to confirm account deletion.</p>
    </div>

    <form method="post" action="{{ route('profile.destroy') }}">
        @csrf
        @method('delete')

        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="delete_password" class="form-label">Password</label>
                <input id="delete_password" name="password" type="password" class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif" placeholder="Enter your password">
                @if($errors->userDeletion->has('password'))
                    <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                @endif
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-danger">Delete Account</button>
            </div>
        </div>
    </form>
</section>
