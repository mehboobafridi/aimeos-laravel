@extends('layouts.master-without-nav')
@section('title')
Reset Password
@endsection
@section('body')
<body class="auth-body-bg">
@endsection
@section('content')
<div class="home-btn d-none d-sm-block">
    <a href="{{url('index')}}"><i class="mdi mdi-home-variant h2 text-white"></i></a>
</div>
<div>

    <div class="container-fluid p-0">
        <div class="row no-gutters">
            <div class="col-lg-12">
                <div class="authentication-page-content p-4 d-flex align-items-center min-vh-100">
                    <div class="w-100">
                        <div class="row justify-content-center">
                            <div class="col-lg-4"></div>
                            <div class="col-lg-4">
                            @if (Session::has('message'))
         <div class="alert alert-success">{{ Session::get('message') }}</div>
         @elseif (Session::has('error'))
         <div class="alert alert-danger">{{ Session::get('error') }}</div>
         @endif
                                <div>
                                    <div class="text-center">
                                        <div>
                                            <h2 style="color: black">Ebay Orders</h2>
                                            {{-- <a href="{{url('index')}}" class="logo"><img src="{{ URL::asset('/assets/images/logo-dark.png')}}" height="20" alt="logo"></a> --}}
                                        </div>

                                        <h4 class="font-size-18 mt-4">Reset Password</h4>
                                        {{-- <p class="text-muted">Reset your password to Nazox.</p> --}}
                                    </div>

                                    <div class="p-2 mt-5">
                                        
                                        <form method="POST" action="{{ route('changePass') }}">
                                            @csrf
                                            <!-- <input type="hidden" name="token" value="{{-- $token --}}"> -->

                                            <div class="form-group auth-form-group-custom mb-4">
                                                <i class="ri-mail-line auti-custom-input-icon"></i>
                                                <label for="email">{{ __('E-Mail Address') }}</label>
                                                <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter email">
                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            <div class="form-group auth-form-group-custom mb-4">
                                                <i class="ri-lock-2-line auti-custom-input-icon"></i>
                                                <label for="password">{{ __('Password') }}</label>
                                                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Enter password">
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            <div class="form-group auth-form-group-custom mb-4">
                                                <i class="ri-lock-2-line auti-custom-input-icon"></i>
                                                <label for="password-confirm">{{ __('Confirm Password') }}</label>
                                                <input type="password" id="password-confirm" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Enter password">
                                            </div>

                                            <div class="mt-4 text-center">
                                                <button class="btn btn-primary w-md waves-effect waves-light" type="submit">Reset Password</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="mt-5 text-center">
                                        <p><script>document.write(new Date().getFullYear())</script>© Techxa.  Crafted with <i class="mdi mdi-heart text-danger"></i> by Techxa</p>
                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-4"></div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection
