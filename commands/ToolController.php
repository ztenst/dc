<?php
namespace app\commands;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use yii\console\Controller;

class ToolController extends Controller
{
    /**
     * 同步小程序图片文件到七牛
     */
   public function actionQiniuSync()
   {
       $accessKey = 'ucBKr7BxOc2PFVMl6w47-H4qvOwJofNsoPUKBotA';
       $secretKey = '4W7QPR9U3x7gNJ3Z_Rgunw3Ny5QrL274cYqW6NqH';
       $auth = new Auth($accessKey, $secretKey);

       $bucket = 'hangjiayun-web';
       $token = $auth->uploadToken($bucket);
       $uploadMgr = new UploadManager();

       $assets_path = 'D:/svn/diancan/mini/html-2017/dist/assets';

       if(!file_exists($assets_path) || !is_dir($assets_path)){
           return ;
       }
       try{
           $image_path = $assets_path.'/images';
           foreach (glob($image_path.'/*.{jpg,png,gif}', GLOB_BRACE) as $filePath){
               $key = 'rms/assets/images/'.pathinfo($filePath)['basename'];
               list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
               //614图片已存在的错误码，已存在的图片，就不需要再传到七牛上了。
               if ($err !== null && $err->getResponse()->statusCode != 614) {
                   throw new \Exception($err->getResponse()->error);
               }
           }
       }catch (\Exception $e){
           echo $e->getMessage();
           die;
       }
       echo "success\n";
   }

}