 
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <form action="http://localhost:8080/frtplus/public/getEligibility" method="post">
                @csrf
                <input type="text" name="eligibility" />
                <input type="submit" value="Call" />
            </form>
         
        </div>
    </div>
</div>

 

<!-- Datatable init js -->
<script>
 
 

 

 $.ajaxSetup({
    headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
 
    var $xml_data = '<setShipmentRequestDetails><sellingPartnerId>ALL7DYOJ1G2QC</sellingPartnerId><setAmazonOrderId>112-8438742-3732210</setAmazonOrderId><setItemList><item><order_item_id>40726335874170</order_item_id><quantity>1</quantity></item></setItemList><setShipFromAddress><name>EON PRODUCTS</name><address_line1>3230 INDUSTRIAL WAY SW</address_line1><district_or_county>US</district_or_county><email>SHIPPING@EONPRO.COM</email><city>SNELLVILLE</city><state_or_province_code>GA</state_or_province_code><postal_code>30039</postal_code><country_code>US</country_code><phone>7708419971</phone></setShipFromAddress><setPackageDimensions><length>1</length><width>1</width><height>1</height><unit>inches</unit></setPackageDimensions><setWeight><value>56</value><unit>oz</unit></setWeight><setShippingServiceOptions><setDeliveryExperience>DeliveryConfirmationWithoutSignature</setDeliveryExperience><setCarrierWillPickUp>false</setCarrierWillPickUp><setLabelFormat>ZPL203</setLabelFormat></setShippingServiceOptions></setShipmentRequestDetails>';

    function call_shipping()
    {
        $.post('http://localhost:8080/frtplus/public/getEligibility',   // url
       { myData: $xml_data }, // data to be submit
       function(data, status, jqXHR) {// success callback

                $('#response').text(data);
        });

    }    
 

$(document).ready(function () {
    
  
    var data = '<setShipmentRequestDetails><sellingPartnerId>ALL7DYOJ1G2QC</sellingPartnerId><setAmazonOrderId>112-8438742-3732210</setAmazonOrderId><setItemList><item><order_item_id>40726335874170</order_item_id><quantity>1</quantity></item></setItemList><setShipFromAddress><name>EON PRODUCTS</name><address_line1>3230 INDUSTRIAL WAY SW</address_line1><district_or_county>US</district_or_county><email>SHIPPING@EONPRO.COM</email><city>SNELLVILLE</city><state_or_province_code>GA</state_or_province_code><postal_code>30039</postal_code><country_code>US</country_code><phone>7708419971</phone></setShipFromAddress><setPackageDimensions><length>1</length><width>1</width><height>1</height><unit>inches</unit></setPackageDimensions><setWeight><value>56</value><unit>oz</unit></setWeight><setShippingServiceOptions><setDeliveryExperience>DeliveryConfirmationWithoutSignature</setDeliveryExperience><setCarrierWillPickUp>false</setCarrierWillPickUp><setLabelFormat>ZPL203</setLabelFormat></setShippingServiceOptions></setShipmentRequestDetails>';
    $('input[name="eligibility"]').val($data)

      
    // $.ajax({
    //     url: 'http://localhost:8080/frtplus/public/testElig_post',
    //     type: 'POST',
    //     contentType: 'application/json; charset=utf-8',
    //     // contentType: 'text/xml; charset=utf-8',
    //     data: {'name' : 'mehboob'},
    //     success: function(data){
    //         console.log(data);
    //     },
    //     error: function(error){
    //         console.log(error);
    //     }
    // });


    // JSON.stringify({
    //     "setShipmentRequestDetails":
    //     {
    //         "setAmazonOrderId": "234-2343233-2343233",
    //         "setSellerOrderId": "some-user-defined-id",
    //         "setItemList": [
    //         {
    //             "order_item_id": "some-order-item-id",
    //             "quantity": "2",
    //             "item_weight":
    //             {
    //                 "value": 2,
    //                 "unit": "oz"
    //             },
    //             "item_description": "2",
    //             "transparency_code_list": "2",
    //             "item_level_seller_inputs_list": "2"
    //         }],
    //         "setShipFromAddress":
    //         {
    //             "name": "",
    //             "addressLine1": "",
    //             "addressLine2": "",
    //             "addressLine3": "",
    //             "district_or_county": "",
    //             "email": "",
    //             "city": "",
    //             "state_or_province_code": "",
    //             "postal_code": "",
    //             "country_code": "",
    //             "phone": ""
    //         },
    //         "setPackageDimensions":
    //         {
    //             "length": 2,
    //             "width": 2,
    //             "height": 2,
    //             "unit": "inches"
    //         },
    //         "setWeight":
    //         {
    //             "value": 32,
    //             "unit": "oz"
    //         },
    //         "setMustArriveByDate": "2022-08-05T12:00:00.000Z",
    //         "setShipDate": "2022-08-05T09:30:00.000Z",
    //         "setShippingServiceOptions":
    //         {
    //             "setDeliveryExperience": "DeliveryConfirmationWithAdultSignature",
    //             "setDeclaredValue":
    //             {
    //                 "amount": 2343,
    //                 "currency_code": "USD"
    //             },
    //             "setCarrierWillPickUp": true,
    //             "setCarrierWillPickUpOption": "CarrierWillPickUp",
    //             "setLabelFormat": "PDF"
    //         },
    //         "setLableCustomization":
    //         {
    //             "custom_text_for_label": "Some custom tax",
    //             "standard_id_for_label": "AmazonOrderId"
    //         }
    //     },
    //     "setShippingOfferingFilter":
    //     {
    //         "include_packing_slip_with_label": true,
    //         "include_complex_shipping_options": true,
    //         "carrier_will_pick_up": true,
    //         "delivery_experience": "DeliveryConfirmationWithAdultSignature"
    //     }
    // })

});
 
 







 
 
</script>

