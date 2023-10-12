@extends('layouts.master')
@section('css')

@endsection
@section('title')Users Management @endsection

@section('content')
@component('components.breadcrumb')
    @slot('title')Users Management @endslot
@endcomponent

<div class="row">
  <div class="col-md-1"></div>
  <div class="col-md-10">
    <div class="mb-2"> 
      <a class="btn btn-primary waves-effect waves-light" href="{{ route('users-management.create') }}"> Create New User</a>
    </div>
      <div class="card">
          <div class="card-body">
              <h4 class="card-title">Users Managment</h4>
              <p class="card-title-desc"> Following are the permissions assigned to users. You can change permissions with edit icon
              </p>    
              
              <div class="table-responsive">
                  <table class="table mb-0 table-bordered">

                      <thead>
                          <tr>
                              <th width="5%">#</th>
                              <th width="20%">Name</th>
                              <th width="20%">Email</th>
                              <th width="40%">Permissions</th>
                              <th width="15%">Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                        @foreach ($data as $key => $user)
                          <tr>
                              <th scope="row">{{ ++$i }}</th>
                              <td>{{ $user->name }}</td>
                              <td>{{ $user->email }}</td>
                              <td>
                                @if(!empty($user->getPermissionNames()))
                                  @foreach($user->getPermissionNames() as $v)
                                      @php
                                          $permission = Spatie\Permission\Models\Permission::where('name', $v)->first();
                                          $my_app_id = env('APP_ID');
                                      @endphp
                                      @if($permission && $permission->app_id == $my_app_id)
                                          <label class="badge badge-soft-success font-size-13">{{ $v }}</label>
                                      @endif
                                  @endforeach
                                @endif
                                
                              </td>
                              <td id="tooltip-container9" >
                                <a href="{{ route('users-management.edit',$user->id) }}" class="me-3 text-primary" data-bs-container="#tooltip-container9" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Edit" aria-label="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
                                {!! Form::open(['method' => 'DELETE','route' => ['users-management.destroy', $user->id],'style'=>'display:inline', 'onsubmit' => 'return confirm("are you sure ?")']) !!}
                                {{ Form::button('<i class="fas fa-trash-alt text-danger"></i>', ['class' => 'btn btn-default', 'type' => 'submit']) }}
                                {!! Form::close() !!}
                              </td>
                          </tr>
                          @endforeach
                      </tbody>
                  </table>
              </div>

          </div>
      </div>
  </div>
  <div class="col-md-1"></div>

</div>


{!! $data->render() !!}


@endsection