@extends('layouts.app')

@section('title', __(key: 'Create Module (API)'))

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>
                        {{ __(key: 'API Generators') }}
                        <small style="font-size: 8px;">Beta</small>
                    </h3>
                    <p class="text-subtitle text-muted">
                        {{ __(key: 'Create a new CRUD Module (API).') }}
                    </p>
                </div>

                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __(key: 'Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __(key: 'API Generators') }}
                    </li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <x-alert />

            <div class="row">
                <div class="col-md-12">
                    @if (!class_exists(class: \Laravel\Sanctum\Sanctum::class))
                        <div class="alert alert-danger">
                            Please install <a href="https://laravel.com/docs/12.x/sanctum">Laravel Sanctum</a> first, and
                            read how to used in <a
                                href="https://zzzul.github.io/generator-docs-next/usage#creating-api-crud">Generator</a>.
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-body">
                            <form action="/api-generators" method="POST" id="form-generator">
                                @csrf
                                @method('POST')

                                @include('generator::include.api-form')

                                <a href="/api-generators/create" id="btn-back" class="btn btn-secondary">{{ __(key: 'Back') }}</a>

                                <button type="submit" id="btn-save" @disabled(!class_exists(class: \Laravel\Sanctum\Sanctum::class))
                                    class="btn btn-primary">{{ __(key: 'Generate') }}</button>
                            </form>
                        </div>
                    </div>

                    <div id="validation-errors" style="display: none;">
                        <div class="alert alert-danger fade show" role="alert">
                            <h4 class="alert-heading">Error</h4>
                            <ul class="mb-0"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
        integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('generator::include.js.api-create-js')

    @include('generator::include.js.function-js')
@endpush
