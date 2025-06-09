@extends('layouts.app')

@section('title', __(key: 'Edit Role'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __(key: 'Role') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __(key: 'Edit an new role.') }}
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
                        {{ __(key: 'Edit') }}
                    </li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route(name: 'roles.update', parameters: $role->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                @include('roles.include.form')

                                <a href="{{ route(name: 'roles.index') }}" class="btn btn-secondary">{{ __(key: 'Back') }}</a>
                                
                                <button type="submit" class="btn btn-primary">{{ __(key: 'Update') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
