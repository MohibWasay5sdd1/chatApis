<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'name' => "Joynal",
    'basePath' => dirname(__DIR__),    
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'class' => 'api\modules\v1\Module',
            'viewPath' =>'@api/modules/v1/views'
        ]
    ],
    'components' => [        
        'user' => [
            'identityClass' => 'api\modules\v1\models\Users',
            'enableAutoLogin' => false,
            'enableSession'  => false,
            'loginUrl' => null
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'request' => [
             'class' => '\yii\web\Request',
             'enableCookieValidation' => false,
             'parsers' => [
             'application/json' => 'yii\web\JsonParser',
             ],
        ],
        'response' => [
                 'format' => yii\web\Response::FORMAT_JSON,
                 'charset' => 'UTF-8',
   
        ],
     
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
               [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/users',
                      'extraPatterns'=> [
                  
                            'PUT,POST,OPTIONS <id:\d+>' => 'update',
                            'PUT,POST,OPTIONS <id:\d+>/updatesettings' => 'update-settings',
                            'POST,OPTIONS' =>'create',
                            'POST,OPTIONS social-account-create' =>'social-account-create',
                            'GET, OPTIONS <id:\d+>' =>'view',
                            'POST,OPTIONS login' => 'login',
                            'POST,OPTIONS check-user' => 'check-user',
                            'POST,OPTIONS reset-password-request' => 'request-password-reset',
                            'POST,OPTIONS reset-password' => 'reset-password',
                            'POST,OPTIONS verify-token' => 'verify-token',
                            'POST,OPTIONS change-password' => 'change-password'
                    ],
                    'tokens' => [
                            
                            '{id}' => '<id:\\w+>' 
                    ]
                    
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/entries',
                     'extraPatterns'=> [
                             //'PUT,POST,OPTIONS user/{userid}/category/{id}' => 'update',
                             'GET,OPTIONS user/{id}/entry' => 'index',
                             'POST,OPTIONS user/{id}/entry' =>'create',
                             'GET,OPTIONS user/{id}/randomentry' =>'random-entry',
                             //'GET,OPTIONS user/{userid}/category/{id}' => 'view',
                             //'DELETE,OPTIONS user/{userid}/category/{id}' => 'delete',
                             'POST,OPTIONS app/test-post' => 'test-post'
                    ],
                    
                    'tokens' => [
                            
                             '{userid}' => '<userid:\\d+>' ,
                             '{id}' => '<id:\\d+>' ,
                             '{sid}' => '<sid:\\d+>'
                    ]
                    
                ],
                //      [
                //     'class' => 'yii\rest\UrlRule',
                //     'controller' => 'v1/equipment-sub-categories',
                //     'extraPatterns'=> [
                //             'PUT,POST,OPTIONS category/{id}/subcategory/{sid}' => 'update',
                //             'GET,OPTIONS category/{id}/subcategory' => 'index',
                //             'GET,OPTIONS category/{id}/subcategory/{sid}' => 'view',
                //             'POST,OPTIONS category/{id}/subcategory' => 'create',
                //             'DELETE,OPTIONS category/{id}/subcategory/{sid}' => 'delete',
                //             ],

                //     'tokens' => [
                            
                //             '{userid}' => '<userid:\\w+>' ,
                //              '{id}' => '<id:\\w+>' ,
                //              '{sid}' => '<sid:\\w+>'
                //     ]
                    
                // ],
                // [
                //     'class' => 'yii\rest\UrlRule',
                //     'controller' => 'v1/equipment-questions',
                //     'extraPatterns'=> [
                        
                //            'GET,OPTIONS subcategory/{id}/questions' => 'index',
                //             //'GET,OPTIONS category/{id}/subcategory/{sid}' => 'view',
                //             'POST,OPTIONS subcategory/{id}/questions' => 'create',
                //             ],

                //     'tokens' => [
                            
                //           '{id}' => '<id:\\w+>' 
                //     ]
                    
                // ],
                // [
                //     'class' => 'yii\rest\UrlRule',
                //     'controller' => 'v1/user-inspections',
                //      'extraPatterns'=> [
                  
                //              'GET,OPTIONS user/{userid}/inspection/{id}' => 'view',
                //              'GET,OPTIONS user/{userid}/inspection' => 'index',
                //              'POST,OPTIONS user/{userid}/category/{id}/inspection' =>'create',
                //              'POST,OPTIONS inspection/{id}/subcategory/{sid}/answer' => 'create-answer-test',
                //              'POST,OPTIONS inspection/{id}/answer' => 'create-answer',
                //              'DELETE,OPTIONS inspection/{id}' => 'delete',

                //              'GET,OPTIONS category/{id}/remarks' => 'remarks',
                //              'POST,OPTIONS inspection/{id}/report' => 'create-report',
                //              'POST,OPTIONS app/array-test' => 'test-array'
                //             ],
                    
                //     'tokens' => [
                            
                //              '{userid}' => '<userid:\\w+>' ,
                //              '{id}' => '<id:\\w+>' ,
                //              '{sid}' => '<sid:\\w+>'
                //     ]
                    
                // ],
                 
                //   [
                //     'class' => 'yii\rest\UrlRule',
                //     'controller' => 'v1/default-equipment-categories',
                //     'extraPatterns'=> [
                //             'PUT <id:\w+>' => 'update'
                //             ],
                //     'tokens' => [
                            
                //             '{id}' => '<id:\\w+>' 
                //     ]
                    
                // ],
                //  [
                //     'class' => 'yii\rest\UrlRule',
                //     'controller' => 'v1/default-equipment-sub-categories',
                //     'extraPatterns'=> [
                  
                //            'POST,OPTIONS category/{id}/subcategory' =>'create',
                //             ],
                //     'tokens' => [
                            
                //             '{id}' => '<id:\\w+>' 
                //     ]
                    
                // ],
                

                // [
                //     'class' => 'yii\rest\UrlRule',
                //     'controller' => 'v1/default-equipment-questions',
                //       'extraPatterns'=> [
                  
                //            'POST,OPTIONS app/subcategory/{id}/questions' =>'create',
                //             ],

                //     'tokens' => [
                            
                //             '{id}' => '<id:\\w+>' 
                //     ]
                    
                // ],
            

                 [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/users',
                      'extraPatterns'=> [
                  
                             'POST,OPTIONS app/test-post' => 'test-post'
                            ],
                    
                ],
                  [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/users',
                  
                    'extraPatterns'=> [
                  
                             'POST,OPTIONS app/change-password' => 'change-password'
                            ],
                    
                ],
                   [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/users',
             
                    'extraPatterns'=> [
                  
                            
                            ],
                    
                ],
              
                 [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/users',
                  
                    'extraPatterns'=> [
                  
                             'POST,OPTIONS app/reset-password-request' => 'request-password-reset',
                             
                             'POST,OPTIONS app/code-change-password' =>'code-change-password'
                            ],
                    
                ],
                 [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/users',
                  
                    'extraPatterns'=> [
                  
                             'POST,OPTIONS app/reset-password' => 'reset-password'
                            ],
                    
                ]
            ],        
        ]
    ],
    'params' => $params,
];