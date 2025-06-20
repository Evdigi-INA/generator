@extends('layouts.app')

@section('title', __(key: 'Create User'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __(key: 'User') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __(key: 'Create a new user.') }}
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
                        {{ __(key: 'Create') }}
                    </li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route(name: 'users.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('POST')

                                @include('users.include.form')

                                <a href="{{ route(name: 'users.index') }}" class="btn btn-secondary">{{ __(key: 'Back') }}</a>

                                <button type="submit" class="btn btn-primary">{{ __(key: 'Save') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
