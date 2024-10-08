@extends('layouts.app')

@section('title', __('Create Module (API)'))

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
                        {{ __('API Generators') }}
                        <small style="font-size: 8px;">Beta</small>
                    </h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Create a new CRUD Module (API).') }}
                    </p>
                </div>

                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('API Generators') }}
                    </li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <x-alert />

            {{-- <div class="alert alert-info alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <h6 class="alert-heading">{{ __('Info') }}</h6>
                <p>
                    <a href="https://zzzul.github.io/generator-docs-next/usage#creating-api-crud" target="_blank">
                        {{ __('Please consider to read about new Laravel 11 API') }}
                    </a>
                </p>
            </div> --}}

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="/api-generators" method="POST" id="form-generator">
                                @csrf
                                @method('POST')

                                @include('generator::include.api-form')

                                <a href="{{ url()->previous() }}" id="btn-back"
                                    class="btn btn-secondary">{{ __('Back') }}</a>

                                <button type="submit" id="btn-save" class="btn btn-primary">{{ __('Generate') }}</button>
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
