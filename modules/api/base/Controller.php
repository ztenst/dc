<?php

namespace api\base;

use app\base\ArCache;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\filters\Cors;

class Controller extends \yii\rest\Controller
{
    protected $arCache;
    /**
     * 该类负责将Action中return的数据转换成数组
     */
    public $serializer = [
        'class'=>'yii\rest\Serializer',
        'collectionEnvelope' => 'list',
        'metaEnvelope'=>'page',
        'linksEnvelope'=>'link'
    ];

    public function behaviors()
    {
        // $behaviors = parent::behaviors();
        //该行为在BeforAction前触发，设置Response::format格式
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    //note:当response返回格式为json时，配置的ErrorHandler::$errorAction将不会被执行
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ],
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                ],
            ],
        ];
    }

    public function getArCache()
    {
        if(!$this->arCache){
            $this->arCache = new ArCache('rms_shop_arcache_');
        }
        return $this->arCache;
    }
}
