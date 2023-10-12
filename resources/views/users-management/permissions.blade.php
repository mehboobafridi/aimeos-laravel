@extends('layouts.master')
@section('css')

@endsection
@section('title')Permission @endsection

@section('content')
@component('components.breadcrumb')
    @slot('title')Permissions @endslot
@endcomponent


 
@if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
@endif


<div class="row justify-content-center">
    <div class="col-md-6">
        {{-- <div class="mb-2"> 
            <button type="button" class="btn btn-success waves-effect waves-light" data-toggle="modal" data-target="#staticBackdrop">Create New Permission</button>
        </div> --}}
        <div class="card">
            <div class="card-body"> 
                <div class="table-responsive">
                    <table class="table mb-0">
  
                        <thead>
                            <tr>
                                <th >#</th>
                                <th >Permisson Name</th>
                            </tr>
                                {{-- <th >Action</th>  --}}
                        </thead>
                        <tbody>
                          @foreach ($permissions as $key => $perm)
                            <tr>
                                <th scope="row">{{ ++$i }}</th>
                                <td ><lable class="badge badge-soft-primary font-size-14">{{ $perm->name }}</label></td> 
                            </tr>
                                {{-- <td id="tooltip-container9" >
                                  {!! Form::open(['method' => 'DELETE','route' => ['permissions.destroy', $perm->id],'style'=>'display:inline', 'onsubmit' => 'return confirm("are you sure ?")' ]) !!}
                                  {{ Form::button('<i class="fas fa-trash-alt text-danger"></i>', ['class' => 'btn btn-default', 'type' => 'submit']) }}
                                  {!! Form::close() !!}
                                </td> --}}
                            @endforeach
                        </tbody>
                    </table>
                </div>
  
            </div>
        </div>
    </div>
  
  </div>
  

{!! $permissions->render() !!}


{{-- ////////////--ADD-NEW-PERMISSION-FORM--/////////// --}}

<div class="col-sm-6 col-md-4 col-xl-3">
    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Create New Permission</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                {!! Form::open(array('route' => 'permissions.store','method'=>'POST')) !!}
                <div class="modal-body">
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
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Permission Name:</strong>
                                {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                            </div>
                        </div>
                        
                        {{-- <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div> --}}
                    </div>
                    {{-- <p>I will not close if you click outside me. Don't even try to press escape key.</p> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light waves-effect" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary waves-effect waves-light">Submit</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
{{-- /////////////////////////////// --}}
@endsection