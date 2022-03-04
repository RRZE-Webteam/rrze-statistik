let currentYear = new Date().getFullYear();
//const { __, _x, _n, sprintf } = wp.i18n;
/*
Datenstruktur:
0 {monat: "3", jahr: "2020", hits: "222475", files: "188973", hosts: "2112", …}
...
*/
document.addEventListener("DOMContentLoaded", function(event) {

console.log(linechartDataset);
  if (linechartDataset === undefined){
    console.log("Data could not be retrieved");
    console.log(linechartDataset);
  } else {
    console.log("else");
    console.log(linechartDataset);
  let filterData = (dataset, year) => {
    let output = dataset.filter(data => {
      return data.jahr === year.toString();
    });
    return output;
  }

  let outputThirdYear = filterData(linechartDataset, currentYear);
  let outputSecondYear = filterData(linechartDataset, currentYear-1);
  let outputFirstYear = filterData(linechartDataset, currentYear-2);

  let generateDatasets = (dataset) => {
    let datasetDummy = [null, null, null, null, null, null, null, null, null, null, null, null];
    let datasetOutput = datasetDummy;
    dataset.forEach(data => {
      datasetOutput[parseInt(data.monat)-1] = parseInt(data.visits);
    });
    return datasetOutput;
  }

  let datasetFirstYear = generateDatasets(outputFirstYear);
  let datasetSecondYear = generateDatasets(outputSecondYear);
  let datasetThirdYear = generateDatasets(outputThirdYear);
  
Highcharts.chart('container', {
    chart: {
      type: 'areaspline'
    },
    title: {
      text: headlineDescriptiontext
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
      categories: abscissaDescriptiontext,
    },
    yAxis: {
      title: {
        text: ordinateDescriptiontext
      }
    },
    tooltip: {
      shared: true,
      valueSuffix: tooltipDesc
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
      name: (currentYear-2).toString(),
      data: datasetFirstYear
    },
      {
      name: (currentYear-1).toString(),
      data: datasetSecondYear
    },
             {
      name: currentYear,
      data: datasetThirdYear
    }, 
            ]
  });
  };
  
});
