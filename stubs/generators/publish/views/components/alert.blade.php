@if (session(key: 'success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __(key: 'Success') }}</h4>
        <p>{{ session(key: 'success') }}</p>
    </div>
@endif

@if (session(key: 'error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __(key: 'Error') }}</h4>
        <p>{{ session(key: 'error') }}</p>
    </div>
@endif

@if (session(key: 'status') == 'profile-information-updated')
    <div class="alert alert-success alert-dismissible show fade mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __(key: 'Success') }}</h4>
        <p>{{ __(key: 'Profile information updated successfully.') }}</p>
    </div>
@endif

@if (session(key: 'status') == 'password-updated')
    <div class="alert alert-success alert-dismissible show fade mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __(key: 'Success') }}</h4>
        <p>{{ __(key: 'Password updated successfully.') }}</p>
    </div>
@endif

@if (session(key: 'status') == 'two-factor-authentication-disabled')
    <div class="alert alert-success alert-dismissible show fade mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __(key: 'Success') }}</h4>
        <p>{{ __(key: 'Two factor Authentication has been disabled.') }}</p>
    </div>
@endif

@if (session(key: 'status') == 'two-factor-authentication-enabled')
    <div class="alert alert-success alert-dismissible show fade mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __(key: 'Success') }}</h4>
        <p>{{ __(key: 'Two factor Authentication has been enabled.') }}</p>
    </div>
@endif

@if (session(key: 'status') == 'recovery-codes-generated')
    <div class="alert alert-success alert-dismissible show fade mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __(key: 'Success') }}</h4>
        <p>{{ __(key: 'Regenerated Recovery Codes Successfully.') }}</p>
    </div>
@endif
