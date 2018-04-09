<?php

return [
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function($event){
                $response = $event->sender;
                //抛出的异常statusCode=>200and<300均认为Response::$isSuccessful为true
                if($response->isSuccessful) {
                    $message = 'ok';
                } else {
                    $message = isset($response->data['message']) ? $response->data['message'] : 'unknow error';
                    $response->data = null;
                }
                $code = $response->isSuccessful;
                if($response->isSuccessful === false && $response->statusCode === 401){
                    $code = $response->statusCode;
                }
                $response->data = [
                    'code' => $code,
                    'data'=> $response->data,
                    'message'=> $message
                ];
                $response->statusCode = 200;
            }
        ]
    ]
];
