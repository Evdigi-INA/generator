<div class="row mb-2">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">{{ __(key: 'Name') }}</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                placeholder="{{ __(key: 'Name') }}" value="{{ isset($user) ? $user->name : old(key: 'name') }}" required
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
            <label for="email">{{ __(key: 'Email') }}</label>
            <input type="email" name="email" id="email"
                class="form-control @error('email') is-invalid @enderror" placeholder="{{ __(key: 'Email') }}"
                value="{{ isset($user) ? $user->email : old(key: 'email') }}" required>
            @error('email')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="password">{{ __(key: 'Password') }}</label>
            <input type="password" name="password" id="password"
                class="form-control @error('password') is-invalid @enderror" placeholder="{{ __(key: 'Password') }}"
                {{ empty($user) ? 'required' : '' }}>
            @error('password')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
            @isset($user)
                <div id="passwordHelpBlock" class="form-text">
                    {{ __(key: 'Leave the password & password confirmation blank if you don`t want to change them.') }}
                </div>
            @endisset
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="password-confirmation">{{ __(key: 'Password Confirmation') }}</label>
            <input type="password" name="password_confirmation" id="password-confirmation" class="form-control"
                placeholder="{{ __(key: 'Password Confirmation') }}" {{ empty($user) ? 'required' : '' }}>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="role">{{ __(key: 'Role') }}</label>
            <select class="form-select" name="role" id="role" class="form-control" required>
                <option value="" selected disabled>{{ __(key: '-- Select role --') }}</option>
                @foreach ($roles as $role)
                    @isset($user)
                        <option value="{{ $role->id }}"
                            {{ $user->getRoleNames()->toArray() !== [] && $user->getRoleNames()[0] == $role->name ? 'selected' : '-' }}>
                            {{ $role->name }}</option>
                    @else
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endisset
                @endforeach
            </select>
            @error('role')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="row g-0">
            <div class="col-md-5 text-center">
                <img src="{{ $user?->avatar ?? 'https://placehold.co/300?text=No+Image+Available' }}" alt="Avatar"
                    class="rounded img-fluid" style="object-fit: cover; width: 100%; height: 100px;" />
            </div>
            <div class="col-md-7">
                <div class="form-group ms-3">
                    <label for="avatar">{{ __(key: 'Avatar') }}</label>
                    <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror"
                        id="avatar">
                    @error('avatar')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                    @enderror
                    @isset($user)
                        <div id="avatar-help-block" class="form-text">
                            {{ __(key: 'Leave the avatar blank if you don`t want to change it.') }}
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>
