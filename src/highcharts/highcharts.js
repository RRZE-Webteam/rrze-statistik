let currentYear = new Date().getFullYear();



//data is passed from Transfer.php
//Check if document is ready
document.addEventListener("DOMContentLoaded", function (event) {
    //Check if data was passed from PHP
    if (linechartDataset === "undefined") {
        console.log("Data could not be retrieved");
    } else {
        //read existent datatypes from dom
        dataTypes = ["visits", "hits", "hosts", "files", "kbytes"];
        chartTypes = [];

        //for Each datatype check if it is places as css id in the dom
        dataTypes.forEach( dataType => {
            if (document.getElementById(dataType)) {
                //if yes, read the data from the dom
                chartTypes.push(dataType);
            }
        });
        console.log(chartTypes);

        //Process dataset and split it based on the last three years
        console.log("Dataset successfully loaded");

        const firstYear = currentYear - 2;
        const secondYear = currentYear - 1;
        const thirdYear = currentYear;

        console.log(linechartDataset);
        
        //Create the dataset for each datatype
        chartTypes.forEach((datatype) => {
            let filterData = (dataset, year) => {
                let output = dataset.filter((data) => {
                    return data.year === year.toString();
                });
                return output;
            };
            let outputThirdYear = filterData(linechartDataset, thirdYear);
            let outputSecondYear = filterData(linechartDataset, secondYear);
            let outputFirstYear = filterData(linechartDataset, firstYear);

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

            //Load the theme colors
            var colors = Highcharts.getOptions().colors;

            //Create the Highcharts container for each datatype
            const chart = Highcharts.chart(datatype, {
                chart: {
                    type: displayType,
                    renderTo: datatype,
                },

                legend: {
                    symbolWidth: 40,
                },

                title: {
                    text: languagePackage[datatype].headline_chart,
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
                        text: abscissaTitle,
                    },
                    accessibility: {
                        description: a11yAbscissa,
                    },
                    categories: abscissaDescriptiontext,
                },

                tooltip: {
                    valueSuffix: ` ${languagePackage[datatype].ordinate_desc}`,
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
                        color: colors[4],
                        zIndex: 0,
                    },
                    {
                        name: secondYear.toString(),
                        data: datasetSecondYear,
                        zIndex: 1,
                        dashStyle: "ShortDashDot",
                        color: colors[1],
                    },
                    {
                        name: thirdYear.toString(),
                        data: datasetThirdYear,
                        zIndex: 2,
                        dashStyle: "ShortDot",
                        color: colors[2],
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
