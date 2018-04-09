<div style="width: 180px; box-sizing: border-box; padding: 25px 8px; font-size: 12px; line-height: 1.2; margin: 0 auto;">
    <!-- 标题 -->
    <div style="text-align: center;">
        <p>&gt;&gt;简单点智能点餐&lt;&lt;</p>
    </div>
    <!-- 台号,时间 -->
    <div style="overflow: hidden; line-height: 1.1;">
        <p>单号：<?=$tradeNo.($addNo?'-'.$addNo:''); ?></p>
        <p>台号：<?=$deskNo; ?>桌</p>
        <p>收银员：<?=$admin; ?></p>
        <p>时间：<?=$time; ?></p>
    </div>
    <table style="border-collapse: collapse;border-spacing: 0; width: 100%; vertical-align: middle; margin: 10px 0; font-size: 12px; line-height: 1;overflow: visible;">
        <!-- head -->
        <tbody><tr style="height: 2px; overflow: visible;">
            <td colspan="3">
                <hr style="margin:0; border-top: 2px dotted #000;">
            </td>
        </tr>
        <tr style="height: 28px;">
            <td style="width: 64%;">品名规格</td>
            <td style="width: 18%; text-align: center; white-space: nowrap;">数量</td>
            <td style="width: 18%; text-align: right; white-space: nowrap;">金额</td>
        </tr>
        <tr style="height: 2px; overflow: visible;">
            <td colspan="3">
                <hr style="margin:0; border-top: 2px dotted #000;">
            </td>
        </tr>
        <!-- list -->
        <?php if(isset($menus['diancai'])): ?>
            <?php foreach($menus['diancai'] as $menu): ?>
                <tr style="height: 45px;">
                    <td><?=$menu['name']; ?><?php if($menu['attrs']): ?><br><span style="font-size: 10px; line-height: 1.4"><?=$menu['attrs']; ?></span><?php endif; ?></td>
                    <td style="text-align: center;"><?=$menu['num']; ?></td>
                    <td style="text-align: right;"><?=$menu['price']; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif ?>
        <!-- 加菜 -->
        <?php if(isset($menus['jiacai'])): ?>
            <?php if($addNo<1): ?>
            <tr style="height: 10px; overflow: visible;">
                <td colspan="3" style="text-align: center; padding-top: 6px;">
                    <hr style="margin:0; border-top: 2px dotted #000;">
                    <span style="position: relative; top: -14px; background: #fff; display: inline-block; padding: 0 20px;">加菜明细</span>
                </td>
            </tr>
            <?php endif;?>
            <?php foreach($menus['jiacai'] as $menu): ?>
                <tr style="height: 45px;">
                    <td><?=$menu['name']; ?><?php if($menu['attrs']): ?><br><span style="font-size: 10px; line-height: 1.4"><?=$menu['attrs']; ?></span><?php endif; ?></td>
                    <td style="text-align: center;"><?=$menu['num']; ?></td>
                    <td style="text-align: right;"><?=$menu['price']; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if($addNo<1): ?>
        <!-- 结算 -->
        <tr style="height: 2px; overflow: visible;">
            <td colspan="3">
                <hr style="margin:0; border-top: 2px dotted #000;">
            </td>
        </tr>
        <tr style="height: 28px;">
            <td>合计金额</td>
            <td></td>
            <td style="text-align: right;"><?=$originalPrice; ?></td>
        </tr>
        <tr style="height: 28px;">
            <td>折扣金额</td>
            <td></td>
            <td style="text-align: right;"><?=$discount; ?></td>
        </tr>
        <tr style="font-size: 16px; font-weight: bold; height: 28px;">
            <td>金额</td>
            <td></td>
            <td style="text-align: right;"><?=$discountPrice; ?></td>
        </tr>
        <?php endif; ?>
    </tbody></table>

    <?php if($addNo<1): ?>
    <!-- foot -->
    <div style="text-align: center; font-size: 14px;">
        <p>谢谢惠顾</p>
        <p>欢迎再次光临</p>
        <p style="font-size: 16px; font-weight: bold;"><?=$shopName; ?></p>
    </div>
    <?php endif; ?>
</div>
