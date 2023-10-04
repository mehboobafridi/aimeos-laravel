<!-- ========== Left Sidebar Start ========== -->
<style>
    #sidebar-menu ul li ul.sub-menu li a {
        color: #d9dde3 !important;
    }

    #sidebar-menu .has-arrow:after {
        content: "";
        font-family: "Material Design Icons";
        display: block;
        float: right;
        transition: transform 0.2s;
        font-size: 1rem;
    }
</style>
<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu" class="box">



                <li class="menu-title">@lang('translation.Menu')</li>

                @can('users-management')
                    <li>
                        <a href="{{ route('users-management.index') }}" class="has-arrow waves-effect">
                            <i class=" fas fa-users-cog"></i>
                            <span>@lang('translation.Users Managment')</span>
                        </a>
                    </li>
                @endcan

                @can('home-page')
                    <li>
                        <a href="{{ url('home') }}" class="waves-effect">
                            <i class="ri-dashboard-line"></i>
                            <span>Home</span>
                        </a>
                    </li>
                @endcan

                <li>
                    <a href="#" class="has-arrow waves-effect">
                        <i class="ri-profile-line"></i>
                        <span>Orders</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
						<li><a href="{{route('ViewNewOrders')}}" >New</a></li>
						<li><a href="{{route('ViewCanceledOrders')}}" >Canceled</a></li>
						<li><a href="{{route('ViewShippedOrders')}}" >Shipped</a></li>
                    </ul>
                </li>






            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
