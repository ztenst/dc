<div style="width: 180px; box-sizing: border-box; padding: 25px 8px; font-size: 12px; line-height: 1.2; margin: 0 auto;">
    <!-- 标题 -->
    <div style="text-align: center;">
        <p>退菜联-厨房</p>
        <p style="font-size: 14px;">单号:<?=$tradeNo; ?></p>
    </div>
    <p>桌号：<?=$deskNo; ?>桌</p>
    <table style="border-collapse: collapse;border-spacing: 0; width: 100%; vertical-align: middle; margin: 16px 0 10px; font-size: 13px; line-height: 1;">
        <tbody>
            <tr style="height: 45px;">
                <td style="width: 80%;"><?=$menu['name']; ?><?php if($menu['attrs']): ?><br><span style="font-size: 10px; line-height: 1.4"><?=$menu['attrs']; ?></span><?php endif;?></td>
                <td style="width: 20%; text-align: right;"><?=$menu['num']; ?></td>
            </tr>
        </tbody>
    </table>
    <p>打印时间：<?=$time; ?></p>
</div>
