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
           
                <h6 class="card-subtitle mb-2 text-muted">Select Site</h6>
                <p class="card-text">Select the site you want to subscribe to:</p>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <select class="custom-select" name="sites" id="sites">
                                <option value="">Select Site</option>
                             
                                @foreach ($sites as $site)
                                    <option value="{{$site->site_code }}">{{ $site->site_name }}</option>
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
                        <h5>Subscribed Regions</h5>
                       <table class="table table-responsive" id="subscribed">
                       
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
    <!-- Include DataTables JavaScript library -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

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
                var selectedValue = $("#sites").val();
                var postData = {
                    site_code: selectedValue
                };

                // Make the AJAX POST request
                $.ajax({
                    type: "POST",
                    url: "{{ route('connect_amazon') }}", // Replace with your Laravel route
                    data: postData,
                    success: function(response) {
                       var uri=response['uri'];
 
                       var newTab = window.open(uri, '_blank');
                        newTab.focus();
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        // Handle any errors
                        console.error(xhr.responseText);
                    }
                });
            });
        });





        $(document).ready(function() {
            $('#subscribed').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('get.data') }}",
                columns: [
                        { data: 'id', name: 'id', title: '#', render: function(data, type, row, meta) {return meta.row + 1;}},
                        { data: 'site_code', name: 'site_code', title: 'Site' },
                        { 
                            data: 'created_at', 
                            name: 'created_at', 
                            title: 'Date',
                            render: function(data, type, row) {
                                var date = new Date(data);
                                return date.toLocaleDateString("en-US", { year: 'numeric', month: 'short', day: 'numeric' });
                            } 
                        },
                        { 
                            data: null,
                            render: function(data, type, row) {
                                // Render the Delete button here
                                return '<button class="btn btn-danger btn-delete btn-sm" data-id="' + row.id + '">x</button>';
                            }
                        }
                    ],
                searching: false,  
                lengthChange: false,  
                info: false,  
                paging: false, 
                ordering: false,
                language: {
                    emptyTable: "Not subscribed yet"
                },
                  
                // Add a click event handler for Delete buttons
                initComplete: function() {
                            $('#subscribed').on('click', '.btn-delete', function() {

                                if(!confirm('are you sure you want to delete'))
                                {
                                    return false;
                                }
                                var id = $(this).data('id');
                                
                                // Send an AJAX request to delete the record
                                $.ajax({
                                    type: 'DELETE',
                                    url: 'subscribed/' + id, // Use the correct route URL
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.success) {
                                            // Handle success, e.g., remove the row from the DataTable
                                            $('#subscribed').DataTable().row($(this).closest('tr')).remove().draw();
                                            // Show a success message
                                            // You can display the message wherever you prefer
                                            alert(response.message);
                                        } else {
                                            // Handle deletion error
                                            alert('Error: ' + response.message);
                                        }
                                    },
                                    error: function(xhr, textStatus, errorThrown) {
                                        // Handle AJAX error
                                        console.error(xhr.responseText);
                                    }
                                });
                            });
                        }
               
            });
        });



    </script>
@endsection
