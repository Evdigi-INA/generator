@extends('layouts.app')

@section('title', __(key: 'Dashboard'))

@section('content')
    <div class="page-heading">
        <h3>Dashboard</h3>
    </div>

    <div class="page-content">
        <section class="row">
            <div class="col-md-12">
                @if (session(key: 'status'))
                    <div class="alert alert-success alert-dismissible show fade">
                        {{ session(key: 'status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <h4>Hi 👋, {{ auth()->user()->name }}</h4>
                        <p>{{ __(key: 'You are logged in!') }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
