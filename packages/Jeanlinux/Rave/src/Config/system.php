<?php

return [
    [
        'key'    => 'sales.paymentmethods.rave',
        'name'   => 'Rave',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'admin::app.admin.system.title',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'admin::app.admin.system.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'active',
                'title'         => 'admin::app.admin.system.status',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'public_key',
                'title'         => 'Rave Public Key',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'secret_key',
                'title'         => 'Rave Secret Key',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'store_title',
                'title'         => 'Rave Store Title',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
                'info' => 'Your store title will be displayed on the rave payment form'

            ],
            [
                'name'          => 'environment',
                'title'         => 'Rave Environment',
                'type' => 'select' ,
                'options' => [
                    [
                        'title' => 'Test' ,
                        'value' => 'test'
                    ], [
                        'title' => 'Live' ,
                        'value' => 'live'
                    ]
                ],
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
                'info' => 'Default state is test, change to live and update your public and secret keys accordingly'
            ],
            [
                'name'          => 'logo',
                'title'         => 'Rave Logo',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
                'info' => 'Url to your logo'
            ],
            [
                'name'          => 'prefix',
                'title'         => 'Rave Prefix',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true,
                'prefix' => "Prefix for your rave transactions"
            ],
            [
                'name'          => 'secret_hash',
                'title'         => 'Rave Secret Hash',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true,
            ]
        ]
    ]
];