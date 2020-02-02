<?php

return [
    '__name' => 'admin-broadcast',
    '__version' => '0.0.1',
    '__git' => 'git@github.com:getmim/admin-broadcast.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/admin-broadcast' => ['install','update','remove'],
        'theme/admin/broadcast' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'admin' => NULL
            ],
            [
                'broadcast' => NULL
            ],
            [
                'lib-form' => NULL
            ],
            [
                'lib-formatter' => NULL
            ],
            [
                'lib-pagination' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'AdminBroadcast\\Controller' => [
                'type' => 'file',
                'base' => 'modules/admin-broadcast/controller'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'admin' => [
            'adminBroadcast' => [
                'path' => [
                    'value' => '/broadcast'
                ],
                'method' => 'GET',
                'handler' => 'AdminBroadcast\\Controller\\Broadcast::index'
            ],
            'adminBroadcastEdit' => [
                'path' => [
                    'value' => '/broadcast/(:id)',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET|POST',
                'handler' => 'AdminBroadcast\\Controller\\Broadcast::edit'
            ],
            'adminBroadcastRemove' => [
                'path' => [
                    'value' => '/broadcast/(:id)/remove',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'AdminBroadcast\\Controller\\Broadcast::remove'
            ],
            'adminBroadcastItem' => [
                'path' => [
                    'value' => '/broadcast/(:id)/item',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'AdminBroadcast\\Controller\\Broadcast::item'
            ],
            'adminBroadcastContact' => [
                'path' => [
                    'value' => '/broadcast/contact'
                ],
                'method' => 'GET',
                'handler' => 'AdminBroadcast\\Controller\\Contact::index'
            ],
            'adminBroadcastContactEdit' => [
                'path' => [
                    'value' => '/broadcast/contact/(:id)',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET|POST',
                'handler' => 'AdminBroadcast\\Controller\\Contact::edit'
            ],
            'adminBroadcastContactRemove' => [
                'path' => [
                    'value' => '/broadcast/contact/(:id)/remove',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'AdminBroadcast\\Controller\\Contact::remove'
            ],
            'adminBroadcastGroup' => [
                'path' => [
                    'value' => '/broadcast/group'
                ],
                'method' => 'GET',
                'handler' => 'AdminBroadcast\\Controller\\ContactGroup::index'
            ],
            'adminBroadcastGroupEdit' => [
                'path' => [
                    'value' => '/broadcast/group/(:id)',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET|POST',
                'handler' => 'AdminBroadcast\\Controller\\ContactGroup::edit'
            ],
            'adminBroadcastGroupRemove' => [
                'path' => [
                    'value' => '/broadcast/group/(:id)/remove',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'AdminBroadcast\\Controller\\ContactGroup::remove'
            ]
        ]
    ],
    'adminUi' => [
        'sidebarMenu' => [
            'items' => [
                'broadcast' => [
                    'label' => 'Broadcast',
                    'icon' => '<i class="fas fa-bullhorn"></i>',
                    'priority' => 0,
                    'children' => [
                        'all-cast' => [
                            'label' => 'All Cast',
                            'icon'  => '<i class="fas fa-broadcast-tower"></i>',
                            'route' => ['adminBroadcast'],
                            'perms' => 'manage_broadcast'
                        ],
                        'contact' => [
                            'label' => 'Contact',
                            'icon'  => '<i class="fas fa-id-card-alt"></i>',
                            'route' => ['adminBroadcastContact'],
                            'perms' => 'manage_broadcast_contact'
                        ],
                        'contact-group' => [
                            'label' => 'Contact Group',
                            'icon'  => '<i class="fas fa-users"></i>',
                            'route' => ['adminBroadcastGroup'],
                            'perms' => 'manage_broadcast_contact_group'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'libForm' => [
        'forms' => [
            'admin-broadcast.edit' => [
                'text' => [
                    'label' => 'Text',
                    'type' => 'textarea',
                    'rules' => [
                        'required' => true,
                        'length' => [
                            'max' => 160
                        ]
                    ]
                ],
                'target' => [
                    'label' => 'Target Group',
                    'type' => 'select',
                    'rules' => [
                        'required' => true,
                        'exists' => [
                            'model' => 'Broadcast\\Model\\BroadcastContactGroup',
                            'field' => 'id'
                        ]
                    ]
                ],
                'time' => [
                    'label' => 'Schedule',
                    'type' => 'datetime',
                    'rules' => [
                        'required' => true
                    ]
                ]
            ],
            'admin-broadcast.index' => [
                'q' => [
                    'label' => 'Search',
                    'type' => 'search',
                    'nolabel' => true,
                    'rules' => []
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'select',
                    'nolabel' => true,
                    'options' => [
                        0 => 'All',
                        1 => 'Pending',
                        2 => 'Partially Send',
                        3 => 'Done'
                    ],
                    'rules' => []
                ]
            ],
            'admin-broadcast-contact.edit' => [
                'name' => [
                    'label' => 'Name',
                    'type' => 'text',
                    'rules' => [
                        'required' => true 
                    ]
                ],
                'phone' => [
                    'label' => 'Phone',
                    'type' => 'tel',
                    'rules' => [
                        'required' => true,
                        'regex' => '!^(0|62)[0-9]{9,12}$!',
                        'unique' => [
                            'model' => 'Broadcast\\Model\\BroadcastContact',
                            'field' => 'phone',
                            'self'  => [
                                'service' => 'req.param.id',
                                'field'   => 'id'
                            ]
                        ]
                    ]
                ],
                'group' => [
                    'label' => 'Group',
                    'type' => 'checkbox-group',
                    'rules' => []
                ]
            ],
            'admin-broadcast-contact.index' => [
                'q' => [
                    'label' => 'Search',
                    'type' => 'search',
                    'nolabel' => true,
                    'rules' => []
                ]
            ],
            'admin-broadcast-contact-group.edit' => [
                'name' => [
                    'label' => 'Name',
                    'type' => 'text',
                    'rules' => [
                        'required' => true,
                        'unique' => [
                            'model' => 'Broadcast\\Model\\BroadcastContactGroup',
                            'field' => 'name'
                        ]
                    ]
                ]
            ],
            'admin-broadcast-contact-group.index' => [
                'q' => [
                    'label' => 'Search',
                    'type' => 'search',
                    'nolabel' => true,
                    'rules' => []
                ]
            ]
        ]
    ]
];