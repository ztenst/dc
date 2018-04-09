var basedir = 'js/';
module.exports = function (grunt) {
  // 项目配置
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    //uglify: {
        //'options' : {
            //'compress' : false,
            //'mangle' : false
        //},
        ////'my_js' : {
            ////'files' : {
                ////'js/dest/libs.min.js' : [
                    ////basedir + 'ionic-v1.3.1/js/ionic.bundle.min.js',
                    ////basedir + 'underscore-min.js'
                ////],
                ////'js/dest/component.min.js' : [
                    ////basedir + 'modules/factory/loading-bar.min.js',
                    ////basedir + 'modules/factory/ionic-image-lazy-load.js',
                    ////basedir + 'modules/factory/myMod_factory.js',
                    ////basedir + 'modules/directive/myMod_directive.js',
                    ////basedir + 'modules/filter/myMod_filter.js',
                    ////basedir + 'modules/controller/myMod_controller.js',
                    ////basedir + 'modules/factory/otherMod_factory.js',
                    ////basedir + 'modules/directive/otherMod_directive.js',
                    ////basedir + 'modules/filter/otherMod_filter.js',
                    ////basedir + 'modules/controller/otherMod_controller.js',
                    ////basedir + 'modules/factory/secondMod_factory.js',
                    ////basedir + 'modules/directive/secondMod_directive.js',
                    ////basedir + 'modules/filter/secondMod_filter.js',
                    ////basedir + 'modules/controller/secondMod_controller.js',
                ////]
            ////}
        ////},
        ////'my_css' : {
            ////files : {
                ////'style/lib.min.css' : [
                    //////basedir + 'modules/factory/loading-bar.min.css'
                ////]
            ////}
        ////}
    //},
    cssmin : {
        'my_css':{
            'files' : {
                //'../esfwap-2017/style/all.min.css' : [
                    //'../wap-style/js/ionic-v1.3.1/css/ionic.css',
                    //'../esfwap-2017/style/common.css',
                    //'../esfwap-2017/style/flex.css',
                    //'../esfwap-2017/style/layout.css'
                //]
            }
        }
    },
    ngtemplates : {
        'myAppTpl' : {
            'src' : ['tpl/*.html'],
            'dest' : 'js/dest/templates.min.js',
            'options' : {
                standalone : true,
                htmlmin: {
                    collapseBooleanAttributes: true,
                    collapseWhitespace: true,
                    removeAttibuteQuotes: true,
                    removeEmptyAttributes: true,
                    //removeRedundantAttributes: true,
                    //removeScriptTypeAttributes: true,
                    //removeStyleLinkTypeAttributes: true
                }
            }
        }
    }
  });
  // 加载提供"uglify"任务的插件
  //grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-angular-templates');
  // 默认任务
  //grunt.registerTask('default', ['uglify','cssmin','ngtemplates']);
  grunt.registerTask('default', ['ngtemplates','cssmin']);
}

