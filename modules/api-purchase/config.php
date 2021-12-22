<?php

return [
    '__name' => 'api-purchase',
    '__version' => '0.0.2',
    '__git' => 'git@github.com:getmim/api-purchase.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/api-purchase' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'purchase' => NULL
            ],
            [
                'api' => NULL
            ],
            [
                'cart' => NULL
            ],
            [
                'product' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'ApiPurchase\\Controller' => [
                'type' => 'file',
                'base' => 'modules/api-purchase/controller'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'api' => [
            'apiPurchaseIndex' => [
                'path' => [
                    'value' => '/purchase'
                ],
                'method' => 'GET',
                'handler' => 'ApiPurchase\\Controller\\Purchase::index'
            ],
            'apiPurchaseCreate' => [
                'path' => [
                    'value' => '/purchase'
                ],
                'method' => 'POST',
                'handler' => 'ApiPurchase\\Controller\\Purchase::create'
            ],
            'apiPurchaseSingle' => [
                'path' => [
                    'value' => '/purchase/(:id)',
                    'params' => [
                        'id' => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'ApiPurchase\\Controller\\Purchase::single'
            ],
            'apiPurchaseRemove' => [
                'path' => [
                    'value' => '/purchase/(:id)',
                    'params' => [
                        'id' => 'number'
                    ]
                ],
                'method' => 'DELETE',
                'handler' => 'ApiPurchase\\Controller\\Purchase::remove'
            ],
            'apiPurchaseItem' => [
                'path' => [
                    'value' => '/purchase/(:id)/item',
                    'params' => [
                        'id' => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'ApiPurchase\\Controller\\PurchaseItem::index'
            ]
        ]
    ],
    'libForm' => [
        'forms' => [
            'api-purchase.create' => [
                'items' => [
                    'rules' => [
                        'required' => true,
                        'array' => true
                    ],
                    'children' => [
                        '*' => [
                            'rules' => [
                                'numeric' => [
                                    'min' => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
