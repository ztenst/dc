<?php
use app\modules\admin\assets\MetronicLoginPageAsset;
use app\base\ActiveForm;
$metronic = MetronicLoginPageAsset::register($this);

$this->title = '简单点餐饮管理系统-总后台';

$this->beginPage();
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
    <!--<![endif]-->
    <!-- BEGIN HEAD -->

    <head>
        <meta charset="utf-8" />
        <title><?=$this->title; ?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <?php $this->head(); ?>
    </head>
    <!-- END HEAD -->

    <body class=" login">
        <?php $this->beginBody(); ?>
        <!-- BEGIN : LOGIN PAGE 5-1 -->
        <div class="user-login-5">
            <div class="row bs-reset">
                <div class="col-md-6 bs-reset mt-login-5-bsfix">
                    <div class="login-bg" style="background-image:url(<?=$metronic->baseUrl; ?>/pages/img/login/bg1.jpg)">
                        <img class="login-logo" src="<?=$metronic->baseUrl; ?>/pages/img/login/logo.png" /> </div>
                </div>
                <div class="col-md-6 login-container bs-reset mt-login-5-bsfix">
                    <div class="login-content">
                        <h1><?=$this->title; ?></h1>
                        <p>  </p>
                        <?php $form = ActiveForm::begin([
                            'options' => ['class'=>'login-form'],
                            'fieldConfig' => [
                                'template' => '{input}',
                                'options' => [
                                    'tag' => null,
                                ]
                            ],
                            'enableClientScript' => false,
                        ]); ?>
                            <div class="alert alert-danger display-hide">
                                <button class="close" data-close="alert"></button>
                                <span></span>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <?=$form->field($loginForm, 'username')->textInput(['class'=>'form-control form-control-solid placeholder-no-fix form-group' ,'data-msg'=>'请输入用户名和密码','placeholder'=>'请输入用户名', 'required'=>'required'])->error(); ?>
                                </div>
                                <div class="col-xs-6">
                                    <?=$form->field($loginForm, 'password')->passwordInput(['class'=>'form-control form-control-solid placeholder-no-fix form-group' ,'data-msg'=>'请输入用户名和密码','placeholder'=>'请输入密码', 'required'=>'required']); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="rem-password">
                                        <label class="rememberme mt-checkbox mt-checkbox-outline">
                                            <?=$form->field($loginForm, 'rememberMe')->checkbox(['value'=>1,], false); ?> 记住我
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-8 text-right">
                                    <div class="forgot-password">
                                        <a href="javascript:;" id="forget-password" class="forget-password">忘记密码?</a>
                                    </div>
                                    <button class="btn green" type="submit">登录</button>
                                </div>
                            </div>
                        <?php ActiveForm::end(); ?>

                    </div>
                    <div class="login-footer">
                        <div class="row bs-reset">
                            <div class="col-xs-5 bs-reset">
                            </div>
                            <div class="col-xs-7 bs-reset">
                                <div class="login-copyright text-right">
                                    <p>Copyright &copy; xxxx 2018</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END : LOGIN PAGE 5-1 -->
        <!--[if lt IE 9]>
<script src="<?=$metronic->baseUrl; ?>/global/plugins/respond.min.js"></script>
<script src="<?=$metronic->baseUrl; ?>/global/plugins/excanvas.min.js"></script>
<script src="<?=$metronic->baseUrl; ?>/global/plugins/ie8.fix.min.js"></script>
<![endif]-->
        <?php $this->endBody(); ?>

    </body>
    <script type="text/javascript">
    // init background slide images
    $('.login-bg').backstretch([
        "<?=$metronic->baseUrl;?>/pages/img/login/bg1.jpg",
        "<?=$metronic->baseUrl;?>/pages/img/login/bg3.jpg",
        "<?=$metronic->baseUrl;?>/pages/img/login/bg2.jpg",
        "<?=$metronic->baseUrl;?>/pages/img/login/bg3.jpg",
        ], {
          fade: 1000,
          duration: 3000
        }
    );
</script>

</html>
<?php $this->endPage(); ?>
