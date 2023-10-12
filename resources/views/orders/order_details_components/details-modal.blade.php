<div class="modal fade" id="order_details_modal" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="orderModalLabel">Order details</h3>
                <div class="ml-3" style="line-height: 3;">Order ID: # <span class="text-bold"
                        id="order-id-details"></span></div>

                {{-- UNDER DEVELOPMENT ALERT MESSAGE --}}
                {{-- <div class="ml-5">
                    <i class="fas fa-exclamation-triangle mr-1 text-warning"></i>
                    <span class="text-danger">This module is under development!</span>
                </div> --}}
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body p-0" style="background-color: #f1f5f7d9;">
                                <h5 class="m-2">Order Summary</h5>
                                <table class="table table-borderless custom-tables">
                                    <tbody>
                                        <tr>
                                            <td>Ship by:</td>
                                            <td class="text-bold" id="ship_by" style="color: #8b4513;"></td>
                                            <td>Shipping service:</td>
                                            <td class="text-bold" id="shipping_services"></td>
                                        </tr>
                                        <tr>
                                            <td>Deliver by:</td>
                                            <td class="text-bold" id="deliver_by"></td>

                                            <td>Fulfilment:</td>
                                            <td class="text-bold" id="fulfilment"></td>
                                        </tr>
                                        <tr>
                                            <td>Purchase date:</td>
                                            <td class="text-bold" id="purchase_date"></td>

                                            <td>Sales channel:</td>
                                            <td class="text-bold" id="sales_channel"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body p-0" style="background-color: #f1f5f7d9;">
                                <h5 class="m-2">Ship to</h5>
                                <table class="table table-borderless custom-tables">
                                    <tbody>
                                        <tr>
                                            <td colspan="2"  rowspan="2" class="text-bold" id="buyrer_address"></td>
                                            <td>Buyer:</td>
                                            <td class="text-bold" id="buyer_name"></td>
                                        </tr>
                                        <tr>
                                            {{-- <td>Address type:</td> --}}
                                            {{-- <td class="text-bold" id="address_type"></td> --}}
                                            <td>Phone:</td>
                                            <td class="text-bold" id="buyer_phone"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="card">
                            <div class="card-body p-0" style="background-color: #f1f5f7d9;">
                                <h5 class="m-2">Sales proceeds</h5>
                                <table class="table table-borderless custom-tables">
                                    <tbody>
                                        <tr>
                                            <td>Items total:</td>
                                            <td class="text-bold" id="items_subtotal"></td>
                                        </tr>
                                        <tr>
                                            <td>Tax total:</td>
                                            <td class="text-bold" id="tax_total"></td>
                                        </tr>
                                        <tr class="border border-left-0 border-right-0">
                                            <td class="text-bold">Grand total:</td>
                                            <td class="text-bold" id="grand_total"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table  border-0 custom-tables">
                                        <thead>
                                            <tr class="card-header">
                                                <th width="10%" scope="col">Status</th>
                                                <th width="30%" scope="col">Product Name</th>
                                                <th width="20%" scope="col">More Info</th>
                                                <th width="10%" scope="col">Qty</th>
                                                <th width="10%" scope="col">U/Price</th>
                                                <th width="20%" scope="col">Proceeds</th>
                                            </tr>
                                        </thead>
                                        <tbody id="OrderDetailsBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body" style="background-color: #f1f5f7d9;">
                                <div class="d-flex justify-content-between" id="order_details_action_buttons">
                                </div>
                            </div>
                        </div>
                    </div>



                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
