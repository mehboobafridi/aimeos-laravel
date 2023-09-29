<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        
        if (Schema::hasTable('download_orders')) {
            // The "users" table exists...
        }else{
           
        Schema::create('download_orders', function (Blueprint $table) {
            $table->id();
            $table->string('AccountID', 20)->nullable();
            $table->string('OrderID', 30)->nullable();
            $table->string('SaleDate', 15)->nullable();
            $table->string('OrderStatus', 10)->nullable();
            $table->string('AmountPaid', 15)->nullable();
            $table->string('CreatedTime', 10)->nullable();
            $table->string('PaymentMethods', 15)->nullable();
            $table->string('SellerEmail', 30)->nullable();
            $table->string('Name', 20)->nullable();
            $table->string('Street2', 20)->nullable();
            $table->string('CityName', 15)->nullable();
            $table->string('StateOrProvince', 15)->nullable();
            $table->string('Country', 10)->nullable();
            $table->string('Phone', 16)->nullable();
            $table->string('PostalCode', 20)->nullable();
            $table->string('ShippingService', 20)->nullable();
            $table->string('ShippingServiceCost', 15)->nullable();
            $table->string('Total', 15)->nullable();
            $table->string('ItemID', 25)->nullable();
            $table->string('Site', 30)->nullable();
            $table->string('QuantityPurchased', 20)->nullable();
            $table->string('TransactionID', 20)->nullable();
            $table->string('TotalTaxAmount', 15)->nullable();
            $table->string('BuyerUserID', 20)->nullable();
            $table->string('PaidTime', 20)->nullable();
            $table->string('BuyerEmail', 30)->nullable();
            $table->text('BuyerNote')->nullable();
            $table->string('BuyerAdd1', 40)->nullable();
            $table->string('BuyerAdd2', 40)->nullable();
            $table->string('BuyerTAXIDName', 20)->nullable();
            $table->string('BuyerTAXIDValue', 20)->nullable();
            $table->string('ShipToName', 15)->nullable();
            $table->string('ShipToPhone', 15)->nullable();
            $table->string('ShipToAdd1', 40)->nullable();
            $table->string('ShipToAdd2', 40)->nullable();
            $table->string('ShipToCity', 15)->nullable();
            $table->string('ShipToState', 15)->nullable();
            $table->string('ShipToZip', 15)->nullable();
            $table->string('ShipToCountry', 15)->nullable();
            $table->string('CustomLabel', 25)->nullable();
            $table->string('SoldViaPromo', 20)->nullable();
            $table->string('SoldFor', 20)->nullable();
            $table->string('ShippingAndHandling', 20)->nullable();
            $table->string('ItemLoc', 30)->nullable();
            $table->string('ItemZip', 30)->nullable();
            $table->string('ItemCountry', 15)->nullable();
            $table->string('eBayRTR', 20)->nullable();
            $table->string('eBayColRTRType', 20)->nullable();
            $table->string('eBayRefName', 20)->nullable();
            $table->string('eBayRefValue', 20)->nullable();
            $table->string('TaxStatus', 15)->nullable();
            $table->string('SellerColTax', 20)->nullable();
            $table->string('eBayColTax', 20)->nullable();
            $table->string('EWRecyFee', 20)->nullable();
            $table->string('MattRecyFee', 20)->nullable();
            $table->string('BatteryRecyFee', 20)->nullable();
            $table->string('WhiteGoodRecyFee', 20)->nullable();
            $table->string('TireRecyFee', 20)->nullable();
            $table->string('AddFee', 20)->nullable();
            $table->string('TotalPrice', 20)->nullable();
            $table->string('eBayTaxIncTotal', 20)->nullable();
            $table->string('PaymentMethod', 30)->nullable();
            $table->string('PaidDate', 20)->nullable();
            $table->string('ShipDate', 20)->nullable();
            $table->string('ShippedOnDate', 20)->nullable();
            $table->string('FeedbackLeft', 20)->nullable();
            $table->string('FeedbackReceived', 20)->nullable();
            $table->text('MyItemNote')->nullable();
            $table->string('PPTransID', 20)->nullable();
            $table->string('TrackNum', 20)->nullable();
            $table->string('TransID', 20)->nullable();
            $table->string('VarDetail', 20)->nullable();
            $table->string('GSP', 20)->nullable();
            $table->string('GSPRefID', 20)->nullable();
            $table->string('ClickAndCollect', 20)->nullable();
            $table->string('ClickAndCollectRefNum', 20)->nullable();
            $table->string('eBayPlus', 20)->nullable();
            $table->string('AuthVerifProg', 20)->nullable();
            $table->string('AuthVerifSys', 20)->nullable();
            $table->string('AuthVerifOutCome', 20)->nullable();
            $table->string('eBayFP', 20)->nullable();
            $table->string('SalesRecordNumber', 30)->nullable();
            $table->string('BuyerTaxCode', 30)->nullable();
            $table->string('RecycleType', 20)->nullable();
            $table->string('RecycleTaxAmount', 30)->nullable();
            $table->timestamps();
        });
        }

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('download_orders');
    }
}
