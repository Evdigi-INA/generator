<div class="row mb-2">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">{{ __('Name') }}</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                placeholder="{{ __('Name') }}" value="{{ isset($user) ? $user->name : old('name') }}" required
                autofocus>
            @error('name')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="email">{{ __('Email') }}</label>
            <input type="email" name="email" id="email"
                class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('Email') }}"
                value="{{ isset($user) ? $user->email : old('email') }}" required>
            @error('email')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="password">{{ __('Password') }}</label>
            <input type="password" name="password" id="password"
                class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('Password') }}"
                {{ empty($user) ? 'required' : '' }}>
            @error('password')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
            @isset($user)
                <div id="passwordHelpBlock" class="form-text">
                    {{ __('Leave the password & password confirmation blank if you don`t want to change them.') }}
                </div>
            @endisset
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="password-confirmation">{{ __('Password Confirmation') }}</label>
            <input type="password" name="password_confirmation" id="password-confirmation" class="form-control"
                placeholder="{{ __('Password Confirmation') }}" {{ empty($user) ? 'required' : '' }}>
        </div>
    </div>

    @empty($user)
        <div class="col-md-6">
            <div class="form-group">
                <label for="role">{{ __('Role') }}</label>
                <select class="form-select" name="role" id="role" class="form-control" required>
                    <option value="" selected disabled>-- Select role --</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                    @error('role')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                    @enderror
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="avatar">{{ __('Avatar') }}</label>
                <input type="file" name="avatar" id="avatar"
                    class="form-control @error('avatar') is-invalid @enderror">
                @error('avatar')
                    <span class="text-danger">
                        {{ $message }}
                    </span>
                @enderror
            </div>
        </div>
    @endempty

    @isset($user)
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="role">{{ __('Role') }}</label>
                    <select class="form-select" name="role" id="role" class="form-control" required>
                        <option value="" selected disabled>{{ __('-- Select role --') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ $user->getRoleNames()->toArray() !== [] && $user->getRoleNames()[0] == $role->name ? 'selected' : '-' }}>
                                {{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            </div>

            <div class="col-md-1 text-center">
                <div class="avatar avatar-xl">
                    @if (!$user->avatar)
                        <img src="https://via.placeholder.com/350?text=No+Image+Avaiable" alt="Avatar" class="rounded mb-2 mt-2 img-fluid">
                    @else
                        <img src="{{ asset('storage/uploads/avatars/' . $user->avatar) }}" alt="Avatar" class="rounded mb-2 mt-2 img-fluid">
                    @endif
                </div>
            </div>

            <div class="col-md-5 me-0 pe-0">
                <div class="form-group">
                    <label for="avatar">{{ __('Avatar') }}</label>
                    <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" id="avatar">
                    @error('avatar')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                    @enderror
                    @if ($user->avatar == null)
                        <div id="passwordHelpBlock" class="form-text">
                            {{ __('Leave the avatar blank if you don`t want to change it.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endisset
</div>
