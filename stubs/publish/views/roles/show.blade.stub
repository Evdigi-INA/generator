@extends('layouts.app')

@section('title', __(key: 'Detail Role'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __(key: 'Role') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __(key: 'Detail role information.') }}
                    </p>
                </div>

                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __(key: 'Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route(name: 'roles.index') }}">{{ __(key: 'Role') }}</a>
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
                                        <td class="fw-bold">{{ __(key: 'Name') }}</td>
                                        <td>{{ $role->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __(key: 'Permissions') }}</td>
                                        <td>
                                            <ul class="list-inline mb-0">
                                                @foreach ($role->permissions as $permission)
                                                    <li class="list-inline-item ">• {{ $permission->name }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __(key: 'Created at') }}</td>
                                        <td>{{ $role->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __(key: 'Updated at') }}</td>
                                        <td>{{ $role->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <a href="{{ route(name: 'roles.index') }}" class="btn btn-secondary">{{ __(key: 'Back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
