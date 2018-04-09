define(['angular'],function() {
    angular.module('cjmMod_filter',[])
    .filter('detailMenu',function(_) {
        return function(input,addNo) {
            if(addNo){
                return _.where(input,{
                    'addNo' : addNo
                })
            }else{
                return input;
            }
        }
    })
    .filter('highlight',function($sce) {
        return function(input,kw) {
            return $sce.trustAsHtml(input.replace(kw,'<span class="high">' + kw + '</span>'));
        }
    })
    .filter('menufilter',function() {
        return function(input,cid) {
            if(cid){
                return _.where(input,{
                    'cid' : cid
                });
            }else{
                return input;
            }
        }
    })
});
