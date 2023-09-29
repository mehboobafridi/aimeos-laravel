<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box ">
                <a href="{{route('home')}}" class="logo logo-dark mt-3">
                    <span class="logo-sm">
                        <h4  title="Dashboard" style="color: #fff">DB </h4>
                        {{-- <img src="{{ URL::asset('/assets/images/logo-sm-dark.png')}}" alt="" height="22"> --}}
                    </span>
                    <span class="logo-lg">
                        <h4 style="color: #fff">Dashboard</h4>
                        {{-- <img src="{{ URL::asset('/assets/images/logo-dark.png')}} " alt="" height="20"> --}}
                    </span>
                </a>

                <a href="{{route('home')}}" class="logo logo-light mt-3">
                    <span class="logo-sm">
                        <h4 title="Dashboard" style="color: #fff">DB</h4>
                        {{-- <img src="{{ URL::asset('/assets/images/logo-sm-light.png')}}" alt="" height="22"> --}}
                    </span>
                    <span class="logo-lg">
                        <h4 style="color: #fff">Dashboard</h4>
                        {{-- <img src="{{ URL::asset('/assets/images/logo-light.png')}}" alt="" height="20"> --}}
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                <i class="ri-menu-2-line align-middle"></i>
            </button>

           

           
        </div>

        <div class="d-flex">
        <div class="row p2" style="">
                <div class="col-md-12">
                <h5 class="" id="__progress__"></h5>
                </div>
            </div>
        </div>
        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ml-2">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ri-search-line"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
                    aria-labelledby="page-header-search-dropdown">
                    
                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search ...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="dropdown d-lg-inline-block ml-1">
               <div id="progress" class="badge badge-success"></div>
            </div>

            <div class="dropdown d-lg-inline-block ml-1">
              
            </div>


            <div class="dropdown d-lg-inline-block ml-1">
             &nbsp;&nbsp;&nbsp;
            </div>


            <div class="dropdown d-lg-inline-block ml-1">
                
            </div>

            <div class="dropdown d-lg-inline-block ml-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line"></i>
                </button>
            </div>

          

            <div class="dropdown d-inline-block user-dropdown">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{-- <img class="rounded-circle header-profile-user" src="{{ URL::asset('/assets/images/users/avatar-2.jpg')}}"
                        alt="Header Avatar"> --}}
                    <span class="d-none d-xl-inline-block ml-1"> @if(isset(auth()->user()->name))
  {{ auth()->user()->name }}
@else 
  <script>
    window.location = "home";
  </script>
@endif

 </span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    
                    <a class="dropdown-item text-danger" href="javascript:void();" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="bx bx-power-off font-size-16 align-middle mr-1 text-danger"></i> @lang('translation.Logout')</a>
                    {{-- <a class="dropdown-item" href="{{route('changePass')}}" ><i class="bx bx-power-off font-size-16 align-middle mr-1"></i>Change Password</a> --}}
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>

          
            
        </div>
    </div>
</header>