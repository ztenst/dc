define(['angular'],function() {
    angular.module('cjmMod_directive',[])
    //滚动
    .directive('uiScroll',function($log) {
        return {
            'scope' : {
                scrollX : '@'
            },
            'link' : function(scope,element,attr) {
                require(['nicescroll'],function() {
                    element.niceScroll({
                        autohidemode: 'leave',
                        //autohidemode: false,
                        cursorcolor : '#e8e8e8',
                        cursoropacitymin : 0,
                        hidecursordelay : 0,
                        horizrailenabled : scope.scrollX || false
                    });
                    scope.$on('$destroy',function() {
                        element.getNiceScroll().remove();
                    });
                });
            }
        }
    })
    //下拉菜单
    .directive('uiSelect',function(uiComponent) {
        return {
            'link' : function(scope,element,attr) {
                uiComponent.select(element);
            }
        }
    })
    .directive('uiSelectRender',function(uiComponent,$timeout) {
        return {
            'scope' : {
                'data' : '=',
                'name' : '@',
                'value' : '=',
                'width' : '@',
                'datatype' : '@dt',
                'nullmsg' : '@',
                'change' : '&'
            },
            'controller' : function($scope) {
                $scope.width = $scope.width || 100;
                if($scope.value === ''){
                    $scope.value = -1;
                }
            },
            'template' : '<select class="j-ui-selectmenu" datatype="{{datatype}}" nullmsg="{{nullmsg}}" data-width="{{width}}" name="{{data.inputName}}"><option class="select-placeholder u-dn" value="-1" selected disabled>{{data.placeholder}}</option><option value="{{v.value}}" ng-repeat="v in data.list" ng-selected="v.value == value">{{v.name}}</option></select>',
            'link' : function(scope,element,attr) {
                $timeout(function() {
                    var select = element.find('select');
                    uiComponent.select(select);
                    select.on('selectmenuchange',function() {
                        scope.value = this.value;
                        scope.change() && scope.change()(scope.name,this.value);
                    })
                    scope.$on('$destroy',function() {
                        select.selectmenu( "destroy" );
                    })
                })
            }
        }
    })
    .directive('uiRadioGroupRender',function(uiComponent,$timeout) {
        return {
            'replace' : true,
            'scope' : {
                'data' : '=',
                'value' : '=',
                'datatype' : '@dt',
                'nullmsg' : '@'
            },
            'templateUrl' : 'tpl/ui-radio-group-render.html',
            'link' : function(scope,element,attr) {
                $timeout(function() {
                    uiComponent.radio(element);
                },0,false);
            }
        }
    })
    .directive('uiCheckboxGroupRender',function(uiComponent,$timeout,_) {
        return {
            'replace' : true,
            'scope' : {
                'data' : '=',
                'value' : '=',
                'datatype' : '@dt',
                'nullmsg' : '@'
            },
            'controller' : function($scope) {
                var data = $scope.data;
                var value = $scope.value;

                function find_index(name){
                    var index = _.findIndex(data,{
                        'inputName' : name
                    });
                    return index;
                }

                _.each(value,function(v,k) {
                    var index = find_index(v['inputName']);
                    var value = v['value'];
                    data[index]['value'] = value;
                });
            },
            'templateUrl' : 'tpl/ui-checkbox-group-render.html',
            'link' : function(scope,element,attr) {
                scope.fn_is_checked = function(index,value) {
                    return !!value == true;
                }
                $timeout(function() {
                    uiComponent.checkbox(element);
                },0,false);
            }
        }
    })
    //单选框
    .directive('uiRadio',function(uiComponent) {
        return {
            'link' : function(scope,element,attr) {
                uiComponent.radio(element);
            }
        }
    })
    //用户信息头部
    .directive('tplUserheader',function() {
        return {
            'templateUrl' : 'tpl/userheader.html',
            'link' : function() {
                
            }
        }
    })
    //底部信息
    .directive('tplFooter',function() {
        return {
            'templateUrl' : 'tpl/footer.html'
        }
    })
    //换台
    .directive('tplExchange',function($rootScope) {
        return {
            'scope' : {
                'ngIf' : '='
            },
            'templateUrl' : 'tpl/index-exchange.html',
            'controller' : 'deskExchangeController',
            'link' : function(scope,element,attr) {
                scope.fn_close = function() {
                    scope.ngIf = false;
                };

                function showError(msg){
                    scope.page.message = msg;
                }
                function hideError(){
                    scope.page.message = '';
                }

                scope.fn_error_callback = function(data) {
                    var msg = data.msg;
                    if(msg.length){
                        showError(msg);
                    }else{
                        hideError();
                    }
                }

            }
        }
    })
    //并台
    .directive('tplUnion',function($rootScope,$uiAlert) {
        return {
            'scope' : {
                'ngIf' : '=',
                'status' : '=',
                'desk' : '='
            },
            'templateUrl' : 'tpl/index-union.html',
            'controller' : 'deskUnionController',
            'link' : function(scope,element,attr) {
                var STATUS = {
                    'create' : 0,
                    'cancel' : 1
                };
                scope.STATUS = STATUS;

                scope.fn_close = function() {
                    hideDialog();
                };
                scope.fn_iscancel = function() {
                    return scope.page.status == STATUS.cancel;
                };
                function hideDialog(){
                    scope.ngIf = false;
                }

                function clearDialog(){
                    hideError();
                }

                function showError(msg){
                    scope.page.message = msg;
                }
                function hideError(){
                    scope.page.message = '';
                }

                scope.fn_error_callback = function(data) {
                    var msg = data.msg;
                    if(msg.length){
                        showError(msg);
                    }else{
                        hideError();
                    }
                }

            }
        }
    })
    //餐台设置
    .directive('tplZhuosetting',function($rootScope,uiConfirm,$timeout) {
        return {
            'scope' : {
                'ngIf' : '='
            },
            'templateUrl' : 'tpl/index-zhuosetting.html',
            'controller' : 'deskManageController',
        }
    })
    //修改价格折扣
    .directive('tplDiscountprice',function($rootScope,interfaceDeskDetail,$uiAlert) {
        return {
            'scope' : {
                'ngIf' : '=',
                'op' : '=',
                'dp' : '=',
                'oid' : '=',
                'onConfirm' : '&'
            },
            'templateUrl' : 'tpl/index-detail-preprice.html',
            'link' : function(scope,element,attr) {
                var VALUES = {
                    'discount' : 0,
                    'price' : 1
                };

                scope.VALUES = VALUES;

                var originalPrice = scope.op;
                var discountPrice = scope.dp;
                scope.page = {
                    'order_id' : scope.oid,
                    'state' : VALUES['discount'],
                    'originalPrice' : originalPrice,
                    'discountPrice' : discountPrice,
                    'diff' : (originalPrice-discountPrice).toFixed(2)
                };
                scope.page.rate = Math.floor((scope.page.diff / originalPrice) * 100);

                scope.fn_price = function() {
                    scope.page.state = VALUES['price'];
                };
                scope.fn_discount = function() {
                    scope.page.state = VALUES['discount'];
                }
                scope.fn_close = function() {
                    scope.ngIf = false;
                }
                //计算折扣价格
                scope.fn_cal_discount_price = function() {
                    return (scope.page.originalPrice - scope.page.diff).toFixed(2);
                };

                
                scope.numberValid = function(evt) {
                    if((/[\d.]/.test(String.fromCharCode(evt.keyCode)) == false)){
                        evt.preventDefault();
                    }
                }
                //计算利率
                scope.fn_set_rate = function(){
                    if(scope.page.diff){
                        scope.page.rate = Math.floor((scope.page.diff / scope.page.originalPrice) * 100);
                        //if(scope.page.rate < 0){
                            //scope.page.rate = 0;
                            //scope.page.diff = 0;
                        //}
                    }
                }
                //计算减的价格
                scope.fn_set_diff = function(){
                    if(scope.page.rate){
                        scope.page.diff = scope.page.rate * scope.page.originalPrice / 100;
                        //if(scope.page.diff < 0 ){
                            //scope.page.diff = 0;
                            //scope.page.rate = 0;
                        //}
                    }
                }
                //清除折扣
                scope.fn_clear = function() {
                    scope.page.diff = null;
                    scope.page.rate = null;
                }
                //清除临时价格修改
                scope.fn_clear_tmp = function() {
                    scope.page.tmpDiscountPrice = null;
                }
                function showErrorMsg(msg) {
                    element.find('.u-errormsg').text(msg);
                }
                function hideErrorMsg(msg){
                    element.find('.u-errormsg').hide();
                }
                //确认
                scope.fn_confirm = function() {
                    var price = 0;
                    if(scope.page.state === VALUES['discount']){
                        price = scope.fn_cal_discount_price();
                    }
                    if(scope.page.state === VALUES['price']){
                        price = scope.page.tmpDiscountPrice;
                    }
                    if(price < 0){
                        //showErrorMsg('价格不能小于0');
                        return;
                    }

                    interfaceDeskDetail.orderPrice({
                        'order_id' : scope.page.order_id,
                        'price' : price
                    }).success(function(obj) {
                        if(obj.code){
                            scope.fn_close();
                            //回调
                            scope.onConfirm();
                        }else{
                            $uiAlert({
                                'msg':obj.message
                            });
                        }
                    });
                }

            }
        }
    })
    //已点菜单
    .directive('tplIndexDetailHasmenu',function(uiConfirm,$rootScope,$timeout) {
        return {
            scope : true,
            replace : true,
            templateUrl : 'tpl/index-detail-hasmenu.html',
            controller : 'indexDetailHasMenuController'
        }
    })
    //通用的小弹框
    .directive('uiConfirmTip',function($uiConfirmTip) {
        return {
            'scope' : {
                'ok' : '&',
                'cancel' : '&',
                'msg' : '@'
            },
            'link' : function(scope,element,attr) {
                var MSG = {
                    'delete' : '确认要删除？',
                    'tuicai' : '确认要退菜？',
                    'd1' : '确认要删除？分类相关菜将被一起删除'
                };
                scope.msg = MSG[scope.msg] || MSG['delete'];

                $uiConfirmTip.show({
                    'target' : element, 
                    'msg' : scope.msg,
                    'ok' : function() {
                        scope.ok();
                        $uiConfirmTip.hide();
                    },
                    'cancel' : function() {
                        scope.cancel();
                    }
                })
            }
        }
    })
    //校验
    .directive('myValidform',function() {
        return {
            'scope' : {
                'err' : '&',
                'ok' : '&'
            },
            'link' : function(scope,element,attr) {
                function err_callback(obj){
                    if(scope.err()){
                        scope.err()(obj);
                    }
                }
                require(['validform'],function(){
                    element.Validform({
                        tipSweep : true,
                        postonce : true,
                        tiptype: function(msg, o, cssctl) {
                            var itemObj = o.obj;
                            if (o.type === 3) {
                                scope.$emit('validformerrormsg',msg);
                                scope.$emit('validform.errormsg.show',{
                                    'item' : o.obj,
                                    'msg' : msg
                                });
                                err_callback({
                                    'item' : o.obj,
                                    'msg' : msg
                                });
                                scope.$apply();
                            }else{
                                scope.$emit('validformerrormsg','');
                                scope.$emit('validform.errormsg.hide',{
                                    'item' : o.obj,
                                    'msg' : ''
                                });

                                err_callback({
                                    'item' : o.obj,
                                    'msg' : ''
                                });
                                scope.$apply();
                            }
                        },
                        datatype: {},
                        beforeSubmit: function() {
                            scope.ok();
                            scope.$emit('validformsuccess');
                            return false;
                        }
                    })
                })
            }
        }
    })
    //校验显示错误信息
    .directive('myValidformMsg',function($log) {
        return {
            'link' : function(scope,element) {
                scope.$on('validform.errormsg.show',function(evt,obj) {
                    $log.log('错误');
                    obj.item.closest('.ele').find('.u-errormsg').removeClass('u-dn').text(obj.msg);
                })
                scope.$on('validform.errormsg.hide',function(evt,obj) {
                    $log.log('正确');
                    obj.item.closest('.ele').find('.u-errormsg').addClass('u-dn');
                })
            }
        }
    })
    //详情页搜索
    .directive('tplIndexDetailSearch',function() {
        return {
            'scope' : true,
            'templateUrl' : 'tpl/index-detail-search.html',
            'controller' : 'indexDetailSearchController',
            'link' : function(scope,element,attr) {
            }
        }
    })
    //详情页搜索规格选择
    //这里共存scope
    .directive('tplIndexDetailSearchAttr',function(_,uiComponent,$timeout,$rootScope) {
        return {
            'scope' : true,
            'templateUrl' : 'tpl/index-detail-search-attr.html',
            'controller' : 'indexDetailSearchAttrController',
            'link' : function(scope,element,attr) {
                function hideDialog(){
                    element.hide();
                }

                function showDialog(){
                    element.show();
                }

                hideDialog();

                element.on('change',':radio',function() {
                    var flag = check_select_valid();
                    if(flag){
                        hideErrorMsg();
                    }
                    scope.$digest();
                });

                var evt_attr_show = $rootScope.$on('attr.show',function(evt) {
                    showDialog();
                    $timeout(function() {
                        uiComponent.radio(element.find('.u-radio-group'),{
                            'classes' : {
                                'ui-controlgroup' : 'my-attr-controlgroup'
                            }
                        });
                    },0);
                })
                scope.$on('$destroy',function() {
                    evt_attr_show();
                });

                scope.fn_close = function() {
                    hideDialog();
                }


                //获取当前选中的状态
                function get_current_attrs_uid(){
                    var attrs = [];
                    var attrs_name = [];
                    if(element.find(':radio:checked').length){
                        attrs = _.map(element.find(':radio:checked'),function(v,k) {
                            return v.id;
                        });
                        attrs_name = _.map(element.find(':radio:checked'),function(v,k) {
                            return {
                                'k' : $(v).data('k'),
                                'n' : $(v).data('n')
                            }
                        });
                    }
                    return {
                        'uid' : attrs.join('|'),
                        'names' : attrs_name
                    }
                }
                //获取当前选中状态数量
                function get_current_attrs_num(){
                    var data = get_current_attrs_uid();
                    var uid = data.uid;
                    if(uid && scope.page.addList[uid]){
                        return scope.page.addList[uid]['num'];
                    }else{
                        return 0;
                    }
                }

                //设置当前选中状态数量
                function set_current_attrs_num(num){
                    var data = get_current_attrs_uid();
                    var uid = data.uid;
                    var attrs_name = data.names;

                    if(scope.page.addList[uid]){
                        scope.page.addList[uid]['num'] = num;
                    }else{
                        scope.page.addList[uid] = {
                            'num' : num,
                            'names' : attrs_name,
                            'id' : uid
                        };
                    }
                }

                //获取当前状态数量
                scope.fn_get_current_attrs_num = function(){
                    var num = get_current_attrs_num();
                    return num;
                }

                //添加指定类别的数量
                scope.fn_add_attr_num = function() {
                    var flag = check_select_valid();
                    if(!flag) {
                        showErrorMsg();
                        return;
                    };
                    var num = get_current_attrs_num();
                    num ++;
                    set_current_attrs_num(num);
                }
                //删除数量
                scope.fn_minus_attr_num = function() {
                    var num = get_current_attrs_num();
                    if(num <= 0) return;
                    num--;
                    set_current_attrs_num(num);
                }


                function showErrorMsg(){
                    element.find('.u-errormsg').show();
                }
                function hideErrorMsg() {
                    element.find('.u-errormsg').hide();
                }

                //检测是否选择了规格
                function check_select_valid(){
                    var customAttr_length = scope.page.attrFood.attributes.customAttr.length;
                    var sizeAttr = scope.page.attrFood.attributes.sizeAttr.length;
                    var flag = element.find(':radio:checked').length === (customAttr_length + sizeAttr);
                    return flag;
                }
                //保存成功
                scope.fn_save_success = function(id) {
                    //这里显示具体产品信息
                    scope.$emit('attr.add',{
                        'id' : id,
                        'list' : scope.page.addList
                    });
                    hideDialog();
                }
            }
        }
    })
    //菜品列表
    .directive('tplFoodList',function(_) {
        return {
            'replace' : true,
            'templateUrl' : 'tpl/food-list.html',
            'scope' : {
                'list' : '='
            },
            'controller' : function($scope,$uiAlert,interfaceMenu) {
                function find_food_index(id){
                    return _.findIndex($scope.list,{
                        'id' : id
                    });
                }
                function remove_item(id){
                    var index = find_food_index(id);
                    $scope.list.splice(index,1);
                }
                $scope.fn_delete = function(id) {
                    interfaceMenu.del({
                        'id' : id
                    }).success(function(obj) {
                        if(obj.code){
                            remove_item(id);
                        }else{
                            $uiAlert({
                                'msg' : obj.message
                            });
                        }
                    })
                }
            },
            'link' : function(scope,element,attr) {
            }
        }
    })
    //菜单分类表格
    .directive('tplFoodCategoryTable',function(uiConfirm,$timeout,interfaceMenu,$uiAlert,_) {
        return {
            'scope' : {
                'list' : '='
            },
            'templateUrl' : 'tpl/food-category-table.html',
            'controller' : function($scope,interfaceMenu,$uiAlert,_) {
                function find_food_index(id){
                    return _.findIndex($scope.list,{
                        'id' : id
                    });
                }
                function remove_item(id){
                    var index = find_food_index(id);
                    $scope.list.splice(index,1);
                }
                $scope.fn_delete = function(id) {
                    interfaceMenu.cateDelete({
                        'id' : id
                    }).success(function(obj) {
                        if(obj.code){
                            remove_item(id);
                        }else{
                            $uiAlert({
                                'msg' : obj.message
                            });
                        }
                    })
                }
            }
        }
    })
    //监听回车
    .directive('uiKeydownEnter',function() {
        return{
            'scope' : {
                'callback' : '&'
            },
            'link' : function(scope,element,attr) {
                element.bind('keyup',function(evt) {
                    if(evt.keyCode == '13'){
                        scope.callback();
                    }
                })
            }
        }
    })
    //菜单导航
    .directive('tplFoodMenu',function($state) {
        return {
            'replace' : true,
            'templateUrl' : 'tpl/food-menu.html',
            'scope' : true,
            'link' : function(scope,element,attr) {
                scope.is_food_state = function() {
                    return $state.is('food') || $state.is('food.add') || $state.is('food.edit');
                }
                scope.is_food_category = function() {
                    return $state.includes('food.category');
                }
                scope.is_food_unit = function() {
                    return $state.is('food.unit');
                }
            }
        }
    })
    //规格
    .directive('tplFoodAddAttrs',function() {
        return {
            'scope' : {
                'data' : '=',
            },
            'controller' : 'foodAddAttrController',
            'templateUrl' : 'tpl/food-add-attrs.html',
            'link' : function() {
                
            }
        }
    })
    //自定义规格
    .directive('tplFoodAddSelfattrs',function() {
        return {
            'scope' : {
                'data' : '='
            },
            'controller' : 'foodAddSelfattrController',
            'templateUrl' : 'tpl/food-add-selfattrs.html',
        }
    })
    //图片上传
    .directive('tplFoodAddUpload',function($rootScope,$uiAlert) {
        return {
            'scope' : {
                'data' : '=',
                'uploadsuccess' : '&'
            },
            'controller' : function($scope) {
                //初始化数据
                $scope.data = $scope.data || './images/default-upload.png';
            },
            'templateUrl' : 'tpl/food-add-upload.html',
            'link' : function(scope,element,attr) {
                var qiniuDomain = $rootScope.globals.user.qiniuDomain;
                require(['plupload'],function() {
                    //上传图片
                    var domain = qiniuDomain;
                    var _this = this;
                    var uploader = Qiniu.uploader({
                        multi_selection : false,
                        runtimes: 'html5,flash',      // 上传模式，依次退化
                        browse_button: 'j-upload-btn',         // 上传选择的点选按钮，必需
                        // 在初始化时，uptoken，uptoken_url，uptoken_func三个参数中必须有一个被设置
                        // 切如果提供了多个，其优先级为uptoken > uptoken_url > uptoken_func
                        // 其中uptoken是直接提供上传凭证，uptoken_url是提供了获取上传凭证的地址，如果需要定制获取uptoken的过程则可以设置uptoken_func
                        // uptoken : '<Your upload token>', // uptoken是上传凭证，由其他程序生成
                        uptoken_url: '/api/shop/storage/get-uptoken',         // Ajax请求uptoken的Url，强烈建议设置（服务端提供）
                        // uptoken_func: function(file){    // 在需要获取uptoken时，该方法会被调用
                        //    // do something
                        //    return uptoken;
                        // },
                        get_new_uptoken: false,             // 设置上传文件的时候是否每次都重新获取新的uptoken
                        // downtoken_url: '/downtoken',
                        // Ajax请求downToken的Url，私有空间时使用，JS-SDK将向该地址POST文件的key和domain，服务端返回的JSON必须包含url字段，url值为该文件的下载地址
                        //unique_names: true,              // 默认false，key为文件名。若开启该选项，JS-SDK会为每个文件自动生成key（文件名）
                        save_key: true,                  // 默认false。若在服务端生成uptoken的上传策略中指定了sava_key，则开启，SDK在前端将不对key进行任何处理
                        domain: domain,     // bucket域名，下载资源时用到，必需
                        //max_file_size: '100mb',             // 最大文件体积限制
                        flash_swf_url: '/shop/js/plupload/js/Moxie.swf',  //引入flash，相对路径
                        max_retries: 3,                     // 上传失败最大重试次数
                        dragdrop: true,                     // 开启可拖曳上传
                        drop_element: 'container',          // 拖曳上传区域元素的ID，拖曳文件或文件夹后可触发上传
                        chunk_size: '4mb',                  // 分块上传时，每块的体积
                        filters : {
                            max_file_size: '10mb',
                            mime_types: [
                                {title : "Image files", extensions : "jpeg,jpg,gif,png"} // 限定jpg,gif,png后缀上传
                            ]
                        },
                        auto_start: true,                   // 选择文件后自动上传，若关闭需要自己绑定事件触发上传
                        //x_vars : {
                        //    查看自定义变量
                        //    'time' : function(up,file) {
                        //        var time = (new Date()).getTime();
                                  // do something with 'time'
                        //        return time;
                        //    },
                        //    'size' : function(up,file) {
                        //        var size = file.size;
                                  // do something with 'size'
                        //        return size;
                        //    }
                        //},
                        resize: {
                            width: 1000,
                            height: 1000,
                            quality: 70,
                            preserve_headers: false
                        },
                        init: {
                            'Error': function(uploader, errObject) {
                                var code = errObject.code;
                                if (code === plupload.FILE_EXTENSION_ERROR) {
                                    $uiAlert({'msg':'格式不正确，请用jpg、jpeg、gif、png'});
                                } else if (code === plupload.FILE_SIZE_ERROR) {
                                    $uiAlert({'msg:':'文件最大10mb'});
                                } else {
                                    $uiAlert({
                                        'msg' : errObject.msg
                                    });
                                }
                            },
                            'FileUploaded': function(up, file, res) {
                                var obj = angular.fromJson(res);
                                var header_url = domain + obj.key;
                                scope.data = header_url;
                                scope.$digest();
                                //scope.$emit('headerUploadSuccess', obj);
                            }
                            //'Key': function(up, file) {
                                //var key = "";
                                //$.ajax({
                                    //url: '/api/userapi/keyname',
                                    //type: 'GET',
                                    //async: false, //这里应设置为同步的方式
                                    //success: function(data) {
                                        //var ext = Qiniu.getFileExtension(file.name);
                                        //key = data.data + '.' + ext;
                                    //},
                                    //cache: false
                                //});
                                //return key;
                            //}
                        }

                    });
                    return uploader;
                })
            }
        
        }
    })
    //设置placeholder
    .directive('placeholder',function() {
        return {
            'link' : function(scope,element,attr) {
                require(['placeholder'],function() {
                    element.placeholder();
                });
            }
        }
    })
    //禁止选中
    .directive('uiDisableSelect',function() {
        return {
            'link' : function(scope,element,attr) {
                element.addClass('disable-user-select');
            }
        }
    })
    //自动调整高度
    .directive('uiAutoheight',function($timeout,$log) {
        return {
            link : function(scope,element,attr,ctrl) {
                function refresh(){
                    $timeout(function() {
                        var init = 58;
                        var height = element.parent().find('.m-hd').outerHeight();
                        var diff = height - init;
                        var table = 580;
                        var table_height = table - diff;
                        $log.log(table_height);
                        element.height(table_height);
                    })
                }
                scope.$watch('page.data.menuCates',function(newValue,oldValue) {
                    if(newValue != oldValue){
                        refresh();
                    }
                });

                refresh();
            }
        }
    })
});
