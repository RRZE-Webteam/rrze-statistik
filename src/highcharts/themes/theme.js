Highcharts.theme = {
    navigator: {
        series: {
            color: '#5f98cf',
            lineColor: '#5f98cf'
        },
    },
    chart: {
        backgroundColor: {
            linearGradient: [0, 0, 500, 500],
            stops: [
                [0, 'rgb(255, 255, 255)'],
                [1, 'rgb(255, 255, 255)']
            ]
        },
    },
    title: {
        style: {
            color: '#000',
            font: 'bold 1rem "Roboto", Verdana, sans-serif'
        }
    },
    subtitle: {
        style: {
            color: '#666666',
            font: 'bold 12px "Roboto MS", Verdana, sans-serif'
        }
    },
    legend: {
        itemStyle: {
            font: '9pt Roboto MS, Verdana, sans-serif',
            color: 'black'
        },
        itemHoverStyle:{
            color: 'gray'
        }
    }
};
// Apply the theme
Highcharts.setOptions(Highcharts.theme);