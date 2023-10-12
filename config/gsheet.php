<?php

return [
	'token' => [
		'db' => false,
		'file' => 'gtoken',
		'path' => storage_path('app/'),
	],

	'config' => [
		'callback' => env('GOOGLE_AUTH_CALLBACK'),
		'keys'     => [
                    'id' => env('GOOGLE_CLIENT_ID'),
                    'secret' => env('GOOGLE_CLIENT_SECRET'),
                ],
		'scope'    => 'https://www.googleapis.com/auth/spreadsheets',
		'authorize_url_parameters' => [
			'approval_prompt' => 'force',
			'access_type' => 'offline'
		]
	],
];
