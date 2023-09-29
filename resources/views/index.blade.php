@extends('layouts.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('/vendor/daterange/css/responsive.dataTables.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('/vendor/daterange/css/dataTables.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('/vendor/daterange/css/daterangepicker.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('/vendor/daterange/css/sweetalert2.min.css') }}" />

    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jq-3.6.0/jszip-2.5.0/dt-1.11.3/b-2.1.1/b-html5-2.1.1/datatables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/1.0.7/css/responsive.dataTables.min.css" /> --}}
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" integrity="sha512-mSYUmp1HYZDFaVKK//63EcZq4iFWFjxSL+Z3T/aCt4IO9Cejm03q3NKKYN6pFQzY0SBOr8h+eCIAZHPXcpZaNw==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.min.css" /> --}}
@endsection
@section('title')
    Home
@endsection
@section('content')
    <style>
        .dt-buttons {
            margin-bottom: 1.2rem;
        }

        .left-col {
            float: left;
            width: 25%;
        }

        .center-col {
            float: left;
            width: 50%;
        }

        .right-col {
            float: left;
            width: 25%;
        }

        .text-bold {
            font-weight: bold;
        }

        .order-detail-parent {
            background-color: #f2f2f2;
            border: 1px solid #e0e0e0;
            font-size: 13px;
            padding: 10px;
            margin: 1px;
        }

        .order-details {
            /* margin-top: 10px;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    max-width: 500px !important; */
        }

        #loading {
            display: none;
        }

        .order-details table {
            width: 100%;
        }

        .order-details th,
        .order-details td {
            padding: 5px;
            text-align: left;
        }

        .order-details thead th {
            font-weight: bold;
        }

        #radio-container {
            overflow-y: auto;
            max-height: 200px;
            font-size: 12px !important;
            color: #3283d6;
        }

        #radio-container input[type="radio"] {
            margin-right: 5px;
        }

        table.table-order td,
        table.table-order th {
            padding: 2px 4px !important;
            font-size: 12px !important;
            border: none !important;
        }

        table.table-order th {
            font-weight: bold !important;
            color: #000 !important;
        }

        .custom-select {
            border: 1px solid #ddd;
            border-radius: 5;
            height: 35px;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="d-none" id="msg"></div>
        </div>
    </div>
    <div class="">
        @if (Session::has('message'))
            <div class="alert alert-success">{{ Session::get('message') }}</div>
        @elseif (Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
        @endif


    </div>

    <div class="main py-2">
    <div class="row">
        <div class="col-md-10  offset-md-1">

        <div class="card border-0 shadow table-wrapper table-responsive">
            <div class="card-header" style="background-color: #fff; border-bottom: 1px solid #dfe3e5;">
                <h2 class="card-title">Connection Panel</h2>
            </div>
            <div class="card-body">
           
                <h6 class="card-subtitle mb-2 text-muted">Select Region</h6>
                <p class="card-text">Connection is regionwise, so if you connect US, you will be able to use the same connection for CA and MX also.</p>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <select class="custom-select" name="region" id="region">
                                <option value="">Select Region</option>
                             
                                @foreach ($regions as $region)
                                    <option value="{{ $region->region_id }}">{{ $region->region_name }}</option>
                                @endforeach
                            </select>
                        </div>
    
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <button id="connectAmazon" class="btn btn-success">Connect to Amazon</button>
                        </div>
    
                    </div>


                    <div class="col-md-6 offset-md-2">
                       <table class="table table-responsive">
                        <tr>
                            <td>#</td>
                            <td>Region</td>
                            <td>Status</td>
                            <td>Connection Date</td>
                        </tr>
                       </table>    
                    </div>


                </div>
            </div>
           
        </div>

 
        </div>
        
        </div>
        </div>
    </div>
    <!-- Dispute Modal End For RETURN-->



    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/vendor/daterange/js/moment.min.js') }}" defer></script>
    <script src="{{ URL::asset('/vendor/daterange/js/knockout-3.4.2.js') }}" defer></script>
    <script src="{{ URL::asset('/vendor/daterange/js/daterangepicker.min.js') }}" defer></script>
    <script src="{{ URL::asset('/vendor/daterange/js/sweetalert2.all.min.js') }}" defer></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.all.min.js"></script> --}}

    <script>

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $(document).ready(function() {
            // Event handler for a button click
            $("#connectAmazon").click(function() {
                // Data to be sent in the POST request
                var postData = {
                    region_id: "NA"
                };

                // Make the AJAX POST request
                $.ajax({
                    type: "POST",
                    url: "{{ route('connect_amazon') }}", // Replace with your Laravel route
                    data: postData,
                    success: function(response) {
                        // Handle the success response
                        $("#result").html(response);
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        // Handle any errors
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@endsection
