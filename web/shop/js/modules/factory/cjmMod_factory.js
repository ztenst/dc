define(['angular','websocket'],function() {
    angular.module('cjmMod_factory',[])
    //underscore.js
    .factory('_',function() {
        return window._;
    })
    //全局
    .factory('interfaceGlobal',function($http) {
        return {
            'qiniu' : function() {
                var url = '/api/shop/storage/get-uptoken';
                var $resource = $http.get(url);
                return $resource;
            },
            'user' : function() {
                var url = '/api/shop/admin/user-info';
                var $resource = $http.get(url);
                return $resource;
            },
            //打印反馈
            'print_log' : function(data) {
                var url = '/api/shop/order/print-log';
                var $resource = $http.post(url,data);
                return $resource;
            }
        }
    })
    //首页接口
    .factory('interfaceIndex',function($http) {
        return{
            //首页信息
            'info' : function(data) {
                var url = '/api/shop/index/index';
                var $resource = $http.get(url,{
                    'params' : data
                });
                return $resource;
            },
            //并桌
            'union' : function(data) {
                var url = '/api/shop/index/merge';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //换桌
            'exchange' : function(data) {
                var url = '/api/shop/index/exchange';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //删除并桌接口
            'unionDel' : function(data) {
                var url = '/api/shop/index/remove-merge';
                var $resource = $http.post(url,data);
                return $resource;
            }
        }
    })
    //餐桌详情
    .factory('interfaceDeskDetail',function($http) {
        return {
            //离店清台
            'clear' : function(data) {
                var url = '/api/shop/desk/clear';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //完成付款
            'orderFinish' : function(data) {
                var url = '/api/shop/order/finish-pay';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //预结
            'orderPrice' : function(data) {
                var url = '/api/shop/order/price-confirm';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //退菜
            'orderCancel' : function(data) {
                var url = '/api/shop/order/order-menu-cancel';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //删除已点菜
            'orderDelete' : function(data) {
                var url = '/api/shop/order/order-menu-delete';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //右侧添加已选菜到左侧
            'menuAdd' : function(data) {
                var url = '/api/shop/desk/submit-menu';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //右侧搜索列表
            'menuSearch' : function(data) {
                var url = '/api/shop/desk/menu-list';
                var $resource = $http.get(url,{
                    'params' : data
                });
                return $resource;
            },
            //确认点单接口
            'orderConfirm' : function(data) {
                var url = '/api/shop/order/order-confirm';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //变化信息
            'info' : function(data) {
                var url = '/api/shop/desk/desk-info';
                var $resource = $http.get(url,{
                    'params' : data
                });
                return $resource;
            },
            //左侧加减 
            'menuUpdate' : function(data) {
                var url = '/api/shop/order/order-menu-num-update';
                var $resource = $http.post(url,data);
                return $resource;
            }
        }
    })
    //弹框餐桌管理
    .factory('interfaceDeskManage',function($http) {
        return {
            //添加修改餐桌信息
            'edit' : function(data) {
                var url = '/api/shop/desk/edit';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //删除指定餐桌
            'del' : function(data) {
                var url = '/api/shop/desk/delete';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //获取餐桌列表
            'list' : function() {
                var url = '/api/shop/desk/desk';
                var $resource = $http.get(url);
                return $resource;
            }
        }
    })
    //菜单管理
    .factory('interfaceMenu',function($http) {
        return {
            //删除菜品
            del : function(data) {
                var url = '/api/shop/menu/menu-delete';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //获取菜品单位列表页接口
            unitList : function() {
                var url = '/api/shop/menu/unit-list';
                var $resource = $http.get(url);
                return $resource;
            },
            //菜单新增/编辑
            'edit' : function(data) {
                var url = '/api/shop/menu/menu-edit';
                var $resource = $http.post(url,data);
                return $resource;
            },
            'getEdit' : function(data) {
                var url = '/api/shop/menu/menu-edit';
                var $resource = $http.get(url,{
                    'params' : data
                });
                return $resource;
            },
            //菜品列表
            'list' : function(data) {
                var url = '/api/shop/menu/menu-list';
                var $resource = $http.get(url,{
                    'params' : data
                });
                return $resource;
            },
            //下拉筛选
            'select' : function() {
                var url = '/api/shop/menu/menu-list-info'
                var $resource = $http.get(url);
                return $resource;
            },
            //分类信息获取（单个）
            'cateGet' : function(data) {
                var url = '/api/shop/menu/menu-cate-edit';
                var $resource = $http.get(url,{
                    'params' : data
                });
                return $resource;
            },
            //分类添加
            'cateEdit' : function(data) {
                var url = '/api/shop/menu/menu-cate-edit';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //删除分类
            'cateDelete' : function(data) {
                var url  = '/api/shop/menu/menu-cate-delete';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //分类列表
            'cateList' : function(data) {
                var url = '/api/shop/menu/menu-cate-list';
                var $resource = $http.get(url,{
                    'params' : data
                });
                return $resource;
            },
            //单位删除
            'unitDel' : function(data) {
                var url = '/api/shop/menu/unit-delete';
                var $resource = $http.post(url,data);
                return $resource;
            },
            //单位编辑
            'unitEdit' : function(data) {
                var url = '/api/shop/menu/unit-edit';
                var $resource = $http.post(url,data);
                return $resource;
            }
        }
    })
    //打印接口
    .factory('interfacePrint',function($http) {
        return{
            //重新打印
            'reprint' : function(data) {
                var url = '/api/shop/printer/reprint';
                var $resource = $http.get(url,{
                    'params' : data
                });
                return $resource;
            },
            //打印列表
            'list' : function(data) {
                var url = '/api/shop/printer/list';
                var $resource = $http.get(url,{
                    'params' : data
                });
                return $resource;
            }
        };
    })
    //浮动弹框
    .factory('uiConfirm',function($uiConfirmTip) {
        return $uiConfirmTip;
    })
    .factory('$uiConfirmTip',function() {
        return {
            show : function(data) {
                var target = data.target;
                var msg = data.msg;
                var okFn = data.ok || angular.noop();
                var cancelFn = data.cancel || angular.noop();

                var template = '<div class="confirm-dialog"><div class="msg">' + msg + '</div><div class="btns"><a class="u-btn u-btn-t2" ok-btn>确定</a><a class="u-btn u-btn-t1 dialog-close" cancel-btn>取消</a></div></div>';

                target.powerFloat({
                    'target' : template,
                    'targetMode' : 'remind',
                    'eventType' : 'click',
                    'position' : '6-8',
                    'showCall' : function() {
                        var $body = $('body');
                        $body.off('.confirm');
                        $body.on('click.confirm','[ok-btn]',function() {
                            okFn();
                        });
                        $body.on('click.confirm','[cancel-btn]',function() {
                            $.powerFloat.hide();
                            cancelFn();
                        });
                    }
                });
            },
            hide : function() {
                $.powerFloat.hide();
            }
        }
    })
    //alert
    .factory('uiAlert',function() {
        return function(msg) {
            alert(msg);
        }
    })
    .factory('$uiAlert',function() {
        return function(obj) {
            alert(obj.msg);
        }
    })
    //系统自带确认框
    .factory('$uiConfirm',function($window) {
        return function(obj) {

            var ok = obj.ok || function(){};
            var cancel  = obj.cancel || function(){};

            var flag = $window.confirm(obj.msg);
            if(flag){
                ok();
            }else{
                cancel();
            }
            return flag;
        }
    })
    .factory('uiComponent',function() {
        return {
            'radio' : function(element,opts) {
                opts = opts || {};
                var _default = {
                    'classes' : {
                        'ui-controlgroup' : 'my-ui-controlgroup'
                    }
                };
                var opts = angular.extend({},_default,opts);
                element.controlgroup(opts);
            },
            'checkbox' : function(element,opts) {
                opts = opts || {};
                var _default = {
                    'classes' : {
                        'ui-controlgroup' : 'my-ui-controlgroup-checkbox'
                    }
                };
                var opts = angular.extend({},_default,opts);
                element.controlgroup(opts);
            },
            'select' : function(element) {
                var w = element.data('width') || 120;
                element.selectmenu({
                    width : w
                });
            }
        }
    })
    .factory('websocket',function(CONFIG,$rootScope,$http,$log,$q,http_cache,$rootScope,$location,uiAlert) {
        var socketDomain = $rootScope.globals.user.socketDomain;
        // Write your code in the same way as for native WebSocket:
        var ws = new WebSocket(socketDomain);
        //ws.onopen = function() {
            //ws.send("Hello");  // Sends a message.
        //};
        ws.onmessage = function(e) {
            var data = eval("("+e.data+")");
            var type = data.type || '';
            switch(type){
                // Events.php中返回的init类型的消息，将client_id发给后台进行uid绑定
                case 'init':
                    var clientId = data.data.clientId;
                    // 利用jquery发起ajax请求，将client_id发给后端进行uid绑定
                    $http.post('/api/shop/ws/bind', {clientId: clientId}).success(function(obj) {
                        $log.log(obj);
                    })
                    break;
                case 'ping' : 
                    ws.send("Hello");
                    break;
                // 当mvc框架调用GatewayClient发消息时直接alert出来
                default :
                    http_cache.clear();
                    $log.log(data);
                    $rootScope.$emit(data.type,data);
            }
        };
        ws.onerror = function(e) {
            uiAlert('websocket连接失败！');
        }
        ws.onclose = function() {
            uiAlert('websocket已关闭！请刷新页面重新连接');
        }
        return {
            'on' : function(type,data,callback) {
                var scope = data.scope;
                //先执行一遍，如果缓存请了，就直接重新渲染，否则读缓存里面
                callback();
                var func = $rootScope.$on(type,callback);
                if(scope){
                    scope.$on('$destroy',function() {
                        func();
                        scope = null;
                    });
                }
            }
        };
        //ws.onclose = function() {
            //alert("closed");
        //};

        //return ws;
    })
    .factory('myInterceptor',function(http_cache) {

        var interceptor = {
            'request': function(config) {
                return config;
            },
            'response': function(response) {
                if(angular.lowercase(response.config.method) === 'post'){
                    http_cache.clear();
                }
                return response;
            }
        };
        return interceptor;
    })
    .factory('http_cache',function($cacheFactory) {
        var http_cache = $cacheFactory.get('$http');
        return {
            'clear' : function() {
                http_cache.removeAll();
            }
        }
    })
    //设置标题
    .factory('uiTitle',function() {
        return function(title) {
            document.title = title;
        }
    })
    //打印机服务（打印小票）
    .factory('$uiPrint',function(LodopService,$rootScope,$q,$log,$http,uiAlert,interfaceGlobal) {
        var printerServer = $rootScope.globals.user.settings.printerServer;
        var kitchenPrinter = $rootScope.globals.user.settings.kitchenPrinter;
        var delay = LodopService.getLodop(printerServer);
        return {
            '_print' : function(type,orderId,addNo) {
                var url = '/api/shop/order/print';
                var $resource = $http.get(url,{
                    'params' : {
                        type : type,
                        orderId : orderId,
                        addNo : addNo
                    }
                }).then(function(obj) {
                    return obj.data.data;
                });
                return $resource;
            },
            //前台打印机
            'call' : function(orderId,addNo) {
                var self = this;
                delay.then(function(LODOP) {
                    self._print(1,orderId,addNo).then(function(obj) {
                        var html = obj.htmlContent;
                        var printId = obj.printId;

                        LODOP.PRINT_INIT('打印前台小票'); //这里起个任务名称，如"A3桌后厨票"
                        LODOP.SET_PRINT_PAGESIZE(3,'48mm',0.1,'CreateCustomPage');
                        LODOP.ADD_PRINT_HTM(0,0,"48mm","100%",html);       //改这里
                        //LODOP.PREVIEW();//调试时直接预览看效果就行，节省打印纸张，上线后把这行注释，启用下面一行
                        //$log.log(obj);
                        var flag = LODOP.PRINT();
                        if(flag){
                            uiAlert('打印小票成功');
                        }else{
                            uiAlert('打印小票失败');
                        }

                        return {
                            'id' : printId,
                            'success' : !!flag === true ? 1 : 0
                        };
                        
                    }).then(function(obj) {
                        interfaceGlobal.print_log(obj);
                    })
                });
            },
            //后厨打印机
            'call_kitchen' : function(orderId,addNo) {
                var self = this;
                delay.then(function(LODOP) {
                    self._print(2,orderId,addNo).then(function(obj) {
                        var html = obj.htmlContent;
                        var printId = obj.printId;

                        LODOP.PRINT_INIT('打印后厨小票'); //这里起个任务名称，如"A3桌后厨票"
                        LODOP.SET_PRINT_PAGESIZE(3,'48mm',0.1,'CreateCustomPage');
                        LODOP.ADD_PRINT_HTM(0,0,"48mm","100%",html);       //改这里
                        LODOP.SET_PRINTER_INDEXA(kitchenPrinter);
                        //LODOP.PREVIEW();//调试时直接预览看效果就行，节省打印纸张，上线后把这行注释，启用下面一行
                        var flag = LODOP.PRINT();
                        if(flag){
                            uiAlert('打印后厨小票成功');
                        }else{
                            uiAlert('打印后厨小票失败');
                        }
                        return {
                            'id' : printId,
                            'success' : !!flag === true ? 1 : 0
                        };
                    }).then(function(obj) {
                        interfaceGlobal.print_log(obj);
                    })
                });
            },
            //打印后厨内容
            'print_content' : function(html,printId) {
                LODOP.PRINT_INIT('打印后厨小票'); //这里起个任务名称，如"A3桌后厨票"
                LODOP.SET_PRINT_PAGESIZE(3,'48mm',0.1,'CreateCustomPage');
                LODOP.ADD_PRINT_HTM(0,0,"48mm","100%",html);       //改这里
                LODOP.SET_PRINTER_INDEXA(kitchenPrinter);
                var flag = LODOP.PRINT();
                var obj = {
                    'id' : printId,
                    'success' : !!flag === true ? 1 : 0
                };
                interfaceGlobal.print_log(obj);
            },
            //打印清单
            'print_content_index' : function(html,printId,printIndex) {
                LODOP.PRINT_INIT('打印清单'); //这里起个任务名称，如"A3桌后厨票"
                LODOP.SET_PRINT_PAGESIZE(3,'48mm',0.1,'CreateCustomPage');
                LODOP.ADD_PRINT_HTM(0,0,"48mm","100%",html);       //改这里
                if(printIndex != -1){
                    LODOP.SET_PRINTER_INDEXA(printIndex);
                }
                var flag = LODOP.PRINT();
                var obj = {
                    'id' : printId,
                    'success' : !!flag === true ? 1 : 0
                };
                interfaceGlobal.print_log(obj);
                return flag;
            }
        }
    })
});
