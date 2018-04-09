<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" >
  <title></title>
  <link rel="stylesheet" type="text/css" href="./css/iconfont/iconfont.css" media="all" />
  <link rel="stylesheet" type="text/css" href="./js/jquery-ui-1.12.1.custom/jquery-ui.css" media="all" />
  <link rel="stylesheet" type="text/css" href="./css/main.css" media="all" />
</head>
<body>
    <div ui-view="sidebar"></div>
    <div ui-view="main"></div>
    <div ui-view="aside"></div>
    <script type="text/javascript">
        // WEB_SOCKET_SWF_LOCATION = "./js/web-socket-js-master/WebSocketMain.swf";
    </script>
    <script type="text/javascript" src="./js/require.js" data-main="./build/pro.js"></script>
</body>
</html>
