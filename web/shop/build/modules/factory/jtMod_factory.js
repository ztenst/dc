define(['angular'],function() {
    angular
        .module('jtMod_factory',[])
        .factory('HttpService',HttpService)
        .factory('AuthService',AuthService)
        .factory('OrderService',OrderService)
        .factory('UserService',UserService)
        .factory('SettingService',SettingService)
        .factory('AdminService',AdminService)
        .factory('LodopService',LodopService)
        .factory('ChartService',ChartService);

    HttpService.$inject = ['$http','$q'];
    function HttpService($http, $q) {
        var service = {
            get: function(url, config) {
                return handleRepData('get', url, null, config);
            },
            post: function(url, data, config) {
                return handleRepData('post', url, data, config);
            },
            put: function(url, data, config) {
                return handleRepData('post', url, data, config);
            }
        };
        return service;

        function handleRepData(method, url, data, config) {
            var promise;
            var defer = $q.defer();
            switch (method) {
                case 'get':
                    promise = $http.get(url, config);
                    break;
                case 'post':
                    promise = $http.post(url, data, config);
                    break;
                case 'put':
                    promise = $http.put(url, data, config);
                    break;
            }

            promise.then(function(rep) {
                if (rep.data.code || rep.data.code === true) {
                    defer.resolve(rep.data);
                } else {
                    var errorMsg = rep.data.message || '出错啦！';
                    // 弹出错误信息，或者重定向到404页面
                    alert(errorMsg);
                }
            }, function() {
                defer.reject('出错啦！');
            })

            return defer.promise;
        }

    }

    AuthService.$inject = ['HttpService','$rootScope'];
    function AuthService(HttpService, $rootScope){
        
        var service = {
            login : login ,
            logout : logout,
            isLogin : isLogin,
            setUser : setUser,
            password : password
        };

        return service;
        
        function setUser(user){
            $rootScope.globals={
                user : user
            }
        }
        
        function login(username, password){
            return HttpService.post('/api/shop/admin/login', {
                'username' : username ,
                'password' : password
            }).then(function(response){
                return response.data;
            });
        }

        function logout() {
            return HttpService.post('/api/shop/admin/logout')
                .then(function (response) {
                     return response.data;
                });
        }

        function isLogin() {
            return HttpService.get('/api/shop/admin/user-info',{
                // cache : true
            }).then(function (response) {
                return response.data;
            })
        }

        function password(password) {
            return HttpService.post('/api/shop/admin/pwd-update',{
                password : password
            }).then(function (response) {
                return response.data;
            })
        }
    }

    OrderService.$inject = ['HttpService'];
    function OrderService(HttpService){
        var service = {
            list : list,
            detail : detail
        }
        return service;
        
        function list(params) {
            return HttpService.get('/api/shop/bill/list',{
                params : params,
                cache : true
            }).then(function (response) {
                return response.data;
            })
        }

        function detail(id){
            return HttpService.get('/api/shop/bill/order-detail',{
                params : {
                    order_id : id
                },
                cache : true
            }).then(function(response){
                return response.data;
            });
        }
    }

    UserService.$inject = ['HttpService'];
    function UserService(HttpService){
        var service = {
            list : list,
            detail : detail,
            info : info,
            edit : edit
        }
        return service;

        function list(params){
            return HttpService.get('/api/shop/user/list',{
                params : params,
                cache : true
            }).then(function (response) {
                return response.data;
            })
        }

        function detail(id){
            return HttpService.get('/api/shop/user/user-detail',{
                params : {
                    id : id
                },
                cache : true
            }).then(function(response){
                return response.data;
            });
        }

        function info(id){
            return HttpService.get('/api/shop/user/user-edit',{
                params : {
                    id : id
                },
                // cache : true
            }).then(function(response){
                return response.data;
            })
        }

        function edit(params){
            return HttpService.post('/api/shop/user/user-edit',params).then(function (response) {
                return response.data;
            })
        }

    }

    SettingService.$inject = ['HttpService'];
    function SettingService(HttpService){
        var service = {
              allInfo : allInfo,
              setKeywords : setKeywords,
              editInfo : editInfo,
              setPrinter : setPrinter
        };
        return service;

        function allInfo(){
            return HttpService.get('/api/shop/setting/all-info').then(function (response) {
                return response.data;
            })
        }

        function setKeywords(keywords){
            return HttpService.post('/api/shop/setting/setting-edit',{
                searchKeywords : keywords
            }).then(function (response) {
                return response.data;
            })
        }

        function setPrinter(printerServer,kitchenPrinter) {
            return HttpService.post('/api/shop/setting/setting-edit',{
                printerServer : printerServer,
                kitchenPrinter : kitchenPrinter
            }).then(function (response) {
                return response.data;
            })
        }

        function editInfo(params){
            return HttpService.post('/api/shop/setting/shop-edit',params).then(function (response) {
                return response.data;
            })
        }
    }

    AdminService.$inject = ['HttpService'];
    function AdminService(HttpService) {
        var service = {
            list : list,
            info : info,
            edit : edit,
            del : del
        };
        return service;

        function list() {
            return HttpService.get('/api/shop/admin/list').then(function (response) {
                return response.data;
            })
        }

        function info(id) {
            return HttpService.get('/api/shop/admin/staff-edit',{
                params : {
                    id : id
                }
            }).then(function (response) {
                return response.data;
            })
        }

        function edit(data){
            return HttpService.post('/api/shop/admin/staff-edit',data).then(function (response) {
                return response.data;
            })
        }

        function del(id) {
            return HttpService.post('/api/shop/admin/staff-delete',{
                id : id
            }).then(function (response) {
                return response.data;
            })
        }
    }

    LodopService.$inject = ['$q'];
    function LodopService($q) {
        var service = {
            getLodop : getLodop
        };

        return service;

        function getLodop(address) {
            //http://192.168.0.13:8000/
            var delay = $q.defer();
            require.config({
                paths: {
                    "CLodopfuncs": "//"+address + "/CLodopfuncs.js?priority=2"
                }
            });

            require(['CLodopfuncs'],function () {
                var LODOP;
                try{
                    LODOP= getCLodop();
                    if (!LODOP && document.readyState!=="complete") {
                        alert("C-Lodop没准备好，请稍后再试！");
                        return;
                    };
                    delay.resolve(LODOP);
                    //return LODOP;
                } catch(err) {alert("getLodop出错:"+err);};
            },function (err) {
                var failedId = err.requireModules && err.requireModules[0];
                console.log(failedId);
                if(failedId == 'CLodopfuncs'){
                    alert('打印机地址错误！');
                    requirejs.undef(failedId);
                }
            });
            return delay.promise;
        }
    }

    ChartService.$inject = ['HttpService'];
    function ChartService(HttpService) {
        var service = {
            salePrice : salePrice ,
            saleMenu : saleMenu,
            useDesk : useDesk,
            useDeskRate : useDeskRate
        };
        return service;

        function useDesk(params) {
            return HttpService.get('/api/shop/statistics/desk-use',{
                params : params,
                cache : true
            }).then(function (response) {
                return response.data;
            })
        }

        function useDeskRate(params){
            return HttpService.get('/api/shop/statistics/desk-use-rate',{
                params : params,
                cache : true
            }).then(function (response) {
                return response.data
            })
        }

        function saleMenu(params) {
            return HttpService.get('/api/shop/statistics/menu-sale',{
                params : params,
                cache : true
            }).then(function (response) {
                return response.data;
            })
        }
        
        function salePrice(params) {
            return HttpService.get('/api/shop/statistics/sale-price',{
                params : params,
                cache : true
            }).then(function (response) {
                return response.data;
            })
        }
    }


});
