<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') | {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ URL::asset('/assets/images/favicon.ico') }}">
    @include('layouts.head')
</head>
@section('body')
@show

<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content px-0">
                <div class="container-fluid p-0">
                    @include('layouts.message')
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar -->
    {{-- @include('layouts.right-sidebar') --}}
    <!-- /Right-bar -->

    <!-- Right bar overlay-->
    {{-- <div class="rightbar-overlay"></div> --}}


    <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLbl" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="progressModalLbl"> </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-2" style="min-height:100px;">

                    <div id="message_box" class="alert alert-success mt-2">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="modal fade" id="message-modal" tabindex="-1" aria-labelledby="message-modall"
            aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="message-modall"> </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-2" style="min-height:100px;">
                        <p id="msg"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}



    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')
    <script>
        $("document").ready(function() {
            setTimeout(function() {
                $("div.alert div[id!='message_box']").remove();
            }, 5000); // 5 secs


        });
    </script>

</body>

</html>
