@extends('layouts.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('/vendor/daterange/css/responsive.dataTables.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('/vendor/daterange/css/dataTables.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('/vendor/daterange/css/daterangepicker.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('/vendor/daterange/css/sweetalert2.min.css') }}" />

    {{-- ALL CUSTOM STYLES FOR THIS PAGE --}}
    <link href="{{ URL::asset('/assets/css/customized.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('title')
    Orders
@endsection
@section('content')
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

        <div class="row">
            <div class="col-md-6">


            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-9"></div>

                </div>

            </div>
        </div>

    </div>

    <div class="main py-2">




        <div class="card card-body border-0 shadow table-wrapper table-responsive">

            <div class="table-responsive">

                <table class="table table-hover nowrap table-bordered " width="100%" id="myTable">
                    <thead>
                        <tr>
                            <th width="03%" class="border-gray-200">#</th>
                            <th width="10%" class="border-gray-200">Order date</th>
                            <th width="18%" class="border-gray-100">Order details</th>
                            <th width="21%" class="border-gray-200">Product name</th>
                            {{-- <th width="" class="border-gray-200">Buyer details</th> --}}
                            <th width="05%" class="border-gray-200">State</th>
                            <th width="20%" class="border-gray-200">Customer option</th>
                            <th width="10%" class="border-gray-200">Order Status</th>
                            <th width="13%" class="border-gray-200">Action</th>
                        </tr>
                    </thead>
                </table>

            </div>

        </div>


        {{-- create order details modal --}}
        @include('orders.order_details_components.details-modal')


        {{-- MARK AS SHIPPED MODAL --}}
        <div id="create-label" tabindex="-1" role="dialog" aria-labelledby="manual_label" aria-hidden="true"
            class="modal fade text-left out">
            <div role="document" class="modal-dialog modal-lg ">
                <div class="modal-content">
                    <form id="markOrderAsShippedForm" method="POST">
                        <div class="modal-header">
                            <h5 id="manual_label" class="modal-title">Mark Order as Shipped</h5>
                            <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                    aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="hf_order_id" id="hf_order_id_ml">
                            <input type="hidden" name="hf_ship_date" id="hf_ship_date_ml">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id="order_items_container_ml"></div>
                                    </div>
                                </div>
                            </div>
                                <hr />
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="shipDate_ml">Ship Date</label>
                                            <input type="date" id="shipDate_ml" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tracking ID * </label>
                                            <input type="number" name="TrackingId" class="form-control" id="TrackingId"
                                                placeholder="Enter tracking id" required>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Shipping Service*</strong> </label>
                                            <select name="ShippingServiceId" id="ShippingServiceId_ml"
                                                class="form-control" required>
                                                <option disabled selected>Select</option>
                                                <option value="USPS_PTP_FC">USPS First Class</option>
                                                <option value="UPS_PTP_GND">UPS® Ground</option>
                                                <option value="USPS_PTP_PRI">USPS Priority Mail®</option>
                                                <option value="USPS_PTP_PRI_LFRE">USPS Priority Mail L</option>
                                                <option value="USPS_PTP_PRI_CUBIC">USPS Priority Mail C</option>
                                                <option value="USPS_PTP_PSBN">USPS Parcel Select</option>
                                                <option value="USPS_PTP_PRI_PFRE">USPS Priority Mail P</option>
                                                <option value="USPS_PTP_PRI_FRE">USPS Priority Mail® </option>
                                                <option value="UPS_PTP_3DAY_SELECT">UPS 3 Day Select®</option>
                                                <option value="UPS_PTP_2ND_DAY_AIR">UPS 2nd Day Air®</option>
                                                <option value="USPS_PTP_PRI_MFRB">USPS Priority Mail F</option>
                                                <option value="USPS_PTP_PRI_LFRB">USPS Priority Mail® </option>
                                                <option value="USPS_PTP_EXP_FRE">USPS Priority Mail E</option>
                                                <option value="FEDEX_PTP_EXPRESS_SAVER">FedEx Express Saver®</option>
                                                <option value="FEDEX_PTP_SECOND_DAY">FedEx 2Day®</option>
                                                <option value="UPS_PTP_NEXT_DAY_AIR">UPS Next Day Air®</option>
                                                <option value="FEDEX_PTP_PRIORITY_OVERNIGHT">FedEx Priority Overn</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <input type="submit" id="btn_save_label" class="btn btn-warning btn-sm text-dark"
                                        value="Mark as Shipped" />
                                </div>

                        </div>
                        <div class="modal-footer">
                        </div>
                    </form>
                </div>
            </div>
        </div>




    </div>



    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/vendor/daterange/js/moment.min.js') }}" defer></script>
    <script src="{{ URL::asset('/vendor/daterange/js/knockout-3.4.2.js') }}" defer></script>
    <script src="{{ URL::asset('/vendor/daterange/js/daterangepicker.min.js') }}" defer></script>
    <script src="{{ URL::asset('/vendor/daterange/js/sweetalert2.all.min.js') }}" defer></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.all.min.js"></script> --}}

    <script>
        $(document).ready(function() {

            function refreshDataTable() {
                const table = $('#myTable').DataTable();
                table.ajax.reload();
            }


            function calculateAgeString(timeDifference) {
                var seconds = Math.floor(timeDifference / 1000);
                if (seconds < 60) {
                    return seconds === 1 ? '1 second ago' : seconds + ' seconds ago';
                }

                var minutes = Math.floor(seconds / 60);
                if (minutes < 60) {
                    return minutes === 1 ? '1 minute ago' : minutes + ' minutes ago';
                }

                var hours = Math.floor(minutes / 60);
                if (hours < 24) {
                    return hours === 1 ? '1 hour ago' : hours + ' hours ago';
                }

                var days = Math.floor(hours / 24);
                if (days < 7) {
                    return days === 1 ? '1 day ago' : days + ' days ago';
                }

                var weeks = Math.floor(days / 7);
                if (weeks < 4) {
                    return weeks === 1 ? '1 week ago' : weeks + ' weeks ago';
                }

                var months = Math.floor(weeks / 4);
                return months === 1 ? '1 month ago' : months + ' months ago';
            }


            //============ datatable ===========


            var dataTable = $("#myTable").DataTable({
                dom: '<"top"<"left-col"B><"center-col"l><"right-col"f>>rtip',
                lengthMenu: [
                    [25, 50, 100, 150, 300],
                    [25, 50, 100, 150, 300]
                ],
                buttons: [{
                    extend: "colvis",
                    columns: ":not(:first-child)",
                    className: "btn-light",
                    init: function(api, node, config) {
                        $(node).removeClass('btn-secondary')
                    }

                }],
                autoWidth: !1,
                serverSide: !0,
                processing: !0,
                responsive: !1,
                ajax: {
                    url: "{{ route('load_amazon_orders') }}",
                    dataType: "json",
                    type: "POST",
                    bAutoWidth: !1,
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.order_status = 'Unshipped';
                    },
                },
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Loading...',
                    paginate: {
                        previous: '<i class="fas fa-arrow-left"></i>',
                        next: '<i class="fas fa-arrow-right"></i>'
                    }
                },
                columnDefs: [{
                    targets: 1,
                    className: "noVis"
                }, {
                    targets: "_all",
                    className: "nowrap"
                }, {
                    targets: "_all",
                    orderable: !1
                }],
                order: [
                    [1, "desc"]
                ],
                columns: [{
                        data: "id"
                    },
                    {
                        data: "PurchaseDate",
                        render: function(t, a, e) {
                            var date = moment(t);
                            var formattedDate = "<div>" + date.format("YYYY-MM-DD") + "</div>";
                            var formattedTime = "<div>" + date.format("hh:mm:ss A") + "</div>";
                            var ageValue = calculateAgeString(moment().diff(date));
                            var ageHtml = "<div> <span  class='text-bold'>" + ageValue +
                                "</span></div>";

                            return ageHtml + formattedDate + formattedTime;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            var badge_class = (data.OrderStatus === 'Shipped') ? 'success' : (data
                                .OrderStatus === 'Unshipped' || data.OrderStatus ===
                                'PartiallyShipped') ? 'primary' : (data.OrderStatus ===
                                'Canceled') ? 'danger' : 'standard';

                            return '<table class="table table-order">' +
                                '<tbody >' +
                                '<tr>' +
                                '<th class="text-bold">Order-ID:</th>' +

                                '<td> <button onclick="create_order_details_modal(\'' +
                                data.amazon_order_id +
                                '\')" class="link-button">' + data.amazon_order_id +
                                ' </button></td>' +

                                // '<td>' + data.amazon_order_id + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                '<th class="text-bold">Service:</th>' +
                                '<td>' + data.ShipServiceLevel + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                '<th class="text-bold">Order Total:</th>' +
                                '<td>' + data.Amount + '</td>' +
                                '</tr>' +
                                // '<th class="text-bold">Order Status:</th>' +
                                // '<td class="badge badge-soft-' + badge_class + ' font-size-15">' +
                                // data.OrderStatus + '</td>' +
                                '</tr>' +
                                '</tbody>' +
                                '</table>';
                        }
                    },
                    {
                        data: null,
                        render: function(t, a, e) {
                            var d = "";
                            if (e.order_detail.length > 0) {
                                for (var n = 0; n < e.order_detail.length; n++) {
                                    var s = e.order_detail[n],
                                        r = s.ProductName.substring(0, 30),
                                        i = s.ProductName,
                                        l = (parseFloat(s.ItemPrice) + parseFloat(s.ItemTaxAmount))
                                        .toFixed(2),
                                        o = s.is_cancellation_requested,
                                        c = s.cancel_reason;
                                    const formattedProductName = insertLineBreaks(i, 55);

                                    d += '<div class="order-detail-parent mb-2"><table class="" ><thead></thead><tbody><tr><td><div class="cell-body"><div class="myo-list-orders-product-name-cell"><div class=""><div><a href="https://www.amazon.com/gp/product/' +
                                        s.ASIN + '" target="_blank"><div >' + formattedProductName +
                                        '</div></a></div></div><div class=""><div><span class="">ASIN</span>: <b>' +
                                        s.ASIN +
                                        '</b></div></div><div class=""><div><span class="">SKU</span>:  ' +
                                        s.SKU +
                                        '</div></div><div class=""><div><span class="">Quantity</span>:  <b>' +
                                        s.Quantity +
                                        '</b></div></div><div class=""><div><span class="">Item subtotal</span>: ' +
                                        l + "</div></div>", "1" == o && (d +=
                                            '<div class="bg-warning"><div><i class="fas fa-exclamation-triangle mr-1"></i><span class="">Cancellation Request</span>: </div></div><div class="bg-warning" ><div style="font-size: 12px;"><span>Reason</span>: ' +
                                            convertToSentenceCase(c) + "</div></div>"), d +=
                                        "</div></div></td></tr></tbody></table></div>"
                                }
                                d += ""
                            }
                            return '<div class="order-details">' + d + "</div>"
                        }
                    },

                    {
                        data: 'shipping_address_state_or_region'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            var shipment_service = "<div class='text-bold' >" + row
                                .shipment_service_level_category + "</div>";

                            var ship_date =
                                "<div style='font-size: 0.8rem;'> Ship by date: <span>" +
                                moment(row.earliest_ship_date).format("MMM D, YYYY") + " to " +
                                moment(row.latest_ship_date).format("MMM D, YYYY") +
                                "</span></div>";

                            var delivery_date =
                                "<div style='font-size: 0.8rem;'> Delivery date: <span>" +
                                moment(row.earliest_delivery_date).format("MMM D, YYYY") + " to " +
                                moment(row.latest_delivery_date).format("MMM D, YYYY") +
                                "</span></div>";

                            return shipment_service + ship_date + delivery_date;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            var badge_class = (data.OrderStatus === 'Shipped') ? 'success' : (data
                                .OrderStatus === 'Unshipped' || data.OrderStatus ===
                                'PartiallyShipped') ? 'danger' : (data.OrderStatus ===
                                'Canceled') ? 'warning' : 'standard';

                            var shipDate = moment(row.latest_ship_date);
                            var today = moment().startOf('day');
                            var tomorrow = moment().add(1, 'days').startOf('day');

                            var ship_date_formatted = shipDate.format("MMM D, YYYY");
                            var orderStatus =
                                '<div class="badge font-size-12" style="color:#ffff; background-color: rgb(251 0 45 / 75%);">' +
                                data.OrderStatus + '</div>';

                            var warning_message = '';

                            if (shipDate.isSame(today, 'd') || shipDate.isSame(tomorrow, 'd')) {
                                warning_message =
                                    "<div style='font-size: 0.8rem;'> Confirm as ship by</br>" +
                                    ship_date_formatted +
                                    " to avoid</br>late shipment </div>";
                            }

                            return orderStatus + warning_message;
                        }
                    },


                    {
                        data: "PurchaseLabel",
                        render: function(t, a, e) {
                            return '<div class="button-container"><button type="button" onclick="create_manual_label(\'' +
                                e.amazon_order_id +
                                '\',1);" style="cursor: pointer;" class="btn btn-sm btn-warning text-dark rounded-pill border border-secondary">Mark as Shipped</button></div>'
                        }
                    },
                ],
                drawCallback: function(t) {
                    var a = dataTable.page.info();
                    console.log(a), $("#totalpages").text(a.pages);
                    for (var e = "", d = 0, n = a.length, s = 1; s <= a.pages; s++) e +=
                        '<option value="' + (s - 1) + '" data-start="' + d + '" data-length="' + n +
                        '">' + s + "</option>", d += a.length;
                    $("#pagelist").html(e), $("#pagelist").val(a.page)
                }
            });



            $('#pagelist').change(function() {

                var start = $('#pagelist').find(':selected').data('start');

                var length = $('#pagelist').find(':selected').data('length');

                load_data(start, length);

                var page_number = parseInt($('#pagelist').val());

                var test_table = $('#myTable').dataTable();

                test_table.fnPageChange(page_number);

            });


            //============ end of datatable ===========

        });

        //inclue order-details JavaScript functions
        @include('orders.order_details_components.details-js')


        function insertLineBreaks(str, lettersPerLine) {
            let result = '';
            for (let i = 0; i < str.length; i++) {
                result += str[i];
                if ((i + 1) % lettersPerLine === 0) {
                    result += '<br>';
                }
            }
            return result;
        }

        function convertToSentenceCase(text) {
            return text.toLowerCase()
                .split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }


        function fire_sweet_alert(msg, alert_type = 1) {
            var toastMixin = Swal.mixin({
                toast: true,
                icon: 'success',
                title: 'General Title',
                animation: true,
                position: 'top-right',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            if (alert_type == 1) {
                toastMixin.fire({
                    animation: true,
                    title: msg
                });
            } else {
                toastMixin.fire({
                    animation: true,
                    title: 'Error ' + msg,
                    icon: 'error'
                });
            }

        }

        function redraw() {
            jQuery('#myTable').DataTable().ajax.reload();
        }




        function formatDateToString(date, format = null) {
            if (format !== null) {
                return moment(date).format(format);
            }
            return moment(date).format('YYYY-MM-DD HH:mm:ss');
        }

        //Mark as SHIPPED MODULE 
        $("#markOrderAsShippedForm").submit(function(e) {
            e.preventDefault();

            //get order details
            const checkboxes = document.querySelectorAll(
                '#order_items_container_ml input[type="checkbox"]'
            );
            const selectElems = document.querySelectorAll(
                "#order_items_container_ml select"
            );

            const checkedValues = [];

            checkboxes.forEach((checkbox, index) => {
                if (checkbox.checked) {
                    const item = {
                        OrderItemId: checkbox.value,
                        QuantityOrdered: selectElems[index].value,
                    };
                    checkedValues.push(item);
                }
            });

            // Get the values from the input fields
            var order_id = $("#hf_order_id_ml").val();
            var shipDate = $("#shipDate_ml").val();
            var trackingId = $("#TrackingId").val();
            var shippingServiceId = $("#ShippingServiceId_ml").val();
            var serviceName = $("#ShippingServiceId_ml option:selected").text();
            var CarrierName = serviceName.split(" ")[0];

            // Create the data object
            var data = {
                _token: "{{ csrf_token() }}",
                order_id: order_id,
                shipDate_ml: shipDate,
                TrackingId: trackingId,
                ShippingServiceId_ml: shippingServiceId,
                serviceName: serviceName,
                CarrierName: CarrierName,
                LabelItems: checkedValues,
            };

            // Send the AJAX request
            $.ajax({
                url: '{{ route('mark_order_shipped') }}',
                type: "POST",
                data: data,
                beforeSend: function() {
                    $("#create-label").modal("hide");
                    fire_sweet_alert("Resquest submitted, please wait for the response");
                },
                success: function(response) {
                    $("#create-label").modal("hide");
                    if (response.error) {
                        fire_sweet_alert(response.error, 0);
                    } else {
                        $("#markOrderAsShippedForm")[0].reset();
                        fire_sweet_alert(response.message);
                        redraw();
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                },
            });
        });
        //END MANUAL_LABEL SUBMIT FROM

        //create return model of manual label
        function create_manual_label(order_id, quantity) {
            $("#order_details_modal").modal("hide")
            $("#hf_order_id").val(order_id);
            if (quantity > 1) {
                $("#return_quantity").prop("disabled", false);
            } else {
                $("#return_quantity").prop("disabled", true);
            } {
                $.ajax({
                    type: "GET",
                    cache: false,
                    url: "{{ route('get_order_details') }}",
                    contentType: "application/json; charset=utf-8",
                    data: {
                        order_id: order_id,
                    },
                    success: function(data) {
                        var contentBoxHTML = generateContentBox_ml(data);
                        $("#order_items_container_ml").html(contentBoxHTML);
                    },
                    error: function(data) {},
                });
            }

            // $('#order-return').modal('show');
            $("#create-label").modal("show");
        }

        //for manual label
        function generateContentBox_ml(data) {
            // console.log(JSON.stringify(data));
            var content = "";

            $("#hf_order_id_ml").val(data.amazon_order_id);
            $("#hf_ship_date_ml").val(data.earliest_ship_date);
            // $('#shipDate_ml').html(formatDateToString(data.earliest_ship_date, 'YYYY-MM-DD'));
            $("#shipDate_ml").val(
                formatDateToString(data.earliest_ship_date, "YYYY-MM-DD")
            );

            for (var i = 0; i < data.order_details.length; i++) {
                var orderDetail = data.order_details[i];

                var productName = orderDetail.ProductName.substring(0, 20);
                var fullProductName = orderDetail.ProductName;
                var item_price_tax =
                    parseFloat(orderDetail.ItemPrice) +
                    parseFloat(orderDetail.ItemTaxAmount);
                var total_item_price_tax = item_price_tax.toFixed(2);
                productName += orderDetail.ProductName.length > 20 ? "..." : "";
                content +=
                    '<div class="col-md-4 p-1"><div class="row order-detail-parent">';
                content +=
                    '<div class="col-md-12"><span style="font-weight:bold" title="' +
                    fullProductName +
                    '">' +
                    '<a href="https://www.amazon.com/gp/product/' +
                    orderDetail.ASIN +
                    '"' +
                    ' target="_blank">' +
                    '<div title="' +
                    fullProductName +
                    '">' +
                    productName +
                    "</div>" +
                    "</a>" +
                    "</span></div>";
                content +=
                    '<div class="col-md-12"><span  style="font-weight:bold;margin-right:5px;">Qty: </span>' +
                    orderDetail.Quantity +
                    "</div>";
                content +=
                    '<div class="col-md-12"><span style="font-weight:bold;float:left;margin-right:5px;">Price: </span>' +
                    total_item_price_tax +
                    "</div>";
                content +=
                    '<div class="col-md-4 p-2"><div class="form-check"><input type="checkbox" checked id="' +
                    orderDetail.AmazonOrderItemCode +
                    '" class="form-check-input" value="' +
                    orderDetail.AmazonOrderItemCode +
                    '"></div></div>';
                content +=
                    '<div class="col-md-2 pull-right mt-2">Qty:</div><div class="col-md-6 "><select class="form-control small">';
                for (var j = 1; j <= orderDetail.Quantity; j++) {
                    //   content += '<option value="' + j + '"' + (orderDetail.Quantity == j ? ' selected' : '') + '>' + j + '</option>';
                    var selected =
                        parseInt(orderDetail.Quantity) === parseInt(j) ?
                        " selected" :
                        "";
                    content +=
                        '<option value="' + j + '"' + selected + ">" + j + "</option>";
                }
                content += "</select></div>";
                content += "</div></div>";
            }
            return '<div class="row">' + content + "</div>";
        }

        // for custom dimensions- for manual label
        $("#add-dimension_ml").click(function(e) {
            e.preventDefault();
            var height = $("#height_ml").val();
            var width = $("#width_ml").val();
            var length = $("#length_ml").val();

            if (height && width && length) {
                var value = length + "x" + width + "x" + height;
                var option = $("<option>").attr("value", value).text(value);
                $("#dimensions_ml").append(option);

                // clear the input fields
                $("#height_ml").val("");
                $("#width_ml").val("");
                $("#length_ml").val("");
            }
        });
    </script>
@endsection
