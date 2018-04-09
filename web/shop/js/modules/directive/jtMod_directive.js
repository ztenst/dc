define(['angular'], function () {
    angular
        .module('jtMod_directive', [])
        .directive('uiValidform', uiValidform)
        .directive('memberSearch', memberSearch)
        .directive('uiPagination', uiPagination)
        .directive('memberEdit', memberEdit)
        .directive('billSearch', billSearch)
        .directive('lodopPrinter', lodopPrinter)
        .directive('uiLineEcharts',uiLineEcharts)
        .directive('uiPieEcharts',uiPieEcharts);

    uiPagination.$inject = ['$state'];
    function uiPagination($state) {
        return {
            restrict: "EA",
            templateUrl: "tpl/ui-pagination.html",
            scope: {
                totalPage: "=",
                currentPage: "="
            },
            link: function (scope, element, attr) {

                //总页数大于1 显示下一页 但当前页在最后一页 隐藏下一页
                //总页数大于1并且当前页要大于1 显示上一页
                scope.currentPage = parseInt(scope.currentPage || 1);
                if (scope.totalPage > 1) {
                    scope.isShowNext = scope.currentPage < scope.totalPage ? true : false;
                    scope.isShowPre = scope.currentPage > 1 ? true : false;
                } else {
                    scope.isShowNext = false;
                    scope.isShowPre = false;
                }
                scope.nextUrl = $state.href($state.current.name, {page: scope.currentPage + 1});
                scope.preUrl = $state.href($state.current.name, {page: scope.currentPage - 1});
            }
        }
    }

    memberSearch.$inject = ['$state', '$stateParams'];
    function memberSearch($state, $stateParams) {
        return {
            restrict: "EA",
            templateUrl: "tpl/member-search.html",
            link: function (scope, element, attr) {
                var options = {
                    id: '用户ID',
                    username: '姓名',
                    phone: '手机号'
                }
                scope.keyword = $stateParams.str;
                scope.options = options;
                scope.filter = scope.options[$stateParams.type] || scope.options['id'];
                element.find('.ui-selectmenu-text').text(scope.filter);
                scope.search = function () {
                    $state.go('member', {
                        type: element.find('.j-ui-selectmenu').val(),
                        str: scope.keyword
                    }, {reload: true});
                }
            }
        }
    }

    function uiValidform() {
        return {
            restrict: "EA",
            link: function (scope, element, attr) {
                require(['validform'], function () {
                    element.Validform({
                        tipSweep: true,
                        postonce: true,
                        tiptype: function (msg, o, cssctl) {
                            var itemObj = o.obj;
                            if (o.type === 3) {
                                itemObj.siblings('.u-errormsg').removeClass('f-dn').text(msg);
                                itemObj.addClass('u-ipt-err');
                            } else {
                                itemObj.siblings('.u-errormsg').addClass('f-dn');
                                itemObj.removeClass('u-ipt-err');
                            }
                        },
                        datatype: {
                            "*5-20": /^[^\s]{5,20}$/,
                        },
                        beforeSubmit: function () {
                            scope.$emit('validformsuccess', attr.name);
                            return false;
                        }
                    })
                })
            }
        }
    }

    memberEdit.$inject = ['UserService', '$timeout', 'uiComponent', '$state', '$stateParams'];
    function memberEdit(UserService, $timeout, uiComponent, $state, $stateParams) {
        return {
            restrict: "EA",
            templateUrl: "tpl/member-edit.html",
            link: function (scope, element, attr) {

                require(['my97'], function () {
                    scope.closeDialog = function () {
                        element.addClass('f-dn');
                    }
                    scope.$on('member.edit', function (event, args) {
                        return UserService.info(args.id).then(function (res) {
                            element.removeClass('f-dn').css('top', args.pageY).css('right', args.pageX);
                            scope.info = res.editInfo;
                            $timeout(function () {
                                uiComponent.radio(element.find('.u-radio-group'));
                            })
                        })
                    })

                    scope.submitInfo = function () {
                        scope.info.birthday = element.find("#birthday").val();
                        UserService.edit(scope.info).then(function (res) {
                            scope.info = res.editInfo;
                            $state.go($state.current, {}, {reload: true});
                        })
                    }
                })

            }
        }
    }

    function uiLineEcharts() {
        return {
            restrict: "EA",
            scope : {
                showData : "=",
                seriesName : "@",
                seriesUnit : "@"
            },
            link: function (scope, element, attr) {
                console.log(scope.seriesUnit);
                require(['echarts'], function (echarts) {
                    var lineChart = echarts.init(element[0]);
                    // 指定图表的配置项和数据
                    var option = {
                        title: {
                            show : false,
                            // text: '销售额统计'
                        },
                        tooltip: {
                            trigger: 'axis',
                            formatter: "{a}：{c}"+scope.seriesUnit // 这里是鼠标移上去的显示数据
                        },
                        legend: {
                            // data:['销售额']
                        },
                        grid: {
                            left:'3%',
                            right: '5%',
                            bottom: '12%',
                            top : '7%',
                            containLabel: true
                        },
                        toolbox: {
                        },
                        dataZoom: [{
                            type: 'inside',
                            start: 0,
                            end: 100
                        }, {
                            start: 0,
                            end: 10,
                            handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                            handleSize: '80%',
                            handleStyle: {
                                color: '#fff',
                                shadowBlur: 3,
                                shadowColor: 'rgba(0, 0, 0, 0.6)',
                                shadowOffsetX: 2,
                                shadowOffsetY: 2
                            }
                        }],
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: scope.showData.x ,
                        },
                        yAxis: {
                            type: 'value',
                            axisLabel: {
                                formatter: '{value}' + scope.seriesUnit
                            },
                        },
                        series: [
                            {
                                name:scope.seriesName,
                                type:'line',
                                stack: '总量',
                                data: scope.showData.y,
                                label : {
                                    normal : {
                                        show : true,
                                        formatter : '{c}' + scope.seriesUnit
                                    }
                                }
                            }
                        ]
                    };

                    // 使用刚指定的配置项和数据显示图表。
                    lineChart.setOption(option);
                })
            }
        }
    }
    
    function uiPieEcharts() {
        return {
            restrict: "EA",
            scope : {
                showData : "=",
                seriesName : "@"
            },
            link: function (scope, element, attr) {
                require(['echarts'], function (echarts) {
                    var pieChart = echarts.init(element[0]);
                    var option = {
                        title : {
                            // text: '某站点用户访问来源',
                            // subtext: '纯属虚构',
                            x:'center'
                        },
                        tooltip : {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c} ({d}%)"
                        },
                        legend: {
                            orient: 'vertical',
                            left: 'left',
                            data: scope.showData.legend
                        },
                        series : [
                            {
                                name: scope.seriesName,
                                type: 'pie',
                                radius : '55%',
                                center: ['50%', '60%'],
                                data:scope.showData.data,
                                itemStyle: {
                                    emphasis: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                                    }
                                }
                            }
                        ]
                    };
                    pieChart.setOption(option);
                });
            }
        }
    }

    billSearch.$inject = ['$state'];
    function billSearch($state) {
        return {
            restrict: "EA",
            templateUrl: "tpl/bill-search.html",
            link: function (scope, element, attr) {
                require(['my97'], function () {
                    var date = new Date();
                    var maxDate = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();

                    element.find("#begin").bind('focus', function () {
                        WdatePicker({maxDate: "#F{$dp.$D('end')||'" + maxDate + "'}"});
                    })
                    element.find("#end").bind('focus', function () {
                        WdatePicker({minDate: "#F{$dp.$D('begin')}", maxDate: maxDate})
                    })

                    scope.search = function () {
                        $state.go('bill', {
                            'begin': $("#begin").val(),
                            'end': $("#end").val()
                        });
                    }
                });
            }
        }
    }

    lodopPrinter.$inject = ['LodopService', '$timeout', 'uiComponent'];
    function lodopPrinter(LodopService, $timeout, uiComponent) {
        return {
            restrict: "EA",
            scope: {
                printServer: "=",
                kitchenPrinter:"="
            },
            link: function (scope, element, attr) {
                $timeout(function () {
                    uiComponent.select(element);
                });
                    element.on("selectmenuopen", function (event, ui) {
                        if (scope.printServer) {
                            LodopService.getLodop(scope.printServer).then(function (LODOP) {
                                LODOP.Create_Printer_List(element[0]);
                                element.val(parseInt(scope.kitchenPrinter));
                                element.selectmenu("refresh");
                            });
                        }
                    })
                    element.on("selectmenuselect", function () {
                        scope.$emit('selectmenuselect', element.val());
                    })

            }
        }
    }

});
