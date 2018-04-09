<?php
use yii\helpers\Url;
use app\assets\EchartsAsset;
$this->title = '监控管理';

EchartsAsset::register($this);
 ?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-social-dribbble font-green"></i>
                    <span class="caption-subject font-green bold uppercase">socket连接数实时观测</span>
                    <span class="caption-helper">实时查看当前小程序端与商家后台端与socket服务建立的连接数</span>
                </div>
                <div class="actions">
                    <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">
                        <i class="icon-cloud-upload"></i>
                    </a>
                    <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">
                        <i class="icon-wrench"></i>
                    </a>
                    <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">
                        <i class="icon-trash"></i>
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="chart1" style="height:400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

<?php $this->beginBlock('js'); ?>
    //chart1 begin
    var chart1 = echarts.init($('#chart1')[0]);
    var range = 60;//总共展示的区间时间长度，60秒
    var interval = 3;//间隔请求时间，3秒
    var size = Math.floor(range/interval);

    function getAllClientCount() {
        $.ajax({
            url: "<?=Url::to('client-count'); ?>",
            type: 'post',
            dataType: 'json',
            async: false,
            success: function(d) {
                if(d.data.allClient) {
                    allClientCountData.push(d.data.allClient);
                    if(allClientCountData.length>size) {
                        allClientCountData.shift();
                    }
                }
                if(d.data.guestClient) {
                    guestCountData.push(d.data.guestClient);
                    if(guestCountData.length>size) {
                        guestCountData.shift();
                    }
                }
                if(d.data.shopClient) {
                    shopCountData.push(d.data.shopClient);
                    if(shopCountData.length>size) {
                        shopCountData.shift();
                    }
                }
            }
        });
    }

    var allClientCountData = [];
    var guestCountData = [];
    var shopCountData = [];
    getAllClientCount();

    option = {
        tooltip: {
            trigger: 'axis',
            // formatter: function (params) {
            //     params = params[0];
            //     var date = new Date(params.name);
            //     return date.getDate() + '/' + (date.getMonth() + 1) + '/' + date.getFullYear() + ' : ' + params.value[1];
            // },
            axisPointer: {
                animation: false
            }
        },
        xAxis: {
            type: 'time',
            splitLine: {
                show: false
            }
        },
        yAxis: {
            type: 'value',
            boundaryGap: [0, '100%'],
            splitLine: {
                show: false
            }
        },
        legend: {
            data: ['socket连接数','小程序socket连接数','商家socket连接数']
        },
        series: [
            {
                name: 'socket连接数',
                type: 'line',
                showSymbol: false,
                hoverAnimation: false,
                data: allClientCountData,
                areaStyle: {normal: {}},
                markLine: {
                    data: [
                        {type: 'max', name: '最大值'},
                    ]
                }
            },
            {
                name: '小程序socket连接数',
                type: 'line',
                showSymbol: false,
                hoverAnimation: false,
                data: guestCountData,
                areaStyle: {normal: {}},
                markLine: {
                    data: [
                        {type: 'max', name: '最大值'},
                    ]
                }
            },
            {
                name: '商家socket连接数',
                type: 'line',
                showSymbol: false,
                hoverAnimation: false,
                data: shopCountData,
                areaStyle: {normal: {}},
                markLine: {
                    data: [
                        {type: 'max', name: '最大值'},
                    ]
                }
            }
        ]
    };
    chart1.setOption(option);

    setInterval(function () {
        getAllClientCount();
        chart1.setOption(option);
    }, 3000);
<?php
$this->endBlock();
$this->registerJs($this->blocks['js'], \yii\web\View::POS_END);
?>
</script>
