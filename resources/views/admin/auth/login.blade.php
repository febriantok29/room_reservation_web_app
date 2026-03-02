@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header', 'Login Admin Dashboard')

@section('auth_body')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.login.submit') }}" method="post">
        @csrf

        <div class="input-group mb-3">
            <input type="text" name="login" class="form-control" value="{{ old('login') }}"
                placeholder="Email / Employee ID" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user"></span>
                </div>
            </div>
        </div>

        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
    </form>
@stop
