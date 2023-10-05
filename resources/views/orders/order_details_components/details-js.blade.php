//START REFUND_ORDER SUBMIT FROM
function create_order_details_modal(order_id) {
    {
        $.ajax({
            type: "GET",
            cache: false,
            url: "{{route('get_order_details')}}",
            contentType: "application/json; charset=utf-8",
            data: {
                order_id: order_id,
            },
            success: function (data) {
                generate_order_details_html_content(data);
            },
            error: function (data) {},
        });
    }

    $("#order_details_modal").modal("show");
}

function generate_order_details_html_content(data) {
    var order_id_html = document.getElementById("order-id-details");
    var OrderDetailsBody = document.getElementById("OrderDetailsBody");
    var action_buttons = document.getElementById(
        "order_details_action_buttons"
    );
    var ship_by = document.getElementById("ship_by");
    var shipping_services = document.getElementById("shipping_services");
    var deliver_by = document.getElementById("deliver_by");
    var fulfilment = document.getElementById("fulfilment");
    var purchase_date = document.getElementById("purchase_date");
    var sales_channel = document.getElementById("sales_channel");
    var buyrer_address = document.getElementById("buyrer_address");
    var buyer_name = document.getElementById("buyer_name");
    // var address_type = document.getElementById("address_type");
    var buyer_phone = document.getElementById("buyer_phone");

    var items_subtotal = document.getElementById("items_subtotal");
    var tax_total = document.getElementById("tax_total");
    var grand_total = document.getElementById("grand_total");

    var order_status = data.order_status;

   

    var ship_by_date =
        "<div style='font-size: 0.8rem;'><span>" +
        moment(data.earliest_ship_date).format("MMM D, YYYY") +
        " to " +
        moment(data.latest_ship_date).format("MMM D, YYYY") +
        "</span></div>";

    var delivery_by_date =
        "<div style='font-size: 0.8rem;'><span>" +
        moment(data.earliest_delivery_date).format("MMM D, YYYY") +
        " to " +
        moment(data.latest_delivery_date).format("MMM D, YYYY") +
        "</span></div>";

    var purchaseDate = moment(data.purchase_date);
    var formattedPurchaseDate =
        "<div>" + purchaseDate.format("MMM D,YYYY hh:mm:ss A") + "</div>";

    ship_by.innerHTML = ship_by_date;
    shipping_services.innerHTML = data.shipment_service_level_category;
    deliver_by.innerHTML = delivery_by_date;
    fulfilment.innerHTML = data.fulfillment_channel;

    purchase_date.innerHTML = formattedPurchaseDate;
    sales_channel.innerHTML = data.sales_channel;

    buyer_name.innerHTML = data.buyer_info_buyer_name;
    buyrer_address.innerHTML = `<div>${data.shipping_address_line_1}, ${data.shipping_address_city},
     ${data.shipping_address_state_or_region}, ${data.shipping_address_country_code}</div> `;

    buyer_phone.innerHTML = data.shipping_address_phone;
    buyer_phone.innerHTML = data.shipping_address_phone;

    order_id_html.innerHTML = "";
    OrderDetailsBody.innerHTML = "";
    action_buttons.innerHTML = "";


    var order_id = data.amazon_order_id;
    order_id_html.innerHTML = order_id;
    var totalPrice = 0;
    var totalTax = 0;

    for (var i = 0; i < data.order_details.length; i++) {
        var item = data.order_details[i];

        var tr = document.createElement("tr");
        var product_name = item.ProductName;
        var item_code = item.AmazonOrderItemCode;
        var asin = item.ASIN;
        var sku = item.SKU;
        var totalQuantity = item.Quantity;
        var cancel_reason = item.cancel_reason;

        const ItemPrice = parseFloat(item.ItemPrice);
        const perUnitPrice = parseFloat(ItemPrice / totalQuantity);
        const ItemTaxAmount = parseFloat(item.ItemTaxAmount);
        const perUnitTax = parseFloat(ItemTaxAmount / totalQuantity);
        const ItemTotal = parseFloat(ItemPrice + ItemTaxAmount);

        totalPrice += ItemPrice;
        totalTax += ItemTaxAmount;

        var tdStatus = document.createElement("td");

        if (order_status == "Unshipped" || order_status == "PartiallyShipped") {
            tdStatus.innerHTML = `<div class="badge font-size-12" style="color:#ffff; background-color: rgb(251 0 45 / 65%);"> ${order_status} </div>`;
        } else if (order_status == "Shipped") {
            tdStatus.innerHTML = `<div class="badge font-size-12" style="color:#ffff; background-color: rgb(4 155 35 / 65%);"> ${order_status} </div>`;
        } else if (order_status == "Canceled") {
            tdStatus.innerHTML = `<div class="badge font-size-12" style="color:#ffff; background-color: rgb(0 0 0 / 75%);"> ${order_status} </div>`;
        }

        tr.appendChild(tdStatus);

        var tdProductDetails = document.createElement("td");
        tdProductDetails.innerHTML = `
        <div><a href="https://www.amazon.com/gp/product/${asin}" target="_blank" class="text-primary" id="product_name_${i}">${product_name}</a></div>
        <div><span class="text-bold" id=item_sku_${i}> SKU : ${sku} </span></div>
        <div><span class="text-bold" id=item_asin_${i}> ASIN : ${asin} </span></div>
        <div><span class="d-none" id="cancel_reason${i}">${cancel_reason}</span></div>
        `;
        tr.appendChild(tdProductDetails);

        var tdMoreInfo = document.createElement("td");
        tdMoreInfo.innerHTML = `<div>Item Code: <span>${item_code}</span></div>`;
        tr.appendChild(tdMoreInfo);

        var tdQuantity = document.createElement("td");
        tdQuantity.textContent = totalQuantity;
        tdQuantity.setAttribute("id", `item_qty_${i}`);
        tr.appendChild(tdQuantity);

        var tdUnitPrice = document.createElement("td");
        tdUnitPrice.textContent = perUnitPrice.toFixed(2);
        tr.appendChild(tdUnitPrice);

        var tdProceeds = document.createElement("td");
        tdProceeds.innerHTML = `
        <table class="table table-borderless custom-tables">
        <tbody>
        <tr><td>Item subtotal:</td> <td>${ItemPrice.toFixed(2)}</td></tr>
        <tr><td>Tax:</td> <td>${ItemTaxAmount.toFixed(2)}</td></tr>
        <tr><td>Item total:</td> <td>${ItemTotal.toFixed(2)}</td></tr>
        <tbody/>
        <table/>`;
        tr.appendChild(tdProceeds);
        OrderDetailsBody.appendChild(tr);
    }

    items_subtotal.textContent = parseFloat(totalPrice).toFixed(2);
    tax_total.textContent = parseFloat(totalTax).toFixed(2);
    grand_total.textContent = parseFloat(totalPrice + totalTax).toFixed(2);

    var shiping_buttons_html = document.createElement("div");
    //check if Order details modal is opening on UNSHIPPED/NEW ORDERS Page
    if (order_status == "Unshipped" || order_status == "PartiallyShipped") {
        shiping_buttons_html.innerHTML = `
        <button type="button" id="order_details_confirm_shipment" onclick="create_manual_label(\'${order_id}\',1)"
        class="btn btn-warning btn-sm text-dark font-weight-bold  border border-secondary">Mark as Shipped</button>
        `;
    } else {
        shiping_buttons_html.innerHTML = "";
    }

    action_buttons.appendChild(shiping_buttons_html);


}
