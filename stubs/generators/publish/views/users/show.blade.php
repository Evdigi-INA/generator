@extends('layouts.app')

@section('title', __(key: 'Detail User'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __(key: 'User') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __(key: 'Detail user information.') }}
                    </p>
                </div>

                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __(key: 'Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route(name: 'users.index') }}">{{ __(key: 'User') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __(key: 'Detail') }}
                    </li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <div class="avatar avatar-xl">
                                                <img src="{{ $user->avatar }}" alt="Avatar" class="rounded img-fluid"
                                                    style="object-fit: cover; width: 350px; height: 200px;">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __(key: 'Name') }}</td>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __(key: 'Email') }}</td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __(key: 'Role') }}</td>
                                        <td>{{ $user->getRoleNames()->toArray() !== [] ? $user->getRoleNames()[0] : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __(key: 'Email verified at') }}</td>
                                        <td>{{ $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __(key: 'Created at') }}</td>
                                        <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __(key: 'Updated at') }}</td>
                                        <td>{{ $user->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <a href="{{ route(name: 'users.index') }}" class="btn btn-secondary">{{ __(key: 'Back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
