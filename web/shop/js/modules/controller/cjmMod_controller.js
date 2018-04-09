define(['angular'],function() {
    angular.module('cjmMod_controller',[])
    //首页
    .controller('indexMainController',function($scope,interfaceIndex,$stateParams,websocket,resolve_desks,$log,$state) {
        // function websocket_refresh(){
        //     interfaceIndex.info($stateParams).then(function(obj) {
        //         $scope.page.data = obj.data.data;
        //     });
        // }
        $state.go('food');
        $scope.isall = function() {
            return !$stateParams.status || $stateParams.status == 0;
        }
        $scope.page = {
            union_show : false,
            union_status : 0,
            exchange_show : false,
            opendesk_show : false,
            currentPage : $stateParams['page'] || 1,
            desk : {}
        };
        $scope.page.data = resolve_desks;
        $scope.page.desk = {};
        //并台
        $scope.fn_union = function(from_desk,to_desk) {
            $scope.page.union_show = true;
            $scope.page.union_status = 0;
        }
        $scope.fn_union_edit = function(from_desk,to_desk) {
            $scope.page.union_show = true;
            $scope.page.union_status = 1;
            $scope.page.desk = {
                from_desk : from_desk,
                to_desk : to_desk
            }
        }
        //换台
        $scope.fn_exchange = function() {
            $scope.page.exchange_show = true;
        }
        //开桌
        $scope.fn_opendesk = function() {
            $scope.page.opendesk_show = true;
        }
        //websocket
        // websocket.on('refreshIndexList',{
        //     'scope' : $scope
        // },function(obj) {
        //     websocket_refresh();
        // });
    })
    //餐桌设置
    .controller('deskManageController',function($scope,interfaceDeskManage,$log,_) {

        function change_desk_data(id,number){
            var index = find_desk_index(id);
            $scope.page[index]['number'] = number;
        }

        function find_desk_index(id){
            var index = _.findIndex($scope.page,{
                'id' : id
            });
            return index;
        }

        //标记餐桌修改状态，意思就是没有保存
        function mark_desk_status(id,status){
            var index = find_desk_index(id);
            $scope.page[index]['_status'] = status;
        }

        function mark_desk_status_success(id){
            mark_desk_status(id,0);
        }
        function mark_desk_status_error(id){
            mark_desk_status(id,1);
        }

        function add_desk(id){
            $scope.page.push({
                number : '',
                id : id
            });
        }

        function del_desk(id){
            var index = find_desk_index(id);
            $scope.page.splice(index,1);
        }
        interfaceDeskManage.list().success(function(obj) {
            $scope.page = obj.data;
        })

        $scope.fn_is_desk_err = function(id) {
            var index = find_desk_index(id);
            if($scope.page[index] && $scope.page[index]['_status'] == 1){
                return true;
            }else{
                return false;
            }
            //var status = typeof $scope.page[index]['_status'] == 'undefined' ? 0 : $scope.page[index]['_status'];
            //return status == 1;
        }

        $scope.fn_close = function() {
            $scope.ngIf = false;
        }

        //添加餐桌
        $scope.fn_add_desk = function() {
            interfaceDeskManage.edit({
                'number' : ''
            }).success(function(obj) {
                if(obj.code){
                    add_desk(obj.data.id);
                    $scope.page.message = '';
                }else{
                    $scope.page.message = obj.message;
                    mark_desk_status(id,1);
                }
            });
        };
        //删除餐桌
        $scope.fn_del_desk = function(id) {
            interfaceDeskManage.del({
                'id' : id
            }).success(function(obj) {
                if(obj.code){
                    del_desk(id);
                    $scope.page.message = '';
                }else{
                    $scope.page.message = obj.message;
                    mark_desk_status_error(id);
                }
            })
        }
        //修改餐桌信息
        $scope.fn_edit_desk = function(number,id) {
            interfaceDeskManage.edit({
                'number' : number,
                'id' : id
            }).success(function(obj) {
                if(obj.code){
                    mark_desk_status_success(id);
                    $scope.page.message = '';
                }else{
                    mark_desk_status_error(id);
                    $scope.page.message = obj.message;
                }
            });
        }
    })
    //并台操作
    .controller('deskUnionController',function($scope,interfaceIndex,$rootScope) {
        $scope.page = {};
        $scope.page.status = $scope.status;
        $scope.page.desk = $scope.desk;

        function unionDel(from_desk){
            interfaceIndex.unionDel({
                'from_desk' : from_desk,
            }).success(function(obj) {
                if(obj.code){
                    $scope.ngIf = false;
                }else{
                    $scope.page.message = obj.message;
                }
            });
        }

        function union(from_desk,to_desk){
            interfaceIndex.union({
                'from_desk' : from_desk,
                'to_desk' : to_desk
            }).success(function(obj) {
                if(obj.code){
                    $scope.ngIf = false;
                }else{
                    $scope.page.message = obj.message;
                }
            });
        }

        $scope.fn_union_success = function() {
            union($scope.page.desk.from_desk,$scope.page.desk.to_desk);
        }

        $scope.fn_union_cancel = function() {
            unionDel($scope.page.desk.from_desk);
        };

    })
    //换台操作
    .controller('deskExchangeController',function($scope,interfaceIndex,$rootScope) {
        
        $scope.page = {};

        $scope.fn_exchange_success = function() {
            exchange($scope.page.desk1,$scope.page.desk2)
        }

        function exchange(desk1,desk2){
            interfaceIndex.exchange({
                desk1 : desk1,
                desk2 : desk2
            }).success(function(obj) {
                if(obj.code){
                    $scope.ngIf = false;
                }else{
                    $scope.page.message = obj.message;
                }
            });
        }
    })
    //首页详情
    .controller('indexDetailMainController',function($scope,interfaceDeskDetail,$stateParams,$uiAlert,$uiConfirm,$log,websocket,$rootScope,$uiPrint,$filter,$log,_) {
        $scope.page = {
            'preprice_show' : false,
            'user' : $rootScope.globals.user
        };

        $scope.page.menuInfo_addNo = 0;
        $scope.fn_refresh = refresh;

        function refresh(){
            interfaceDeskDetail.info($stateParams).success(function(obj) {
                $scope.page.data = obj.data;
            });
        }
        refresh();


        //预结
        $scope.fn_preprice = function() {
            $scope.page.preprice_show = true;
        };
        function get_order_id(){
            var order_id = $scope.page.data.menuInfo.orderId;
            return order_id;
        }
        function get_desk_id(){
            var desk_id = $stateParams.id;
            return desk_id;
        }
        //确认订单
        $scope.fn_confirm_order = function() {
            var order_id = get_order_id();
            $uiConfirm({
                msg : '是否要确认订单',
                ok : function() {
                    interfaceDeskDetail.orderConfirm({
                        'order_id' : order_id
                    }).success(function(obj) {
                        if(obj.code){
                            $uiAlert({
                                'msg' : obj.data
                            });
                        }else{
                            $uiAlert({
                                'msg' : obj.message
                            });
                        }
                    })
                },
                cancel : function() {
                    
                }
            })

        }
        //完成付款
        $scope.fn_finish_pay = function() {
            $uiConfirm({
                msg : '是否要完成付款',
                ok : function() {
                    var params = {
                        order_id : get_order_id()
                    };
                    $log.log(params);
                    interfaceDeskDetail.orderFinish(params).success(function(obj) {
                        if(obj.code){
                            $uiAlert({
                                'msg' : obj.data
                            });
                        }else{
                            $uiAlert({
                                'msg' : obj.message
                            });
                        }
                    });
                },
                cancel : function() {
                    //alert(2);
                }
            });
            
        }
        //清台
        $scope.fn_clear_desk = function() {
            $uiConfirm({
                msg : '是否要离店清台',
                ok : function() {
                    var params = {
                        desk_id : get_desk_id()
                    };
                    $log.log(params);
                    interfaceDeskDetail.clear(params).success(function(obj) {
                        if(obj.code){
                            $uiAlert({
                                'msg' : obj.data
                            });
                        }else{
                            $uiAlert({
                                'msg' : obj.message
                            });
                        }
                    });
                },
                cancel : function() {
                    //alert(2);
                }
            });
        }

        //预结成功回调
        $scope.fn_print_ticket_all = function() {
            var orderId = $scope.page.data.menuInfo.orderId;
            $uiPrint.call(orderId,0);
        };
        //打印小票
        $scope.fn_print_ticket= function() {
            $uiConfirm({
                msg : '是否打印小票？',
                ok : function() {
                    var orderId = $scope.page.data.menuInfo.orderId;
                    var addNo = $scope.page.menuInfo_addNo;
                    $uiPrint.call(orderId,addNo);
                }
            });
        };
        //打印后厨小票
        $scope.fn_print_kitchen_ticket = function() {
            $uiConfirm({
                msg : '是否打印后厨小票？',
                ok : function() {
                    var orderId = $scope.page.data.menuInfo.orderId;
                    var addNo = $scope.page.menuInfo_addNo;
                    $uiPrint.call_kitchen(orderId,addNo);
                }
            });
        }

        websocket.on('refreshMenuList',{
            'scope' : $scope
        },function(obj) {
            refresh();
        })
    })
    //已点菜单
    .controller('indexDetailHasMenuController',function($scope,interfaceDeskDetail,_,$uiAlert,$uiPrint) {
        function get_list(){
            var list = $scope.page.data.menuInfo.list;
            return list;
        }
        function find_index(id){
            var list = get_list();
            return _.findIndex(list,{
                'order_menu_id' : id
            });
        }
        //删除选项
        function remove_item(id){
            var list = get_list();
            var index = find_index(id);
            list.splice(index,1);
        }

        $scope.fn_setcat = function(addNo) {
            $scope.page.menuInfo_addNo = addNo;
        }
        //删除
        $scope.fn_delete = function(id) {
            interfaceDeskDetail.orderDelete({
                'id' : id
            }).success(function(obj) {
                if(obj.code){
                    remove_item(id);
                }else{
                    $uiAlert({
                        'msg' : obj.message
                    })
                }
            })
        }
        //退菜
        $scope.fn_cancel = function(id) {
            interfaceDeskDetail.orderCancel({
                'id' : id
            }).success(function(obj) {
                if(obj.code){
                    remove_item(id);
                    var data = obj.data;
                    $uiPrint.print_content(data.htmlContent,data.printId);
                    //remove_item(id);
                }else{
                    $uiAlert({
                        'msg' : obj.message
                    })
                }
            })
        };
        //添加数量
        $scope.fn_menu_add = function(id,num) {
            num++;
            interfaceDeskDetail.menuUpdate({
                'id' :id,
                'num' : num
            }).success(function(obj) {
                if(obj.code){
                    $scope.fn_refresh();
                }else{
                    $uiAlert({
                        'msg' : obj.message
                    });
                }
            })
        }
        //减少数量
        $scope.fn_menu_minus = function(id,num) {
            num--;
            interfaceDeskDetail.menuUpdate({
                'id' :id,
                'num' : num
            }).success(function(obj) {
                if(obj.code){
                    $scope.fn_refresh();
                }else{
                    $uiAlert({
                        'msg' : obj.message
                    });
                }
            })
        };
        
    })
    //首页右侧搜索
    .controller('indexDetailSearchController',function($scope,interfaceDeskDetail,_,$uiAlert) {
        $scope.page = {};
        //关键词
        $scope.page.kw = '';
        $scope.page.cid = '';
        //临时添加的数量
        $scope.page.add_tmp_list = {};
        interfaceDeskDetail.menuSearch().success(function(obj) {
            $scope.page.data = obj.data;
        })
        //清空查询关键词
        $scope.fn_clear_search = function() {
            $scope.page.kw = '';
        };
        //设置筛选分类
        $scope.fn_menu_cid = function(cid) {
            $scope.page.cid = cid;
        }

        //处理要添加食物的数据
        function get_add_food_list_data() {
            var data = $scope.page.add_tmp_list;
            var end_data = _.map(data,function(v,k) {
                var list = v.list;
                var id = v.id;
                var end_list = _.map(v.list,function(v2,k2) {
                    var attrs = _.map(v2.names,function(v,k) {
                        var obj = {};
                        obj[v['k']] = v['n'];
                        return obj;
                    });
                    return {
                        'id' : id,
                        'num' : v2.num,
                        'attrs' : attrs || []
                    }
                });
                //这里进一步筛选
                end_list = _.filter(end_list,function(v) {
                    return v.num > 0;
                });
                return end_list;
            });
            return _.flatten(end_data);
        }
        //添加食物
        $scope.fn_add_food = function() {
            var data = get_add_food_list_data();
            var order_id = $scope.$parent.page.data.menuInfo.orderId;
            interfaceDeskDetail.menuAdd({
                'order_id' : order_id,
                'menu' : angular.toJson(data)
            }).success(function(obj) {
                if(obj.code){
                    $scope.fn_cancel_food();
                }else{
                    $uiAlert({
                        'msg' : obj.message
                    });
                }
            });
        }

        //取消食物
        $scope.fn_cancel_food = function() {
            $scope.page.add_tmp_list = {};
        }

        function get_food_item(id){
            return _.findWhere($scope.page.data.menuList,{
                'id' : id
            });
        }
        //显示规格
        $scope.fn_show_attr = function(id) {
            var list =  ($scope.page.add_tmp_list[id] && $scope.page.add_tmp_list[id].list) || {}
            $scope.$emit('attr.show',{
                'id' : id,
                'list' : list
            });
        }

        $scope.$on('attr.add',function(evt,data) {
            var id = data.id;
            var list = data.list;
            //商品的基本信息
            var info = get_food_item(id);

            //获取基本信息
            $scope.page.add_tmp_list[id] = {
                'list' : list,
                'info' : info,
                'id' : id
            };
            //$scope.page.add_tmp_list[id] = list;
        });

        //获取没有属性的商品的数量
        $scope.fn_get_noattr_num = function(id){
            var list = $scope.page.add_tmp_list;
            if(list[id]){
                return list[id]['list'][id]['num'];
            }else{
                return 0;
            }
        }
        
        //设置没有属性的商品的数量
        function set_noattr_num(id,num){
            var list = $scope.page.add_tmp_list;
            if(list[id]){
                list[id]['list'][id]['num'] = num;
            }else{
                list[id] = {
                    'id' : id,
                    'info' : get_food_item(id),
                    'list' : {}
                };
                list[id]['list'][id] = {
                    'num' : num,
                    'id' : id
                }
            };
        }

        //添加没有属性的商品
        $scope.fn_add_noattr_num = function(id) {
            var num = $scope.fn_get_noattr_num(id);
            num+=1;
            set_noattr_num(id,num);
        }
        //删除没有属性的商品
        $scope.fn_minus_noattr_num = function(id) {
            var num = $scope.fn_get_noattr_num(id);
            num-=1;
            if(num < 0){
                return;
            }
            set_noattr_num(id,num);
        }

        //添加带有属性的数量
        $scope.fn_add_attr_num = function(id,uid) {
            $scope.page.add_tmp_list[id].list[uid]['num'] += 1;
        }
        //删除带有属性的数量
        $scope.fn_minus_attr_num = function(id,uid) {
            $scope.page.add_tmp_list[id].list[uid]['num'] -= 1;
        }

    })
    //弹框多项属性修改
    .controller('indexDetailSearchAttrController',function($scope,_,$rootScope) {
        //当前页面数据
        $scope.page = {};

        function get_food_item(id){
            return _.findWhere($scope.$parent.page.data.menuList,{
                'id' : id
            });
        }

        var evt_attr_show = $rootScope.$on('attr.show',function(evt,data) {
            //拷贝一份
            data = angular.copy(data);

            var id = data.id;
            $scope.page.id = data.id;
            //处理数据标记上id
            $scope.page.attrFood= pre_data(get_food_item(id));
            //初始化列表为空
            $scope.page.addList = data.list;
        });


        function pre_data(data){
            var attrs = data.attributes;
            attrs.customAttr = _.map(attrs.customAttr,function(d,k) {
                d.id = k;
                _.each(d.attrValues,function(sd,sk) {
                    sd.pid = 'customAttr' + k;
                    sd.id = 'customAttr' + k + '_' + sk;
                });
                return d;
            })

            attrs.sizeAttr = _.map(attrs.sizeAttr,function(d,k) {
                d.id = k;
                _.each(d.attrValues,function(sd,sk) {
                    sd.pid = 'sizeAttr' + k;
                    sd.id = 'sizeAttr' + k + '_' + sk;
                });
                return d;
            })
            return data;
        }
        $scope.$on('$destroy',function() {
            evt_attr_show();
        });
    })
    //菜单管理
    .controller('foodMainController',function($scope,interfaceMenu,resolve_list,resolve_category,$stateParams,$state,$rootScope,_) {
        var totalPage = resolve_list.totalPage;
        var currentPage = +$stateParams.page || 1;
        $scope.page = {};
        $scope.page.currentPage = currentPage;
        $scope.page.totalPage = totalPage;

        //console.log($scope.page);
        $scope.page.list = resolve_list.list;
        $scope.page.totalPage = resolve_list.totalPage;
        $scope.page.category = resolve_category;
        $scope.params = $stateParams;
        $scope.page.kw = $stateParams.kw;

        $scope.fn_category_change = function(name,value) {
            $stateParams[name] = value;
            refresh();
        }

        function refresh(){
            //重置页数
            $stateParams['page'] = undefined;
            $state.go('food',$stateParams,{
                'reload' : true
            });
        }
        $scope.fn_search = function() {
            $stateParams['kw'] = $scope.page.kw;
            refresh();
        }
    })
    //菜单分类
    .controller('foodCategoryController',function($scope,resolve_category,_) {
        $scope.page = {};
        $scope.page.list = resolve_category;
    })
    //菜单编辑分类
    .controller('foodCategoryEditController',function($scope,$stateParams,interfaceMenu,$state,$uiAlert) {
        $scope.page = {}
        //设置默认值
        function _default(data){
            if($state.is('^.add')){
                data.editInfo.status = 1;
            }
            return data;
        }
        interfaceMenu.cateGet($stateParams).success(function(obj) {
            $scope.page.data = _default(obj.data);
        });
        $scope.fn_edit = function() {
            interfaceMenu.cateEdit({
                'id' : $stateParams.id,
                'name' : $scope.page.data.editInfo.name,
                'show_type' : $scope.page.data.editInfo.show_type
            }).success(function(obj) {
                if(obj.code){
                    $uiAlert({
                        'msg' : obj.data.msg
                    });
                    $state.go('food.category',{},{
                        'reload' : true
                    });
                }else{
                    $uiAlert({
                        'msg' : obj.message
                    });
                }
            })
        }
    })
    //菜单添加/修改
    .controller('foodEditControler',function($scope,interfaceMenu,$stateParams,resolve_item,$uiAlert,_,$log,$state) {
        //预处理数据
        function preData(data){
            var editInfo = data.editInfo;
            if(!editInfo.status){
                editInfo.status = 1;
            }
            if(!editInfo.attrs){
                editInfo.attrs = {
                    'customAttr' : [],
                    'sizeAttr' : {
                        'attrName' : '尺寸',
                        'attrValues' : []
                    }
                };
            }
            if(!editInfo.cplb){
                editInfo.cplb = [];
            };
            return data;
        }
        $scope.page = {};
        $scope.page.data = preData(resolve_item);

        //是否是添加操作
        function is_add_opt(){
            return $state.is('^.add');
        }
        function reset_data(){
            $scope.page.data.editInfo = {};
            preData($scope.page.data);
            //$scope.$digest();
        }
        
        //提取cplb的数据
        function simple_cplb(){
            var cplb = $scope.page.data.cplb;
            var data = _.map(cplb.list,function(v,k) {
                return {
                    'inputName' : v.inputName,
                    'value' : v.value == true ? 1 : 0
                }
            });
            return data;
        }


        $scope.fn_edit = function() {
            var copy = _.extend({},$scope.page.data.editInfo);
            copy.attrs = angular.toJson(copy.attrs);
            copy.cplb = angular.toJson(simple_cplb());

            interfaceMenu.edit(copy).success(function(obj) {
                if(obj.code){
                    $uiAlert({
                        'msg' : obj.data.msg
                    });
                    if(is_add_opt()){
                        reset_data();
                    }else{
                        $state.go('food',$state.params,{
                            'reload' : true
                        });
                    }
                }else{
                    $uiAlert({
                        'msg' : obj.message
                    });
                }
            });
        }
    })
    //规格
    .controller('foodAddAttrController',function($scope) {
        var attrs = $scope.data.attrValues;

        $scope.fn_add_attr = function() {
            attrs.push({
                'name' : '',
                'price' : ''
            })
        }
        $scope.fn_delete_attr = function(index) {
            attrs.splice(index,1);
        }
    })
    //自定义规格
    .controller('foodAddSelfattrController',function($scope) {
        var attrs = $scope.data;
        $scope.fn_add_attr = function() {
            attrs.push({
                'attrName' : '',
                'attrValues' : [{'name':''}]
            });
        };
        $scope.fn_delete_attr = function(index) {
            attrs.splice(index,1);
        }
        $scope.fn_delete_value = function(pid,index){
            attrs[pid]['attrValues'].splice(index,1);
        }
        $scope.fn_add_value = function(index) {
            attrs[index]['attrValues'].push({
                'name' : ''
            });
        }
    })
    //菜单单位编辑
    .controller('foodUnitController',function($scope,resolve_list,interfaceMenu,$uiAlert,_) {
        $scope.page = {};
        $scope.page.list = resolve_list;
        function find_index(id){
            return _.findIndex($scope.page.list,{
                'id' : id
            });
        }
        function add_item(id){
            $scope.page.list.push({
                'id' : id
            });
        }
        function remove_item(id){
            var index = find_index(id);
            $scope.page.list.splice(index,1);
        }
        //添加单位
        $scope.fn_add_item = function() {
            interfaceMenu.unitEdit({}).success(function(obj) {
                if(obj.code){
                    add_item(obj.data.id);
                }else{
                    $uiAlert(obj.message);
                }
            });
        }
        //修改单位
        $scope.fn_edit_item = function(id,name) {
            interfaceMenu.unitEdit({
                id : id,
                name : name
            }).success(function(obj) {
                if(obj.code){

                }else{
                    $uiAlert(obj.message);
                }
            })
        }
        //删除单位
        $scope.fn_del_item = function(id) {
            interfaceMenu.unitDel({
                'id' : id
            }).success(function(obj) {
                if(obj.code){
                    remove_item(id);
                }else{
                    $uiAlert(obj.message);
                }
            })
        }
    })
    //打印清单
    .controller('printController',function($scope,resolve_data,interfacePrint,$uiPrint,$log,$stateParams,uiAlert) {
        $log.log(resolve_data);
        $scope.page = {
            'list' : resolve_data.logs,
            'totalPage' : resolve_data.totalPage,
            'currentPage' : $stateParams.page || 1
        };
        $scope.fn_reprint = function(id) {
            interfacePrint.reprint({
                'id' : id
            }).then(function(obj) {
                return obj.data.data;
            }).then(function(obj) {
                var flag = $uiPrint.print_content_index(obj.htmlContent,obj.printId,obj.kitchenPrinter);
                if(flag){
                    uiAlert('打印成功');
                }else{
                    uiAlert('打印失败');
                }
            })
        };
    });
});
