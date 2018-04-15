<?php

namespace app\controllers;

use app\models\ext\ShopShop;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        echo '11';die;
        $this->redirect('/shop/index.php');
    }

    public function actionT()
    {
        $shop = ShopShop::findOne(1);
        print_r($shop->listtime());
        die;
    }
}
