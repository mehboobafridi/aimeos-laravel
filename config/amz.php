<?php

use SellingPartnerApi\Endpoint;

return [
	'seller' => env('AMZ_SELLER'),

	'config' => [
            "lwaClientId" => env('AMZ_CLIENT_ID'),
            "lwaClientSecret" => env('AMZ_CLIENT_SECRET'),
            "lwaRefreshToken" => env('AMZ_LWA_REFRESH_TOKEN'),
            "awsAccessKeyId" => env('AWS_ACCESS_KEY_ID'),
            "awsSecretAccessKey" => env('AWS_SECRET_ACCESS_KEY'),
            "endpoint" => Endpoint::EU,
            'roleArn' => env('AWS_ROLE_ARN'),
	],
	'login_url' => [
            "NA" => env('AMZ_CLIENT_ID'),
            "EU" => env('AMZ_CLIENT_SECRET'),
            "FE" => env('AMZ_LWA_REFRESH_TOKEN'),
	],
	'financial' => [
		'shipment' => 'shipment',
		'adjustment' => 'adjustment',
		'interval' => 30,
	],
	'feed_type' => [
		/*
		'adjustment' => [
			'name' => 'GET_FBA_FULFILLMENT_INVENTORY_ADJUSTMENTS_DATA',
			'obj' => \SellingPartnerApi\ReportType::GET_FBA_FULFILLMENT_INVENTORY_ADJUSTMENTS_DATA,
			'sheet' => 'adjustment',
		],
		*/
		'order' => [
			'name' => 'GET_XML_ALL_ORDERS_DATA_BY_ORDER_DATE_GENERAL',
			'obj' => \SellingPartnerApi\ReportType::GET_XML_ALL_ORDERS_DATA_BY_ORDER_DATE_GENERAL,
			'sheet' => 'Orders',
			'interval' => 300,
		],
		'order_tracking' => [
			'name' => 'GET_AMAZON_FULFILLED_SHIPMENTS_DATA_GENERAL',
			'obj' => \SellingPartnerApi\ReportType::GET_AMAZON_FULFILLED_SHIPMENTS_DATA_GENERAL,
			'sheet' => 'Trackings',
			'interval' => 30,
		],

		'toggle' => [
			'sheet' => 'Toggle',
		],

		'order_tracking_fbm' => [
			'name' => 'GET_FLAT_FILE_ACTIONABLE_ORDER_DATA_SHIPPING',
			'obj' => \SellingPartnerApi\ReportType::GET_FLAT_FILE_ACTIONABLE_ORDER_DATA_SHIPPING,
			'sheet' => 'Trackings',
			'interval' => 30,
		],

		'tree' => [
			'name' => 'GET_XML_BROWSE_TREE_DATA',
			'obj' => \SellingPartnerApi\ReportType::GET_XML_BROWSE_TREE_DATA,
			'sheet' => 'BTG',
			'interval' => 30,
		],
		'listing' => [
			'name' => 'GET_MERCHANT_LISTINGS_ALL_DATA',
			'obj' => \SellingPartnerApi\ReportType::GET_MERCHANT_LISTINGS_ALL_DATA,
			'sheet' => 'Listings',
			'interval' => 30,
		],
		'inventory' => [
			'name' => 'GET_FLAT_FILE_OPEN_LISTINGS_DATA',
			'obj' => \SellingPartnerApi\ReportType::GET_FLAT_FILE_OPEN_LISTINGS_DATA,
			'sheet' => 'Inventory',
			'interval' => 30,
		],
		'inventory_fba' => [
			'name' => 'GET_AFN_INVENTORY_DATA',
			'obj' => \SellingPartnerApi\ReportType::GET_AFN_INVENTORY_DATA,
			'sheet' => 'Inv_FBA',
			'interval' => 5,
		],
		'inbound_fba' => [
			'name' => 'GET_RESTOCK_INVENTORY_RECOMMENDATIONS_REPORT',
			'obj' => \SellingPartnerApi\ReportType::GET_RESTOCK_INVENTORY_RECOMMENDATIONS_REPORT,
			'sheet' => 'Inbound',
			'interval' => 5,
		],
		'post_inventory' => [
			'name' => 'POST_INVENTORY_AVAILABILITY_DATA',
			'obj' => \SellingPartnerApi\FeedType::POST_INVENTORY_AVAILABILITY_DATA,
			'sheet' => 'Inventory',
			'interval' => 10,
		],
		'post_price' => [
			'name' => 'POST_PRODUCT_PRICING_DATA',
			'obj' => \SellingPartnerApi\FeedType::POST_PRODUCT_PRICING_DATA,
			'sheet' => 'Price',
			'interval' => 10,
		],
		'handling_time' =>[
			'inStock' => 1,
			'outOfStock' => 7,
		]
	],

	'marketplaces' => [
		'CA' => 'A2EUQ1WTGCTBG2',
		'MX' => 'A1AM78C64UM0Y8',
		'US' => 'ATVPDKIKX0DER',
		'AE' => 'A2VIGQ35RCS4UG',
		'DE' => 'A1PA6795UKMFR9',
		'EG' => 'ARBP9OOSHTCHU',
		'ES' => 'A1RKKUPIHCS9HS',
		'FR' => 'A13V1IB3VIYZZH',
		'GB' => 'A1F83G8C2ARO7P',
		'IN' => 'A21TJRUUN4KGV',
		'IT' => 'APJ6JRA9NG5V4',
		'NL' => 'A1805IZSGTT6HS',
		'PL' => 'A1C3SOZRARQ6R3',
		'SA' => 'A17E79C6D8DWNP',
		'SE' => 'A2NODRKZP88ZB9',
		'TR' => 'A33AVAJ2PDY3EV',
		'SG' => 'A19VAU5U5O7RUS',
		'AU' => 'A39IBJ37TRP1C6',
		'JP' => 'A1VC38T7YXB528',
	],
	];
