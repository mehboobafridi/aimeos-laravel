@extends('layouts.master')
@section('css')
<link href="{{ URL::asset('/assets/css/bootstrap.min.css')}}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('/assets/css/icons.min.css')}}" id="icons-style" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('/assets/css/app.min.css')}}" id="app-style" rel="stylesheet" type="text/css" />
@endsection
@section('title')Create User @endsection

@section('content')


@if (count($errors) > 0)
  <div class="alert alert-danger">
    <strong>Whoops!</strong> There were some problems with your input.<br><br>
    <ul>
       @foreach ($errors->all() as $error)
         <li>{{ $error }}</li>
       @endforeach
    </ul>
  </div>
@endif

<div class="row justify-content-md-center">
    <div class="col-md-10">
        {!! Form::open(array('route' => 'users-management.store','method'=>'POST')) !!}
        <div class="row">
            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group">
                    <strong>Name:</strong>
                    {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group">
                    <strong>Email:</strong>
                    {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group">
                    <strong>Password:</strong>
                    {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group">
                    <strong>Confirm Password:</strong>
                    {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
                </div>
            </div>

            <div class="col-xs-3 col-sm-3 col-md-3">
                <div class="form-group">
                    <strong>User Status:</strong>
                    {!! Form::select('user_type', ['0' => 'Inactive User', '2' => 'Active'], null, ['class' => 'form-control']) !!}
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="form-group checkbox">
                    <strong>Permissions:</strong>
                    <br/>
                    <div class="row">
                        @php
                            $columnCount = 6; // Number of columns
                            $permissionArray = $permission->toArray(); // Convert object to array
                            $rowCount = ceil(count($permissionArray) / $columnCount); // Number of rows
                            $permissions = array_chunk($permissionArray, $rowCount); // Split permissions into chunks
                        @endphp
            
                        @for($i = 0; $i < $columnCount; $i++)
                            <div class="col-md-{{ 12 / $columnCount }}">
                            {{-- <div class="col-md-4"> --}}
                                @foreach($permissions[$i] ?? [] as $value)
                                    <div class="form-check mb-2">
                                        {{ Form::checkbox('permission[]', $value['id'], false, array('class' => 'form-check-input ')) }}
                                        <label class="form-check-label ml-2 badge badge-soft-primary font-size-15 ">
                                            {{ $value['name'] }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
            
            <div class="col-xs-12 col-sm-12 col-md-12">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
        {!! Form::close() !!}

    </div>
</div>



@endsection