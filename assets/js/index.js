var linechart_dataset;
//const dataset = JSON.parse(linechart_dataset);

/*
Datenstruktur:
0 {monat: "3", jahr: "2020", hits: "222475", files: "188973", hosts: "2112", …}
1 {monat: "4", jahr: "2020", hits: "176342", files: "162564", hosts: "1983", …}
2 {monat: "5", jahr: "2020", hits: "363197", files: "348613", hosts: "1886", …}
3 {monat: "6", jahr: "2020", hits: "107689", files: "95848", hosts: "1766", …}
4 {monat: "7", jahr: "2020", hits: "75649", files: "66212", hosts: "1610", …}
5 {monat: "8", jahr: "2020", hits: "90330", files: "80660", hosts: "1559", …}
6 {monat: "9", jahr: "2020", hits: "108088", files: "92169", hosts: "1936", …}
7 {monat: "10", jahr: "2020", hits: "1091658", files: "1081699", hosts: "2389", …}
8 {monat: "11", jahr: "2020", hits: "84665", files: "75062", hosts: "2175", …}
9 {monat: "12", jahr: "2020", hits: "73790", files: "64368", hosts: "1928", …}
10 {monat: "1", jahr: "2021", hits: "83159", files: "73664", hosts: "2187", …}
11 {monat: "2", jahr: "2021", hits: "86247", files: "78145", hosts: "2052", …}
12 {monat: "3", jahr: "2021", hits: "82362", files: "73844", hosts: "2169", …}
13 {monat: "4", jahr: "2021", hits: "81352", files: "73883", hosts: "1811", …}
14 {monat: "5", jahr: "2021", hits: "79582", files: "70208", hosts: "1827", …}
15 {monat: "6", jahr: "2021", hits: "91063", files: "80757", hosts: "1797", …}
16 {monat: "7", jahr: "2021", hits: "86508", files: "76681", hosts: "1752", …}
17 {monat: "8", jahr: "2021", hits: "65733", files: "58904", hosts: "1652", …}
18 {monat: "9", jahr: "2021", hits: "111481", files: "101451", hosts: "2064", …}
19 {monat: "10", jahr: "2021", hits: "122952", files: "114787", hosts: "2633", …}
20 {monat: "11", jahr: "2021", hits: "216927", files: "199389", hosts: "2404", …}
21 {monat: "12", jahr: "2021", hits: "171441", files: "157202", hosts: "2167", …}
22 {monat: "1", jahr: "2022", hits: "189770", files: "174241", hosts: "2528", …}
23 {monat: "2", jahr: "2022", hits: "169965", files: "157750", hosts: "2751", …}
*/

document.addEventListener("DOMContentLoaded", function(event) {
  console.log(linechart_dataset);
});


Highcharts.chart('container', {
    chart: {
      type: 'areaspline'
    },
    title: {
      text: 'Seitenbesucher der letzten 24 Monate'
    },
    legend: {
      layout: 'vertical',
      align: 'left',
      verticalAlign: 'top',
      x: 150,
      y: 100,
      floating: true,
      borderWidth: 1,
      backgroundColor:
        Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF'
    },
    xAxis: {
      categories: [
        'Jan',
        'Feb',
        'März',
        'April',
        'Mai',
        'Juni',
        'Juli',
        'Aug',
        'Sep',
        'Okt',
        'Nov',
        'Dez',      
      ],
    },
    yAxis: {
      title: {
        text: 'Besucher'
      }
    },
    tooltip: {
      shared: true,
      valueSuffix: ' Besucher/Monat'
    },
    credits: {
      enabled: false
    },
    plotOptions: {
      areaspline: {
        fillOpacity: 0.3
      }
    },
    series: [
      {
      name: '2020',
      data: [null, null, 9561, 8953, 8963, 8445, 8450, 9173, 9847, 11756, 10304, 10061]
    },
      {
      name: '2021',
      data: [9532, 8955, 9813, 8530, 9956, 10080, 10939, 9645, 10973, 13373, 13204, 13119]
    },
             {
      name: '2022',
      data: [17474, 7553, null, null, null, null, null]
    }, 
            ]
  });