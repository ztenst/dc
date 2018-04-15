define(['cjmMod_controller','cjmMod_factory','cjmMod_directive','cjmMod_filter'],function() {
    angular.module('cjmMod',['ui.router','cjmMod_controller','cjmMod_directive','cjmMod_filter','cjmMod_factory'])
    .config(function($stateProvider,$urlRouterProvider,$httpProvider) {
        $urlRouterProvider.deferIntercept();
        //默认开启缓存
        $httpProvider.defaults.cache = true;
        $httpProvider.interceptors.push('myInterceptor');

        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        /**
        * The workhorse; converts an object to x-www-form-urlencoded serialization.
        * @param {Object} obj
        * @return {String}
        */
        var param = function(obj) {
            var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

            for(name in obj) {
              value = obj[name];

              if(value instanceof Array) {
                for(i=0; i<value.length; ++i) {
                  subValue = value[i];
                  fullSubName = name + '[' + i + ']';
                  innerObj = {};
                  innerObj[fullSubName] = subValue;
                  query += param(innerObj) + '&';
                }
              }
              else if(value instanceof Object) {
                for(subName in value) {
                  subValue = value[subName];
                  fullSubName = name + '[' + subName + ']';
                  innerObj = {};
                  innerObj[fullSubName] = subValue;
                  query += param(innerObj) + '&';
                }
              }
              else if(value !== undefined && value !== null)
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
            }

            return query.length ? query.substr(0, query.length - 1) : query;
        };

        // Override $http service's default transformRequest
        $httpProvider.defaults.transformRequest = [function(data) {
            return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
        }];
        $urlRouterProvider.otherwise('/index');
        //登录页面
        $stateProvider.state('login',{
            'url' : '/login',
            'views' : {
                'main' : {
                    'templateUrl' : 'tpl/login.html',
                    'controller' : 'LoginController',
                    'controllerAs' : 'vm'
                }

            }
        })
        .state('logout',{
            'url' : '/logout',
            'views' : {
                'main' : {
                    'controller' : 'LogoutController'
                }
            }
        })
        //首页
        // .state('index',{
        //     'url' : '/index?status&page',
        //     'views' : {
        //         'main' : {
        //             'templateUrl' : 'tpl/frame.html'
        //         },
        //         'submain@index' : {
        //             'templateUrl' : 'tpl/index.html',
        //             'controller' : 'indexMainController'
        //         },
        //         'sidebar' : {
        //             'templateUrl' : 'tpl/sidebar.html'
        //         }
        //     },
        //     'resolve' : {
        //         'resolve_desks' : function(interfaceIndex,$stateParams) {
        //             return interfaceIndex.info($stateParams).then(function(obj) {
        //                 return obj.data.data;
        //             });
        //         }
        //     }
        // })
        //餐桌详情
        // .state('index.detail',{
        //     'url' : '/detail/:id',
        //     'views' : {
        //         'submain' : {
        //             'templateUrl' : 'tpl/index-detail.html',
        //             'controller' : 'indexDetailMainController'
        //         }
        //     }
        // })
        //账单页面
        .state('bill',{
            'url' : '/bill?d&begin&end&page',
            'views' : {
                'main' : {
                    'templateUrl' : 'tpl/frame.html'
                },
                'submain@bill' : {
                    'templateUrl' : 'tpl/bill.html',
                    'controller' : 'OrderController',
                    'controllerAs' : 'vm'
                },
                'sidebar' : {
                    'templateUrl' : 'tpl/sidebar.html'
                }
            },
            resolve : {
                data : function(OrderService, $stateParams){
                    return OrderService.list($stateParams);
                }
            }
        })
        //会员页面
        .state('member',{
            'url' : '/member?type&str&page',
            'views' : {
                'main' : {
                    'templateUrl' : 'tpl/frame.html'
                },
                'submain@member' : {
                    'templateUrl' : 'tpl/member.html',
                    'controller' : 'UserController',
                    'controllerAs' : 'vm'
                },
                'sidebar' : {
                    'templateUrl' : 'tpl/sidebar.html'
                }
            },
            resolve : {
                data : function(UserService, $stateParams){
                    return UserService.list($stateParams);
                }
            }
        })
        .state('member.detail',{
            'url' : '/:id',
            'views' : {
                'detail' : {
                    'templateUrl' : 'tpl/member-detail.html',
                    'controller' : 'UserDetaiController',
                    'controllerAs' : 'vm'
                }
            },
            resolve : {
                data : function($stateParams,UserService){
                    return UserService.detail($stateParams.id);
                }
            }
        })
        //菜单页面
        .state('food',{
            'url' : '/food?cat&s&kw&page',
            'views' : {
                'main' : {
                    'templateUrl' : 'tpl/frame.html'
                },
                'submain@food' :  {
                    'templateUrl' : 'tpl/food.html',
                    'controller' : 'foodMainController'
                },
                'sidebar' : {
                    'templateUrl' : 'tpl/sidebar.html'
                }
            },
            'resolve' : {
                'resolve_list' : function($q,interfaceMenu,$stateParams) {
                    var cat = $stateParams.cat;
                    var s = $stateParams.s;
                    var kw = $stateParams.kw;
                    var p = $stateParams.page || 1;
                    var params = {
                        'cate_id' : cat,
                        'status' : s,
                        'name' : kw,
                        'page' : p
                    };

                    return interfaceMenu.list(params).then(function(obj) {
                        return obj.data.data;
                    })
                },
                'resolve_category' : function(interfaceMenu) {
                    return interfaceMenu.select().then(function(obj) {
                        return obj.data.data;
                    })
                }
            }
        })
        //菜单添加
        .state('food.add',{
            'url' : '/add',
            'views' : {
                'submain' : {
                    'templateUrl' : 'tpl/food-add.html',
                    'controller' : 'foodEditControler'
                }
            },
            'resolve' : {
                'resolve_item' : function(interfaceMenu,$stateParams) {
                    return interfaceMenu.getEdit($stateParams).then(function(obj) {
                        return obj.data.data;
                    });
                }
            }
        })
        //菜单修改
        .state('food.edit',{
            'url' : '/edit/:id',
            'views' : {
                'submain' : {
                    'templateUrl' : 'tpl/food-add.html',
                    'controller' : 'foodEditControler'
                }
            },
            'resolve' : {
                'resolve_item' : function(interfaceMenu,$stateParams) {
                    return interfaceMenu.getEdit($stateParams).then(function(obj) {
                        return obj.data.data;
                    });
                }
            }
        })
        //菜单单位
        .state('food.unit',{
            'url': '/unit',
            'views' : {
                'submain' : {
                    'templateUrl' : 'tpl/food-unit.html',
                    'controller' : 'foodUnitController'
                }
            },
            resolve : {
                resolve_list : function(interfaceMenu) {
                    return interfaceMenu.unitList().then(function(obj) {
                        return obj.data.data;
                    });
                }
            }
        })
        //菜单分类
        .state('food.category',{
            'url' : '/category',
            'views' : {
                'submain' : {
                    'templateUrl' : 'tpl/food-category.html',
                    'controller' : 'foodCategoryController'
                }
            },
            'resolve' : {
                'resolve_category' : function(interfaceMenu) {
                    return interfaceMenu.cateList().then(function(obj) {
                        return obj.data.data;
                    });
                }
            }
        })
        .state('food.category.add',{
            'url' : '/add',
            'views' : {
                'submain@food' : {
                    'templateUrl' : 'tpl/food-category-add.html',
                    'controller' : 'foodCategoryEditController'
                }
            }
        })
        .state('food.category.edit',{
            'url' : '/edit/:id',
            'views' : {
                'submain@food' : {
                    'templateUrl' : 'tpl/food-category-add.html',
                    'controller' : 'foodCategoryEditController'
                }
            }
        })
        //统计
        .state('statistics',{
            'url' : '/statistics?type&day',
            'views' : {
                'main' : {
                    'templateUrl' : 'tpl/frame.html'
                },
                'submain@statistics' : {
                    'templateUrl' : 'tpl/statistics.html',
                    'controller' : 'StatisticsController',
                    'controllerAs' : 'vm'
                },
                'sidebar' : {
                    'templateUrl' : 'tpl/sidebar.html'
                }
            },
            'resolve' : {
                'type' : function ($stateParams) {
                    return $stateParams.type ? $stateParams.type : 'sales';
                },
                'data' : function (ChartService,$stateParams,type) {
                    var params = {
                        'day' : $stateParams.day ? $stateParams.day : 'benzhou'
                    };
                    if(type == 'sales'){
                        return ChartService.salePrice(params);
                    }else if(type == 'menu'){
                        return ChartService.saleMenu(params);
                    }else if(type == 'desk'){
                        return ChartService.useDesk(params);
                    }else if(type == 'rate'){
                        return ChartService.useDeskRate(params);
                    }
                }
            }
        })
        //设置
        .state('setting',{
            'url' : '/setting',
            'views' : {
                'main' : {
                    'templateUrl' : 'tpl/frame.html'
                },
                'submain@setting' : {
                    'templateUrl' : 'tpl/setting.html',
                    'controller' : 'SettingController',
                    'controllerAs' : 'vm'
                },
                'sidebar' : {
                    'templateUrl' : 'tpl/sidebar.html'
                }
            },
            'resolve' : {
                setting : function (SettingService) {
                    return SettingService.allInfo();
                }
            }
        })
        //管理员
        .state('admin',{
            'url' : '/admin',
            'views' : {
                'main' : {
                    'templateUrl' : 'tpl/frame.html'
                },
                'submain@admin' : {
                    'templateUrl' : 'tpl/admin.html',
                    'controller' : 'AdminController',
                    'controllerAs' : 'vm'
                },
                'sidebar' : {
                    'templateUrl' : 'tpl/sidebar.html'
                }
            },
            'resolve' : {
                list : function (AdminService) {
                    return AdminService.list();
                }
            }
        })
        .state('admin.add',{
            'url' : '/add/:id',
            'views' : {
                'adminadd' : {
                    'templateUrl' : 'tpl/admin-add.html',
                    'controller' : 'AdminAddController',
                    'controllerAs' : 'vm'
                }
            }
        })
        //修改密码
        .state('admin.pwd',{
            'url' : '/pwd',
            'views' : {
                'submain@admin' : {
                    'templateUrl' : 'tpl/admin-pwd.html',
                    'controller' : 'PwdController',
                    'controllerAs' : 'vm'
                }
            }
        })
        //打印清单
        .state('print',{
            'url' : '/print?page',
            'views' : {
                'main' : {
                    'templateUrl' : 'tpl/frame.html'
                },
                'submain@print' : {
                    'templateUrl' : 'tpl/print.html',
                    'controller' : 'printController'
                },
                'sidebar' : {
                    'templateUrl' : 'tpl/sidebar.html'
                }
            },
            'resolve' : {
                resolve_data : function (interfacePrint,$stateParams) {
                    var page = $stateParams.page;
                    return interfacePrint.list({
                        'page' : page
                    }).then(function(obj) {
                        return obj.data.data;
                    })
                }
            }
        })
    })
    .constant('CONFIG',{
        'jsPath' : './js/'
    })
    .run(function($rootScope,interfaceGlobal,$urlRouter,AuthService,uiTitle) {
        $rootScope.config = {};
        function scrollTop(){
            var $body = $('body');
            $body.scrollTop(0);
        }

        $rootScope.$on('$stateChangeSuccess',function() {
            //$rootScope.config.title = '首页';
            uiTitle('简单点');
            scrollTop();
        });

        function check_login(name){
            if(!name.match('/^login/')) return true;
        }


        //进入页面前就获取到globals.user
        $rootScope.$on('$locationChangeSuccess', function(e) {
            var globals = $rootScope.globals || {};
            // UserService is an example service for managing user state
            if (globals.user) return;
            // Prevent $urlRouter's default handler from firing
            e.preventDefault();

            interfaceGlobal.user().then(function (res) {
                if(res.isLogin){
                    AuthService.setUser(res);
                }else{

                }
                $urlRouter.sync();
            })
        });
        $urlRouter.listen();
    })
});
