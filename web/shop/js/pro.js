require.config({
    'baseUrl' : './js/',
    'urlArgs': "version=" + (+new Date),
    'paths' : {
        'jquery' : 'jquery-1.9.1.min',
        'jquery-ui' : 'jquery-ui-1.12.1.custom/jquery-ui',
        'jquery-powerFloat' : 'jquery-powerFloat/js/jquery-powerFloat',
        'jquery-powerFloat-css' : 'jquery-powerFloat/css',
        'nicescroll' : 'jquery.nicescroll',
        'underscore' : 'underscore',
        'angular' : 'angular-1.2.32',
        'ui-router' : 'angular-ui-router',
        'ui-router-extra' : 'ct-ui-router-extras-ionic',
        'cjmMod' : 'modules/cjmMod',
        'cjmMod_controller' : 'modules/controller/cjmMod_controller',
        'cjmMod_directive' : 'modules/directive/cjmMod_directive',
        'cjmMod_factory' : 'modules/factory/cjmMod_factory',
        'cjmMod_filter' : 'modules/filter/cjmMod_filter',
        'jtMod' : 'modules/jtMod',
        'jtMod_controller' : 'modules/controller/jtMod_controller',
        'jtMod_directive' : 'modules/directive/jtMod_directive',
        'jtMod_factory' : 'modules/factory/jtMod_factory',
        'my97' : 'My97DatePicker/my_WdatePicker',
        'validform' : 'validform.min',
        'plupload' : 'plupload/js/plupload.full.min',
        'qiniu' : 'qiniu-sdk/dist/qiniu.min',
        'websocket' : 'web-socket-js-master/web_socket',
        'swfobject' : 'web-socket-js-master/swfobject',
        'placeholder' : 'jquery.placeholder.min',
        'angular_template' : 'dest/templates.min',
        'echarts' : 'echarts.common.min'
    },
    'shim' : {
        'angular_template' : {
            'deps' : ['angular']
        },
        'placeholder' : {
            'deps' : ['jquery']
        },
        'jquery-ui' : {
            'deps' : ['jquery']
        },
        'jquery-powerFloat' : {
            'deps' : ['jquery','css!jquery-powerFloat-css/powerFloat.css']
        },
        'jscrollpane' : {
            'deps' : ['jquery','jquery.mousewheel','css!jquery.jscrollpane.css']
        },
        'jquery.mousewheel' : {
            'deps' : ['jquery']
        },
        'ui-router' : {
            'deps' : ['angular']
        },
        'ui-router-extra' : {
            'deps' : ['ui-router']
        },
        'angular' : {
            'deps' : ['jquery']
        },
        'plupload' : {
            'deps' : ['qiniu']
        },
        'highcharts_zh' : {
            'deps' : ['highcharts']
        },
        'highcharts' : {
            'exports' : 'Highcharts',
            'deps' : ['jquery']
        },
        'websocket' : {
            'deps' : ['swfobject']
        }
    },
    'map' : {
        '*': {
            'css': 'require-css/css.min' // or whatever the path to require-css is
        }
    }
});

require.onError = function (err) {
    console.log(err.requireType);
};
require(['jquery-ui','jquery-powerFloat','underscore','cjmMod','ui-router','jtMod','angular_template'],function() {
    //var $GL_EVENT = $(document);
    //$('.j-ui-selectmenu').each(function() {
        //var self = $(this);
        //var w = self.data('width') || 120;
        //self.selectmenu({
            //width : w
        //});
    //});
    //$('.u-radio-group').controlgroup({
        //'classes' : {
            //'ui-controlgroup' : 'my-ui-controlgroup'
        //}
    //});
    //$('.u-radio-group input').checkboxradio();

    //$('.opt-del').powerFloat({
        //'target' : $('#confirm-dialog-tpl').html(),
        //'targetMode' : 'remind',
        //'eventType' : 'click',
        //'position' : '6-8'
    //});


    //$('.j-nicescroll').each(function() {
        //var self = $(this);
        //self.jScrollPane({
            //'stickToRight' : true,
            //verticalGutter : 0
        //});
        //var api = self.data('jsp');

        //$GL_EVENT.on('resize',function() {
            //api.reinitialise();
        //});
    //});

    //$('.j-nicescroll').niceScroll({
        //autohidemode: false,
        //cursorcolor : '#e8e8e8'
    //});


    //var resizeFn = _.throttle(function() {
        //$GL_EVENT.trigger('resize')
    //},100);
    //$(window).resize(function() {
        //resizeFn();
    //});


    //启动主程序
    angular.module('myApp',['cjmMod','jtMod','myAppTpl']);
    angular.bootstrap(document,['myApp']);
})
