$(document).ready(() => {
    let graphDiv = $('#graphDiv')[0]
    
    let gameID = getParameterByName('gameID')
    let row = getParameterByName('row')
    let col = getParameterByName('col')
    let regressionVars = JSON.parse(localStorage.getItem('regressionVars'))

    let xVals = arrayColumn(regressionVars[col][0], row)
    let yVals = arrayColumn(regressionVars[col][1], 0)

    let trace = {
        x: xVals,
        y: yVals,
        mode: 'markers',
        type: 'scatter'
    }
    let layout = {
        margin: { t: 35 },
        plot_bgcolor: '#F6F6F3',
        paper_bgcolor: '#F6F6F3',
        xaxis: {
            title: row,
            titlefont: {
                family: 'Courier New, monospace',
                size: 12,
                color: '#7f7f7f'
            }
        },
        yaxis: {
            title: col,
            titlefont: {
                family: 'Courier New, monospace',
                size: 12,
                color: '#7f7f7f'
            }
        },
        showlegend: false
    }
    Plotly.newPlot(graphDiv, [trace], layout)
})

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

function arrayColumn(array, columnName) {
    return array.map(function(value,index) {
        return value[columnName];
    })
}