<div class="row mb-2">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">{{ __(key: 'Name') }}</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                placeholder="{{ __(key: 'Name') }}" value="{{ isset($role) ? $role->name : old(key: 'name') }}" autofocus required>
            @error('name')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <label class="mb-1">{{ __(key: 'Permissions') }}</label>
        @error('permissions')
            <div class="text-danger mb-2 mt-0">{{ $message }}</div>
        @enderror
    </div>

    @foreach(config(key: 'permission.permissions') as $permission)
        <div class="col-md-3">
            <div class="card border">
                <div class="card-content">
                    <div class="card-body">
                        <h6 class="card-title">{{ ucwords(string: $permission['group']) }}</h6>
                        @foreach ($permission['access'] as $access)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="{{ str()->slug($access) }}"
                                    name="permissions[]" value="{{ $access }}"
                                    {{ isset($role) && $role->hasPermissionTo($access) ? 'checked' : '' }}/>
                                <label class="form-check-label" for="{{ str()->slug($access) }}">
                                    {{ ucwords(string: __(key: $access)) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
