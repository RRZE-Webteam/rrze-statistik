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