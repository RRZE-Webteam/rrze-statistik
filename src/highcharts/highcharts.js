import Highcharts from 'highcharts';
import 'highcharts/modules/exporting';
import 'highcharts/modules/export-data';
import 'highcharts/modules/data';
import 'highcharts/modules/series-label';
import 'highcharts/themes/high-contrast-light';

import 'highcharts/modules/accessibility';

let currentYear = new Date().getFullYear();

//data is passed from Transfer.php
document.addEventListener("DOMContentLoaded", function (event) {
    //Check if data was passed from PHP
    if (RRZESTATISTIKTRANSFER.linechartDataset === "undefined") {
        return;
    } else {
        //read needed charts from dom
        let dataTypes = ["visits", "hits", "hosts", "files", "kbytes"];
        let chartTypes = [];

        dataTypes.forEach((dataType) => {
            if (document.getElementById(dataType)) {
                chartTypes.push(dataType);
            }
        });

        //Process dataset and split it based on the last three years
        const firstYear = currentYear - 2;
        const secondYear = currentYear - 1;
        const thirdYear = currentYear;

        //Create the dataset for each datatype
        chartTypes.forEach((datatype) => {
            let filterData = (dataset, year) => {
                let output = dataset.filter((data) => {
                    return data.year === year.toString();
                });
                return output;
            };
            let outputThirdYear = filterData(
                RRZESTATISTIKTRANSFER.linechartDataset,
                thirdYear
            );
            let outputSecondYear = filterData(
                RRZESTATISTIKTRANSFER.linechartDataset,
                secondYear
            );
            let outputFirstYear = filterData(
                RRZESTATISTIKTRANSFER.linechartDataset,
                firstYear
            );

            //Create an empty dataset array
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

                //Fill the empty Array with the length of 12 (one for each month)
                dataset.forEach((data) => {
                    datasetOutput[parseInt(data.month) - 1] = parseInt(
                        data[datatype]
                    );
                });
                return datasetOutput;
            };

            //Create three arrays, one for each year
            let datasetFirstYear = generateDatasets(outputFirstYear);
            let datasetSecondYear = generateDatasets(outputSecondYear);
            let datasetThirdYear = generateDatasets(outputThirdYear);

            //Create the Highcharts container for each datatype
            const chart = Highcharts.chart(datatype, {
                chart: {
                    type: RRZESTATISTIKTRANSFER.displayType,
                    renderTo: datatype,
                },

                legend: {
                    symbolWidth: 40,
                },

                colors: ["#fe6100", "#785ef0", "#dc267f", "#648fff", "#f45b5b", "#b68c51", "#397550", "#c0493d", "#4f4a7a", "#b381b3"],

                title: {
                    text: RRZESTATISTIKTRANSFER.languagePackage[datatype]
                        .headline_chart,
                },

                yAxis: {
                    title: {
                        text: RRZESTATISTIKTRANSFER.languagePackage[datatype]
                            .ordinate_desc,
                    },
                    accessibility: {
                        description:
                            RRZESTATISTIKTRANSFER.languagePackage[datatype]
                                .ordinate_desc,
                    },
                },

                xAxis: {
                    title: {
                        text: RRZESTATISTIKTRANSFER.abscissaTitle,
                    },
                    accessibility: {
                        description: RRZESTATISTIKTRANSFER.a11yAbscissa,
                    },
                    categories: RRZESTATISTIKTRANSFER.abscissaDescriptiontext,
                },

                tooltip: {
                    valueSuffix: ` ${RRZESTATISTIKTRANSFER.languagePackage[datatype].ordinate_desc}`,
                },

                plotOptions: {
                    series: {
                        cursor: "pointer",
                    },
                },

                series: [
                    {
                        name: firstYear.toString(),
                        data: datasetFirstYear,
                        zIndex: 0,
                    },
                    {
                        name: secondYear.toString(),
                        data: datasetSecondYear,
                        zIndex: 1,
                        dashStyle: "ShortDashDot",
                    },
                    {
                        name: thirdYear.toString(),
                        data: datasetThirdYear,
                        zIndex: 2,
                        dashStyle: "ShortDot",
                    },
                ],
                //define the Export value
                exporting: {
                    csv: {
                        dateFormat: "%Y-%m-%d",
                    },
                },
            });
            //Target the copy to clipboard button within the dashboard
            document
                .getElementById(datatype + "-getcsv")
                //Add an onclick listener which stores the CSV information of the chart inside the clipboard.
                .addEventListener("click", function () {
                    navigator.clipboard.writeText(chart.getCSV());
                });
        });
    }
});
