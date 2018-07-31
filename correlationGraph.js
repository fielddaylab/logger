$(document).ready(() => {
    let graphDiv = $('#graphDiv')[0]
    
    let gameID = getParameterByName('gameID')
    let row = getParameterByName('row')
    let col = getParameterByName('col')
    let regressionVars = JSON.parse(localStorage.getItem('regressionVars'))

    let xVals = arrayColumn(regressionVars[col][0], row)
    let yVals = arrayColumn(regressionVars[col][1], 0)

    let xTitle, yTitle
    console.log(typeof row)
    switch (row) {
        case '0':
            xTitle = '# moves'; break
        case '1':
            xTitle = '# move type changes'; break
        case '2':
            xTitle = 'Total time'; break
        case '3':
            xTitle = 'Knob max-min (average)'; break
    }
    switch (col) {
        case '0':
            yTitle = 'Completion of game'; break
        case '1':
            yTitle = 'Completion of level 10'; break
        case '2':
            yTitle = 'Completion of level 20'; break
        case '3':
            yTitle = 'Question 1 Answer A'; break
        case '4':
            yTitle = 'Question 1 Answer B'; break
        case '5':
            yTitle = 'Question 1 Answer C'; break
        case '6':
            yTitle = 'Question 1 Answer D'; break
        case '7':
            yTitle = 'Question 2 Answer A'; break
        case '8':
            yTitle = 'Question 2 Answer B'; break
        case '9':
            yTitle = 'Question 2 Answer C'; break
        case '10':
            yTitle = 'Question 2 Answer D'; break
        case '11':
            yTitle = 'Question 3 Answer A'; break
        case '12':
            yTitle = 'Question 3 Answer B'; break
        case '13':
            yTitle = 'Question 3 Answer C'; break
        case '14':
            yTitle = 'Question 3 Answer D'; break
        case '15':
            yTitle = 'Question 4 Answer A'; break
        case '16':
            yTitle = 'Question 4 Answer B'; break
        case '17':
            yTitle = 'Question 4 Answer C'; break
        case '18':
            yTitle = 'Question 4 Answer D'; break
    }
    console.log(xTitle)
    console.log(col)

    let trace = {
        x: xVals,
        y: yVals,
        marker: {
            opacity: 0.1,
            size: 16
        },
        mode: 'markers',
        type: 'scatter'
    }
    let layout = {
        margin: { t: 35 },
        plot_bgcolor: '#F6F6F3',
        paper_bgcolor: '#F6F6F3',
        title: 'Data points for correlation between ' + xTitle.toLowerCase() + ' and ' + yTitle.toLowerCase().substr(0, yTitle.length-1)+yTitle.charAt(yTitle.length-1),
        xaxis: {
            title: xTitle,
            titlefont: {
                family: 'Courier New, monospace',
                size: 12,
                color: '#7f7f7f'
            }
        },
        yaxis: {
            title: yTitle,
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