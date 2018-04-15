define(['angular'], function () {
    angular
        .module('jtMod_controller', [])
        .controller('LoginController', LoginController)
        .controller('OrderController', OrderController)
        .controller('LogoutController', LogoutController)
        .controller('UserController', UserController)
        .controller('UserDetaiController', UserDetaiController)
        .controller('SettingController', SettingController)
        .controller('PwdController', PwdController)
        .controller('AdminController', AdminController)
        .controller('AdminAddController', AdminAddController)
        .controller('StatisticsController', StatisticsController);


    LoginController.$inject = ['$scope', 'AuthService', '$state'];
    function LoginController($scope, AuthService, $state) {
        var vm = this;
        $scope.$on('validformsuccess', function () {
            AuthService.login(vm.username, vm.password).then(function () {
                return AuthService.isLogin();
            }).then(function (res) {
                AuthService.setUser(res);
                $state.go('food');
            });
        })
    }

    LogoutController.$inject = ['AuthService', '$state'];
    function LogoutController(AuthService, $state) {
        logout();
        function logout() {
            return AuthService.logout().then(function () {
                AuthService.setUser(null);
                $state.go('login');
            });
        }
    }

    OrderController.$inject = ['$stateParams', 'data', 'OrderService'];
    function OrderController($stateParams, data, OrderService) {
        var vm = this;
        vm.data = data;
        vm.params = $stateParams;
        vm.showDialog = showDialog;
        vm.closeDialog = closeDialog;
        vm.isShow = false;
        vm.detail = {};

        function showDialog(id) {
            vm.isShow = true;
            return OrderService.detail(id).then(function (res) {
                vm.detail = res;
                return vm.detail;
            })
        }

        function closeDialog() {
            vm.isShow = false;
        }
    }

    UserController.$inject = ['data', '$scope', 'UserService'];
    function UserController(data, $scope, UserService) {
        var vm = this;
        vm.data = data;
        vm.edit = edit;

        function edit(id, $event) {
            $scope.$broadcast('member.edit', {
                pageX: $event.pageX - $event.offsetX - 200,
                pageY: $event.pageY - $event.offsetY + 10,
                id: id
            });
        }
    }

    UserDetaiController.$inject = ['$state', 'data'];
    function UserDetaiController($state, data) {
        var vm = this;
        vm.goback = goback;
        vm.detail = data;
        function goback() {
            $state.go('member');
        }
    }

    SettingController.$inject = ['setting', '$scope', 'SettingService', '$state', 'uiAlert'];
    function SettingController(setting, $scope, SettingService, $state, uiAlert) {
        var vm = this;
        vm.shopInfo = setting.shopInfo;
        vm.searchKeywords = setting.settings.searchKeywords;
        vm.printerServer = setting.settings.printerServer;
        vm.kitchenPrinter = setting.settings.kitchenPrinter;
        vm.saveKeywords = saveKeywords;

        $scope.$on('validformsuccess', function (event, data) {
            if (data == 'basic-settings') {
                SettingService.editInfo(vm.shopInfo).then(function (res) {
                    uiAlert(res);
                    $state.go('setting', {}, {reload: true});
                })
            } else if (data == 'printer-settings') {
                SettingService.setPrinter(vm.printerServer, vm.kitchenPrinter).then(function (res) {
                    uiAlert(res);
                    $state.go('setting', {}, {reload: true});
                })
            }
        })

        $scope.$on('selectmenuselect', function (event, data) {
            console.log(data);
            vm.kitchenPrinter = data;
        })

        function saveKeywords() {
            if (vm.searchKeywords == '') {
                uiAlert('关键词不能为空');
                return;
            }
            vm.searchKeywords = vm.searchKeywords.replace(",", "，");
            SettingService.setKeywords(vm.searchKeywords).then(function (res) {
                $state.go('setting', {}, {reload: true});
            })
        }
    }

    PwdController.$inject = ['$scope', 'AuthService', '$state', 'uiAlert'];
    function PwdController($scope, AuthService, $state, uiAlert) {
        var vm = this;
        vm.password = '';
        $scope.$on('validformsuccess', function () {
            AuthService.password(vm.password).then(function (res) {
                uiAlert(res);
                $state.go('admin.pwd', {}, {reload: true});
            })
        });
    }

    AdminController.$inject = ['list', 'AdminService', 'uiAlert', '$state'];
    function AdminController(list, AdminService, uiAlert, $state) {
        var vm = this;
        vm.list = list;
        vm.delAdmin = delAdmin;
        function delAdmin(id) {
            return AdminService.del(id).then(function (res) {
                uiAlert(res);
                $state.go('admin', {}, {reload: true});
            })
        }
    }

    AdminAddController.$inject = ['AdminService', '$stateParams', '$scope', '$state', '$uiAlert'];
    function AdminAddController(AdminService, $stateParams, $scope, $state, $uiAlert) {
        var vm = this;
        vm.user = {
            account: '',
            newPassword: ''
        };
        if ($stateParams.id) {
            AdminService.info($stateParams.id).then(function (res) {
                vm.user.account = res.editInfo.account;
                vm.user.id = res.editInfo.id;
            });
        }
        $scope.$on('validformsuccess', function () {
            AdminService.edit(vm.user).then(function (res) {
                $uiAlert(res);
                $state.go('admin', {}, {reload: true});
            })
        });
    }

    StatisticsController.$inject = ['data','type'];
    function StatisticsController(data,type) {
        var vm = this;
        vm.data = data;
        vm.type = type;
        vm.isEmpty = angular.isArray(vm.data) && !vm.data.length;
    }

});
