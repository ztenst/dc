<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title></title>
    </head>
    <script type="text/javascript" src="//apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="//apps.bdimg.com/libs/swfobject/2.2/swfobject.js"></script>
    <script type="text/javascript" src="//apps.bdimg.com/libs/socket.io/0.9.16/socket.io.min.js"></script>
    <body>

        <div class="im-default-index">
            <h5><?= '当前为登录状态，可打开多个窗口互发信息查看效果;' ?></h5>
            <p>
                <input type="text" name="neirong" id="neirong" value="">
                <input type="button" id="tijiao" name="name" value="发送">
            </p>
            <p id="chat_list">
            </p>
        </div>


<script type="text/javascript">

    var WEB_SOCKET_SWF_LOCATION = "/WebSocketMain.swf";
    /**
     * 与GatewayWorker建立websocket连接，域名和端口改为你实际的域名端口，
     * 其中端口为Gateway端口，即start_gateway.php指定的端口。
     * start_gateway.php 中需要指定websocket协议，像这样
     * $gateway = new Gateway(websocket://0.0.0.0:7272);
     */
    ws = new WebSocket("wss://jdd.hangjiayun.com:7272");
    ws.onerror = function() {
        $('#chat_list').append('<div>连接失败</div><br>');

    };
    // 服务端主动推送消息时会触发这里的onmessage
    ws.onmessage = function(e){
        // json数据转换成js对象
        var data = eval("("+e.data+")");
        var type = data.type || '';
        switch(type){
            // Events.php中返回的init类型的消息，将client_id发给后台进行uid绑定
            case 'init':
                // 利用jquery发起ajax请求，将client_id发给后端进行uid绑定
                console.log(data);
                $.post('/api/shop/ws/bind', {clientId: data.data.clientId}, function(data){
                }, 'json');
                break;
            // 当mvc框架调用GatewayClient发消息时直接alert出来
            default :
                $('#chat_list').append('<div>'+data.msg+' -- '+ data.datetime+'</div><br>');
        }
    };

    $('#tijiao').click(function(){
        var neirong = $('#neirong').val();
        if(neirong) {
            $.post('<?=$_SERVER["REQUEST_URI"].'/serve.php'?>',{neirong: neirong},function(data){
                console.log($('#neirong').val());
            }, 'json');
        }
    });
</script>


    </body>
</html>
