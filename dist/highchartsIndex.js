(()=>{var t={378:()=>{var t=(new Date).getFullYear();document.addEventListener("DOMContentLoaded",(function(e){if("undefined"===linechartDataset)console.log("Data could not be retrieved");else{console.log("Dataset successfully loaded");var o=function(t,e){return t.filter((function(t){return t.year===e.toString()}))},n=o(linechartDataset,t),r=o(linechartDataset,t-1),l=o(linechartDataset,t-2),a=function(t){var e=[null,null,null,null,null,null,null,null,null,null,null,null];return t.forEach((function(t){e[parseInt(t.month)-1]=parseInt(t.visits)})),e},i=a(l),s=a(r),c=a(n);console.log(n),console.log(r),console.log(l),Highcharts.chart("container",{chart:{type:"areaspline"},title:{text:headlineDescriptiontext},legend:{layout:"vertical",align:"left",verticalAlign:"top",x:150,y:100,floating:!0,borderWidth:1,backgroundColor:Highcharts.defaultOptions.legend.backgroundColor||"#FFFFFF"},xAxis:{categories:abscissaDescriptiontext},yAxis:{title:{text:ordinateDescriptiontext}},tooltip:{shared:!0,valueSuffix:tooltipDesc},credits:{enabled:!1},plotOptions:{areaspline:{fillOpacity:.3}},series:[{name:(t-2).toString(),data:i},{name:(t-1).toString(),data:s},{name:t,data:c}]})}}))},869:()=>{Highcharts.theme={colors:["#648fff","#dc267f","#fe6100","#785ef0","#ffb000"],chart:{backgroundColor:{linearGradient:[0,0,500,500],stops:[[0,"rgb(255, 255, 255)"],[1,"rgb(255, 255, 255)"]]}},title:{style:{color:"#000",font:'bold 1.4rem "Roboto", Verdana, sans-serif'}},subtitle:{style:{color:"#666666",font:'bold 12px "Roboto MS", Verdana, sans-serif'}},legend:{itemStyle:{font:"9pt Roboto MS, Verdana, sans-serif",color:"black"},itemHoverStyle:{color:"gray"}}},Highcharts.setOptions(Highcharts.theme)}},e={};function o(n){var r=e[n];if(void 0!==r)return r.exports;var l=e[n]={exports:{}};return t[n](l,l.exports,o),l.exports}o.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return o.d(e,{a:e}),e},o.d=(t,e)=>{for(var n in e)o.o(e,n)&&!o.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:e[n]})},o.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),(()=>{"use strict";o(378),o(869)})()})();
//# sourceMappingURL=highchartsIndex.js.map