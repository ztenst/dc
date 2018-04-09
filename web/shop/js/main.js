require.config({
    'baseUrl' : './js/',
    'paths' : {
        'jquery' : 'jquery-1.9.1.min',
        'jquery-ui' : 'jquery-ui-1.12.1.custom/jquery-ui',
        'jquery-powerFloat' : 'jquery-powerFloat/js/jquery-powerFloat',
        'jquery-powerFloat-css' : 'jquery-powerFloat/css',
        'nicescroll' : 'jquery.nicescroll.min',
        'respond' : 'respond/respond.min',
        'underscore' : 'underscore',
        'angular' : 'angular-1.2.32',
        'ui-router' : 'angular-ui-router',
        'ui-router-extra' : 'ct-ui-router-extras-ionic',
        'cjmMod' : 'modules/cjmMod',
        'cjmMod_controller' : 'modules/controller/cjmMod_controller',
        'cjmMod_directive' : 'modules/directive/cjmMod_directive',
        'cjmMod_factory' : 'modules/factory/cjmMod_factory',
        'cjmMod_filter' : 'modules/filter/cjmMod_filter',
        'my97' : 'My97DatePicker/my_WdatePicker',
        'plupload' : 'plupload/js/plupload.full.min',
        'qiniu' : 'qiniu-sdk/dist/qiniu.min'
    },
    'shim' : {
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
        }
    },
    'map' : {
        '*': {
            'css': 'require-css/css.min' // or whatever the path to require-css is
        }
    }
});

require(['jquery-ui','jquery-powerFloat','nicescroll','underscore','my97'],function() {
    var $GL_EVENT = $(document);
    $('.j-ui-selectmenu').each(function() {
        var self = $(this);
        var w = self.data('width') || 120;
        self.selectmenu({
            width : w
        });
    });
    $('.u-radio-group').controlgroup({
        'classes' : {
            'ui-controlgroup' : 'my-ui-controlgroup'
        }
    });
    $('.u-checkbox-group').controlgroup({
        'classes' : {
            'ui-controlgroup' : 'my-ui-controlgroup-checkbox'
        }
    });
    $('.u-radio-group input').checkboxradio();

    $('.opt-del').powerFloat({
        'target' : $('#confirm-dialog-tpl').html(),
        'targetMode' : 'remind',
        'eventType' : 'click',
        'position' : '6-8'
    });

    $('body').on('click','.dialog-close',function() {
        $.powerFloat.hide();
    });

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
    $('.j-nicescroll').niceScroll({
        autohidemode: false,
        cursorcolor : '#e8e8e8'
    });


    var resizeFn = _.throttle(function() {
        $GL_EVENT.trigger('resize')
    },100);
    $(window).resize(function() {
        resizeFn();
    });

    $('#j-calendar-time-icon').click(function() {
        WdatePicker({el:'j-calendar-time','onpicked':function() {
            
        }});
    });


    //启动主程序
    //angular.module('myApp',['cjmMod']);
    //angular.bootstrap(document,['myApp']);
})
