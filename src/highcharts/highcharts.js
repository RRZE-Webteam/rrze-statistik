let currentYear = new Date().getFullYear();
const datatypes = ["visits", "hits", "hosts", "files", "kbytes"];

/*
Example Data retrieved from Transfer.php
linechartDataset: {monat: "3", jahr: "2020", hits: "222475", files: "188973", hosts: "2112", …}
...
languagePackage: {visits: {ordinate_desc: "Seitenbesucher", headline_chart: "Besucher der letzten 24 Monate", tooltip_desc: " Besucher / Monat"}}
*/
document.addEventListener("DOMContentLoaded", function (event) {
    console.log(languagePackage);
    if (linechartDataset === "undefined") {
        console.log("Data could not be retrieved");
    } else {
        console.log("Dataset successfully loaded");

        const firstYear = currentYear - 2;
        const secondYear = currentYear - 1;
        const thirdYear = currentYear;

        datatypes.forEach((datatype) => {
            console.log(datatype);
            let filterData = (dataset, year) => {
                let output = dataset.filter((data) => {
                    return data.year === year.toString();
                });
                return output;
            };
            let outputThirdYear = filterData(linechartDataset, thirdYear);
            let outputSecondYear = filterData(linechartDataset, secondYear);
            let outputFirstYear = filterData(linechartDataset, firstYear);
            let generateDatasets = (dataset) => {
                let datasetDummy = [
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                ];
                let datasetOutput = datasetDummy;
                dataset.forEach((data) => {
                    datasetOutput[parseInt(data.month) - 1] = parseInt(
                        data[datatype]
                    );
                });
                return datasetOutput;
            };

            let datasetFirstYear = generateDatasets(outputFirstYear);
            let datasetSecondYear = generateDatasets(outputSecondYear);
            let datasetThirdYear = generateDatasets(outputThirdYear);

            Highcharts.chart(datatype, {
                chart: {
                    type: "areaspline",
                    height: (2.5 / 3 * 100) + '%',
                },
                title: {
                    text: languagePackage[datatype].headline_chart,
                },
                legend: {
                    layout: "vertical",
                    align: "left",
                    verticalAlign: "top",
                    x: 150,
                    y: 100,
                    floating: true,
                    borderWidth: 1,
                    backgroundColor:
                        Highcharts.defaultOptions.legend.backgroundColor ||
                        "#FFFFFF",
                },
                xAxis: {
                    categories: abscissaDescriptiontext,
                },
                yAxis: {
                    title: {
                        text: languagePackage[datatype].ordinate_desc,
                    },
                },
                tooltip: {
                    shared: true,
                    valueSuffix: languagePackage[datatype].tooltip_desc,
                },
                credits: {
                    enabled: false,
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.3,
                    },
                },
                series: [
                    {
                        name: firstYear.toString(),
                        data: datasetFirstYear,
                    },
                    {
                        name: secondYear.toString(),
                        data: datasetSecondYear,
                    },
                    {
                        name: thirdYear,
                        data: datasetThirdYear,
                    },
                ],
            });
/*
            let container = document.querySelector(`#${datatype}`);

            let html = `<div class="highcharts-description highcharts-linked-description rrze-statistik-table"><table><tr><th>${datatype}/month</th><th>${firstYear}</th><th>${secondYear}</th><th>${thirdYear}</th></tr>`;

            for (let i = 0; i < 12; i++) {
                html += `<tr><td>${abscissaDescriptiontext[i]}</td><td>${datasetFirstYear[i]}</td><td>${datasetSecondYear[i]}</td><td>${datasetThirdYear[i]}</td></tr>`;
            }
            html += "</table>";
            console.log(languagePackage[datatype].ordinate_desc);
/*
            window.addEventListener('resize', function () { 
                setTimeout(function(){ 
                "use strict";
                window.location.reload(); 
            }, 2000);
            });

            container.insertAdjacentHTML("beforeend", html);*/
        });
        
    }
});
