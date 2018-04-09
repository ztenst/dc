<?php
namespace api\modules\shop\services;

use Yii;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use app\models\ext\ShopDesk;
use app\models\ext\ShopMergeDesk;
use app\models\ext\ShopActiveDesk;
use \Exception;

/**
 * 餐桌业务逻辑处理服务层
 */
class DeskService extends Service
{

    /**
     * 换桌业务逻辑处理
     * 2017.06.02与冠希商定交换桌中不能有存在并桌信息的餐桌，只能是普通单桌与普通单桌进行交换
     * @param string $desk1 来源桌桌号
     * @param string $desk2 目标桌桌号
     */
    public function exchangeDesk($deskNumber1, $deskNumber2)
    {
        //注意需要with提前加载其他关联表，否则下面会出问题
        $desks = $this->getDeskByNumber($deskNumber1, $deskNumber2);
        $fromDesk = ArrayHelper::getValue($desks, $deskNumber1);
        $toDesk = ArrayHelper::getValue($desks, $deskNumber2);
        if(!$fromDesk || !$toDesk) {
            throw new Exception('更换的餐桌不存在，请检查');
        }
        if($fromDesk->id==$toDesk->id) {
            throw new Exception('请指定两个不同桌号');
        }
        //两个交换桌中不能有已经参与并桌的餐桌
        if($fromDesk->isMerge || $toDesk->isMerge) {
            throw new Exception('无法对并桌餐桌进行交换');
        }

        $toClear = $toDesk->getIsClear();
        $fromClear = $fromDesk->getIsClear();
        if($fromClear && $toClear) {
            throw new Exception('两个空桌无法进行对换');
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            //redis购物车操作不需要改动，使用的是activeDesk数据的id

            //至少有一桌非空桌需要更换信息
            $fromDeskStatus = $fromDesk->status;
            $fromDesk->status = $toDesk->status;
            $fromActiveDeskId = $fromDesk->active_desk_id;
            $fromDesk->active_desk_id = $toDesk->active_desk_id;
            if($fromActiveInfo = $fromDesk->activeInfo) {
                $fromActiveInfo->desk_id = $toDesk->id;
                if(!$fromActiveInfo->save()) goto error;
                if($fromOrder = $fromActiveInfo->order) {
                    $fromOrder->desk_number = $toDesk->number;
                    $fromOrder->desk_id = $toDesk->id;
                    if(!$fromOrder->save()) goto error;
                }
            }
            // $fromMergeDesks = $fromDesk->mergeSourceDesks;
            // $toMergeDesks = $toDesk->mergeSourceDesks;
            // //性能优化点
            // foreach($fromMergeDesks as $sourceDesk) {
            //     $sourceDesk->merge_target_desk_id = $toDesk->id;
            //     $sourceDesk->save();
            // }
            // foreach($toMergeDesks as $sourceDesk) {
            //     $sourceDesk->merge_target_desk_id = $fromDesk->id;
            //     $sourceDesk->save();
            // }


            $toDesk->status = $fromDeskStatus;
            $toDesk->active_desk_id = $fromActiveDeskId;
            if($toActiveInfo = $toDesk->activeInfo) {
                $toActiveInfo->desk_id = $fromDesk->id;
                if(!$toActiveInfo->save()) goto error;
                if($toOrder = $toActiveInfo->order) {
                    $toOrder->desk_number = $fromDesk->number;
                    $toOrder->desk_id = $fromDesk->id;
                    if(!$toOrder->save()) goto error;
                }
            }

            if(!$fromDesk->save() || !$toDesk->save()) goto error;

            $transaction->commit();
            return true;
            error:
            throw new Exception('更换失败');
        } catch(Exception $e) {
            $transaction->rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 并桌业务逻辑处理
     * 2017-05-27业务规则（并且的关系）：
     * 1.只有空桌可以作为来源桌并入目标桌
     * 2.把B并到A桌，A桌（目标桌）可以是除了被并桌以外的任何状态
     * 3.一桌可以并入任意桌
     * 4.被并桌的餐台点击之后出会出现解除并桌的状态
     * 5.来源桌不能是之前并桌的目标桌
     * @param integer $desk1 餐桌1的桌号
     * @param integer $desk2 餐桌2的桌号
     */
    public function mergeDesk($fromDeskNumber, $toDeskNumber)
    {
        $desks = $this->getDeskByNumber($fromDeskNumber, $toDeskNumber);
        $fromDesk = ArrayHelper::getValue($desks, $fromDeskNumber);
        $toDesk = ArrayHelper::getValue($desks, $toDeskNumber);
        if(!$fromDesk || !$toDesk) {
            throw new Exception('要合并的餐桌不存在，请检查');
        }
        if($fromDesk->id==$toDesk->id) {
            throw new Exception('请指定两个不同桌号');
        }
        //只有空桌可以作为来源桌并入目标桌
        if(!$fromDesk->getIsClear()) {
            throw new Exception('只能对空桌进行并桌操作');
        }
        //把B并到A桌，A桌（目标桌）可以是除了被并桌以外的任何状态
        if($toDesk->isMerge) {
            throw new Exception($toDesk->number.'桌已合并至其他桌，无法进行并桌');
        }
        // if($toDesk->getMergeSourceDesks()->count()>0) {
        //     throw new Exception($toDesk->number.'桌已被合并，请先清桌');
        // }

        try {
            Yii::$app->db->transaction(function() use($fromDesk, $toDesk){
                $fromDesk->merge_target_desk_id = $toDesk->id;
                $fromDesk->status = ShopDesk::STATUS_MERGE;
                if(!$fromDesk->save()) {
                    throw new Exception($this->getModelError($fromDesk, '并桌失败'));
                }
            });
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 移除指定餐桌并桌信息
     * @param integer $deskNumber 来源桌的餐桌桌号
     */
    public function removeMerge($deskNumber)
    {
        $desk = ShopDesk::find()->andWhere(['number'=>$deskNumber])
                                    ->shop($this->context->currentShopId)
                                    ->one();
        if(!$desk) {
            throw new Exception('餐桌不存在');
        }
        if(!$desk->getIsMerge()) {
            throw new Exception('该餐桌未被合并');
        }
        try {
            $this->clearDesk($desk);
        } catch(Exception $e) {
            throw new Exception('并桌解除失败');
        }
    }

    /**
     * 删除餐桌
     * @param integer $deskId 餐桌id
     */
    public function deleteDesk($deskId)
    {
        $desk = ShopDesk::find()->shop($this->context->currentShopId)->andWhere(['id'=>$deskId])->one();
        if(!$desk) {
            throw new Exception('餐桌不存在');
        }
        if(!$desk->isClear) {
            throw new Exception('餐桌使用中，请清桌后再操作');
        }

        if(!$desk->delete()) {
            throw Exception($this->getModelError($desk, '餐桌删除失败'));
        }
    }

    /**
     * 快速开桌
     * 需要自己先判断是否是空桌才能调用
     * @return boolean 开桌成功返回ture，失败返回false
     */
    private function quickOpenDesk(ShopDesk $desk)
    {
        if($desk->getIsClear()) {
            $activeInfo = new ShopActiveDesk([
                'desk_id' => $desk->id,
                'shop_id' => $desk->shop_id,
            ]);
            if($activeInfo->save()) {
                //未来优化点之一，这里有一次save操作
                // $desk->link('activeInfo', $activeInfo);
                $desk->populateRelation('activeInfo', $activeInfo);
                return true;
            } else {
                throw new Exception($desk->number.'开桌失败');
            }
        }
    }

    /**
     * 对餐桌清桌
     * 只需要更改餐桌状态，无需处理关联的订单，因为无法知道此刻是关联的哪个订单。
     * 需要处理订单就去订单列表处理
     */
    public function clearDesk($deskId)
    {
        if($deskId instanceof ShopDesk) {
            $desk = $deskId;
        } else {
            $desk = ShopDesk::find()->with(['mergeSourceDesks'])->shop($this->context->currentShopId)->andWhere(['id'=>$deskId])->one();
        }
        if(!$desk) {
            throw new Exception('餐桌不存在');
        }
        $desk->status = ShopDesk::STATUS_EMPTY;
        $desk->active_desk_id = 0;
        $desk->merge_target_desk_id = 0;
        if($desk->mergeSourceDesks) {
            foreach($desk->mergeSourceDesks as $mergeDesk) {
                $mergeDesk->status = ShopDesk::STATUS_EMPTY;
                $mergeDesk->merge_target_desk_id = 0;
                $mergeDesk->active_desk_id = 0;
                if(!$mergeDesk->save()) {
                    throw new Exception('并桌信息清理失败');
                }
            }
        }
        if(!$desk->save()) {
            throw new Exception($this->getModelError($desk), '操作失败');
        }
        return $desk;
    }


    /**
     * 根据餐桌号获取餐桌
     * @return ShopDesk[]
     */
    private function getDeskByNumber()
    {
        $desks = [];
        if($numbers = func_get_args()) {
            $desks = ShopDesk::find()->shop($this->context->currentShopId)->andWhere(['number'=>$numbers])->indexBy('number')->with('activeInfo')->all();
        }
        return $desks;
    }

    /**
     * 加载获取餐桌模型对象
     * @param  integer $id 餐桌id，编辑时使用
     * @throws Exception 找不到帐号时抛出异常
     * @return ShopDesk 餐桌对象
     */
    private function loadShopDesk($id=0)
    {
        if($id>0) {
            if(($model=ShopDesk::findOne($id))===null) {
                throw new Exception('该数据不存在');
            }
        } else {
            $model = new ShopDesk([
                'shop_id' => Yii::$app->user->identity->shop_id,
            ]);
        }
        $model->scenario = ShopDesk::SCENARIO_SHOP_EDIT;//商家编辑场景
        return $model->loadDefaultValues();
    }
}
