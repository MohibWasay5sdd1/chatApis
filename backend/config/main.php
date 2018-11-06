<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'name' => "R&D Modules(Chat)",
    'basePath' => dirname(__DIR__),    
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'class' => 'backend\modules\v1\Module',
            'viewPath' =>'@backend/modules/v1/views'
        ]
    ],
    'components' => [        
        'user' => [
            'identityClass' => 'backend\modules\v1\models\Users',
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
                            'POST,OPTIONS registration-token' =>'request-registration-token',
                            'GET,OPTIONS {id}/contacts' =>'view',
                            'POST,OPTIONS login' => 'login',
                            'POST,OPTIONS socialmedialogin' => 'social-media-login',
                            'POST,OPTIONS {id}/invitation' => 'invitation',
                            'GET,OPTIONS {id}/search' => 'search-user',
                            'POST,OPTIONS reset-password-request' => 'request-password-reset',
                            'POST,OPTIONS reset-password' => 'reset-password',
                            'POST,OPTIONS verify-token' => 'verify-token',
                            'GET,OPTIONS verify-registration' => 'verify-registration',
                            'POST,OPTIONS change-password' => 'change-password'
                    ],
                    'tokens' => [
                            
                            '{id}' => '<id:\\d+>' 
                    ]
                    
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/entries',
                     'extraPatterns'=> [
                             //'PUT,POST,OPTIONS user/{userid}/category/{id}' => 'update',
                             'GET,OPTIONS user/{id}/entry' => 'index',
                             'POST,OPTIONS user/{id}/entry' =>'create',
                             'POST,OPTIONS user/{id}/entrydate' =>'create-date',
                             'GET,OPTIONS user/{id}/randomentry' =>'random-entry',
                             'GET,OPTIONS user/{id}/randomentryofuser' =>'random-entry-of-user',
                             'GET,OPTIONS user/{id}/achievement' => 'achievement-view',
                             'GET,OPTIONS user/{id}/shareachievement' => 'share-achievement',
                             'GET,OPTIONS user/{id}/feedbackachievement' => 'feedback-achievement',
                             //'DELETE,OPTIONS user/{userid}/category/{id}' => 'delete',
                             'POST,OPTIONS app/test-post/{id}' => 'test-post'
                    ],
                    
                    'tokens' => [
                            
                             '{userid}' => '<userid:\\d+>' ,
                             '{id}' => '<id:\\d+>' ,
                             '{sid}' => '<sid:\\d+>'
                    ]
                    
                ],
            

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