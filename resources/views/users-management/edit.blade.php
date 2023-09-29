@extends('layouts.master')
@section('css')
    <link href="{{ URL::asset('/assets/libs/select2/select2.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
@endsection
@section('title')
    Edit User
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('title')
            Edit User
        @endslot
    @endcomponent
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
            {!! Form::model($user, ['method' => 'PATCH', 'route' => ['users-management.update', $user->id]]) !!}
            <div class="row">
                <div class="col-xs-6 col-sm-6 col-md-6">
                    <div class="form-group">
                        <strong>Name:</strong>
                        {!! Form::text('name', null, ['placeholder' => 'Name', 'class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6">
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6">
                    <div class="form-group">
                        <strong>Email:</strong>
                        {!! Form::text('email', null, ['placeholder' => 'Email', 'class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6">
                </div>

                <div class="col-xs-3 col-sm-3 col-md-3">
                    <div class="form-group">
                        <strong>User Status:</strong>
                        {!! Form::select(
                            'user_type',
                            ['0' => 'Inactive User', '2' => 'Active'],
                            null,
                            ['class' => 'form-control'],
                        ) !!}
                    </div>
                </div>


                <div class="col-md-12">
                    <div class="form-group checkbox">
                        <strong>Permissions:</strong>
                        <br />
                        <div class="row">
                            @php
                                $columnCount = 6; // Number of columns
                                $permissionArray = $permissions->toArray(); // Convert object to array
                                $rowCount = ceil(count($permissionArray) / $columnCount); // Number of rows
                                $permissions = array_chunk($permissionArray, $rowCount); // Split permissions into chunks
                            @endphp

                            @for ($i = 0; $i < $columnCount; $i++)
                                <div class="col-md-{{ 12 / $columnCount }}">
                                    @foreach ($permissions[$i] ?? [] as $value)
                                        <div class="form-check mb-2">
                                            <label
                                                class="ml-2 ">{{ Form::checkbox('permissions[]', $value['id'], in_array($value['id'], $userPermissions) ? true : false, ['class' => 'name form-check-input']) }}

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
                {{-- <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Select shipping ddresses for this user</label>
                        <select class="select2 form-control select2-multiple" name="addresses[]" multiple data-placeholder="Choose addresses...">
                            @foreach ($addresses as $address)
                                <option value="{{ $address->id }}" {{ in_array($address->id, $selectedAddresses) ? 'selected' : '' }}>
                                    {{ $address->address_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                    
                </div> --}}
                <div class="col-xs-12 col-sm-12 col-md-12 ">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
            {!! Form::close() !!}

        </div>

    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/assets/libs/select2/select2.min.js') }}"></script>

    <script>
        $(".select2").select2();
        $(".select2-limiting").select2({
            maximumSelectionLength: 2
        });
    </script>
@endsection
