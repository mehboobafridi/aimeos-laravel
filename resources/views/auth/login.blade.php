@extends('layouts.master-without-nav')
@section('title')
Login
@endsection
@section('body')
<body class="auth-body-bg">
@endsection
@section('content')
<div class="home-btn d-none d-sm-block">
    <a href="{{url('home')}}"><i class="mdi mdi-home-variant h2 text-white"></i></a>
</div>
<div>
    <div class="container-fluid p-0">
        <div class="row no-gutters">
            <div class="col-lg-4">
            </div>
            <div class="col-lg-4">
                <div class="authentication-page-content p-4 d-flex align-items-center min-vh-100">
                    <div class="w-100">
                        <div class="row justify-content-center">
                            <div class="col-lg-9">
                                <div>
                                    @if (session('status'))
                                    <div class="alert alert-danger alert-block">
                                        <button type="button" class="close" data-dismiss="alert">×</button>    
                                        <strong> {{ session('status') }} </strong>
                                    </div>
                                    @endif
                                    <div class="text-center">
                                        <div>
                                            <h2 style="color: black">{{ config('app.name') }}</h2>
                                        </div>

                                        <h4 class="font-size-18 mt-4">Welcome Back !</h4>
                                    </div>

                                    <div class="p-2 mt-5">
                                        <form method="POST" action="{{ route('login') }}">
                                            @csrf

                                            <div class="form-group auth-form-group-custom mb-4">
                                                <i class="ri-user-2-line auti-custom-input-icon"></i>
                                                <label for="email">{{ __('E-Mail Address') }}</label>
                                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter Email">
                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            <div class="form-group auth-form-group-custom mb-4">
                                                <i class="ri-lock-2-line auti-custom-input-icon"></i>
                                                <label for="password">{{ __('Password') }}</label>
                                                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" id="password" placeholder="Enter password">
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            {{-- <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="customControlInline">
                                                <label class="custom-control-label" for="customControlInline">Remember me</label>
                                            </div> --}}

                                            <div class="mt-4 text-center">
                                                <button class="btn btn-primary w-md waves-effect waves-light" type="submit">{{ __('Log In') }}</button>
                                            </div>


                                        </form>
                                    </div>

                                </div>
                                <div class="mt-3 text-center">
                                    <p>Don't have an account ? <a href="{{url('register')}}" class="font-weight-medium text-primary"> Resgister</a> </p>
                                    <p><script>document.write(new Date().getFullYear())</script>© Techxa.  Crafted with <i class="mdi mdi-heart text-danger"></i> by Techxa</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                {{-- <div class="authentication-bg">
                <div class="bg-overlay"></div>
                </div> --}}
            </div>
    </div>
</div>
</div>
@endsection