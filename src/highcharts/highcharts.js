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

        datatypes.forEach((datatype) => {
            console.log(datatype);
            let filterData = (dataset, year) => {
                let output = dataset.filter((data) => {
                    return data.year === year.toString();
                });
                return output;
            };
            let outputThirdYear = filterData(linechartDataset, currentYear);
            let outputSecondYear = filterData(
                linechartDataset,
                currentYear - 1
            );
            let outputFirstYear = filterData(linechartDataset, currentYear - 2);
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
                        name: (currentYear - 2).toString(),
                        data: datasetFirstYear,
                    },
                    {
                        name: (currentYear - 1).toString(),
                        data: datasetSecondYear,
                    },
                    {
                        name: currentYear,
                        data: datasetThirdYear,
                    },
                ],
            });
        });
        
    }
});
