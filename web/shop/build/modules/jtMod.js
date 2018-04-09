define(['jtMod_controller', 'jtMod_factory', 'jtMod_directive', 'ui-router'], function () {

    angular.module('jtMod', ['ui.router','jtMod_controller','jtMod_factory','jtMod_directive']).run(run);

    run.$inject = ['$rootScope', '$state', 'AuthService', '$stateParams'];
    function run($rootScope, $state, AuthService, $stateParams) {
       $rootScope.$on('$stateChangeStart', function (event, toState, toParams, fromState, fromParams) {
           var globals = $rootScope.globals || {};
           if(!globals.user){
               AuthService.isLogin().then(function (res) {
                   if(res.isLogin){
                       AuthService.setUser(res);
                       if (toState.name == 'login') {
                            $state.go('index');
                       }
                   }else{
                       if(toState.name != 'login' && toState.name != 'logout'){
                            $state.go('login');
                       }
                   }
               })
           }
       });
       $rootScope.stateParams = $stateParams;
    }
});
