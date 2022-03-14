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

        console.log(linechartDataset);
        datatypes.forEach((datatype) => {
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

            var colors = Highcharts.getOptions().colors;

            Highcharts.chart(datatype, {
                chart: {
                    type: "spline",
                },

                legend: {
                    symbolWidth: 40,
                },

                title: {
                    text: languagePackage[datatype].headline_chart,
                },

                subtitle: {
                    text: sourceText,
                },

                yAxis: {
                    title: {
                        text: languagePackage[datatype].ordinate_desc,
                    },
                    accessibility: {
                        description: languagePackage[datatype].ordinate_desc,
                    },
                },

                xAxis: {
                    title: {
                        text: "Time",
                    },
                    accessibility: {
                        description:
                            "Time from December 2010 to September 2019",
                    },
                    categories: abscissaDescriptiontext,
                },

                tooltip: {
                    valueSuffix: ` ${languagePackage[datatype].ordinate_desc}`,
                },

                plotOptions: {
                    series: {
                        point: {
                            events: {
                                click: function () {
                                    window.location.href =
                                        this.series.options.website;
                                },
                            },
                        },
                        cursor: "pointer",
                    },
                },

                series: [
                    {
                        name: firstYear.toString(),
                        data: datasetFirstYear,
                        website: logsUrl,
                        color: colors[4],
                        zIndex: 0,
                        accessibility: {
                            description:
                                "This is the most used screen reader in 2019",
                        },
                    },
                    {
                        name: secondYear.toString(),
                        data: datasetSecondYear,
                        zIndex: 1,
                        website: logsUrl,
                        dashStyle: "ShortDashDot",
                        color: colors[1],
                    },
                    {
                        name: thirdYear.toString(),
                        data: datasetThirdYear,
                        zIndex: 2,
                        website: logsUrl,
                        dashStyle: "ShortDot",
                        color: colors[2],
                    },
                ],
            });
        });
    }
});
