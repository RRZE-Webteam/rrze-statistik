(()=>{var t={281:()=>{jQuery((function(t){var e="";t('[id^="rrze_statistik_widget"] .edit-box.open-box').click((function(){var a=t(this);return e=t(this).parent().parent().parent().parent().attr("id"),t.ajax({url:ajaxurl,type:"POST",data:"action=showform",beforeSend:function(t){a.hide().before('<span class="spinner" style="visibility:visible;display:block;margin:0 0 0 15px"></span>')},success:function(n){a.prev().remove(),t("#"+e).find(".inside").html(n)}}),!1})),t("body").on("submit","#rrze_statistik_settings",(function(){var a=t(this);return t.ajax({url:ajaxurl,type:"POST",data:t(this).serialize()+"&selector=".concat(e),beforeSend:function(t){a.find(".submit").append('<span class="spinner" style="display:inline-block;float:none;visibility:visible;margin:0 0 0 15px"></span>')},success:function(e){t('[id^="rrze_statistik_widget"]').find(".inside").html(e),t('[id^="rrze_statistik_widget"] .edit-box.open-box').show(),window.location.reload()}}),!1}))}))},378:()=>{var t=(new Date).getFullYear();console.log(RRZESTATISTIKTRANSFER.linechartDataset),document.addEventListener("DOMContentLoaded",(function(e){if("undefined"!==RRZESTATISTIKTRANSFER.linechartDataset){dataTypes=["visits","hits","hosts","files","kbytes"],chartTypes=[],dataTypes.forEach((function(t){document.getElementById(t)&&chartTypes.push(t)}));var a=t-2,n=t-1,i=t;chartTypes.forEach((function(t){var e=function(t,e){return t.filter((function(t){return t.year===e.toString()}))},r=e(RRZESTATISTIKTRANSFER.linechartDataset,i),o=e(RRZESTATISTIKTRANSFER.linechartDataset,n),s=function(e){var a=[null,null,null,null,null,null,null,null,null,null,null,null];return e.forEach((function(e){a[parseInt(e.month)-1]=parseInt(e[t])})),a},l=s(e(RRZESTATISTIKTRANSFER.linechartDataset,a)),c=s(o),d=s(r),u=Highcharts.getOptions().colors,T=Highcharts.chart(t,{chart:{type:RRZESTATISTIKTRANSFER.displayType,renderTo:t},legend:{symbolWidth:40},title:{text:RRZESTATISTIKTRANSFER.languagePackage[t].headline_chart},yAxis:{title:{text:RRZESTATISTIKTRANSFER.languagePackage[t].ordinate_desc},accessibility:{description:RRZESTATISTIKTRANSFER.languagePackage[t].ordinate_desc}},xAxis:{title:{text:RRZESTATISTIKTRANSFER.abscissaTitle},accessibility:{description:RRZESTATISTIKTRANSFER.a11yAbscissa},categories:RRZESTATISTIKTRANSFER.abscissaDescriptiontext},tooltip:{valueSuffix:" ".concat(RRZESTATISTIKTRANSFER.languagePackage[t].ordinate_desc)},plotOptions:{series:{cursor:"pointer"}},series:[{name:a.toString(),data:l,color:u[4],zIndex:0},{name:n.toString(),data:c,zIndex:1,dashStyle:"ShortDashDot",color:u[1]},{name:i.toString(),data:d,zIndex:2,dashStyle:"ShortDot",color:u[2]}],exporting:{csv:{dateFormat:"%Y-%m-%d"}}});document.getElementById(t+"-getcsv").addEventListener("click",(function(){navigator.clipboard.writeText(T.getCSV())}))}))}}))},869:()=>{Highcharts.theme={navigator:{series:{color:"#5f98cf",lineColor:"#5f98cf"}},chart:{backgroundColor:{linearGradient:[0,0,500,500],stops:[[0,"rgb(255, 255, 255)"],[1,"rgb(255, 255, 255)"]]}},title:{style:{color:"#000",font:'bold 1rem "Roboto", Verdana, sans-serif'}},subtitle:{style:{color:"#666666",font:'bold 12px "Roboto MS", Verdana, sans-serif'}},legend:{itemStyle:{font:"9pt Roboto MS, Verdana, sans-serif",color:"black"},itemHoverStyle:{color:"gray"}}},Highcharts.setOptions(Highcharts.theme)}},e={};function a(n){var i=e[n];if(void 0!==i)return i.exports;var r=e[n]={exports:{}};return t[n](r,r.exports,a),r.exports}a.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return a.d(e,{a:e}),e},a.d=(t,e)=>{for(var n in e)a.o(e,n)&&!a.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:e[n]})},a.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),(()=>{"use strict";a(378),a(869),a(281)})()})();
//# sourceMappingURL=highchartsIndex.js.map