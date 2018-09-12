$(document).ready(() => {
    let graphDiv = $('#graphDiv')[0]
    let graphDiv2 = $('#graphDiv2')[0]
    
    let gameID = getParameterByName('gameID')
    let row = getParameterByName('row')
    let colNum = getParameterByName('i')
    let col = getParameterByName('col')
    let rowNum = getParameterByName('j')
    let table = getParameterByName('table')
    let data = JSON.parse(localStorage.getItem(`data_${table}_${col}`))
    let inputTexts = JSON.parse(localStorage.getItem(`row_names_${table}`))
    let coefficients = data['coefficients']
    let stdErrs = data['stdErrs']
    let regressionVars = data['regressionVars']
    let regressionOutputs = data['regressionOutputs']

    let xVals = [], yVals = []
    if (regressionVars)
        xVals = arrayColumn(regressionVars, parseInt(rowNum-1, 10))
    if (regressionOutputs)
        yVals = regressionOutputs
    let xTitle = row
    let yTitle = col
    let equation = ''
    if (coefficients && stdErrs) {
        equation = `<span id="yTooltip" href=# data-toggle="tooltip" data-placement="bottom" title="Predicted output of ${yTitle.toLowerCase()}">Y\'</span> = `
        for (let i = 0; i < coefficients.length; i++) {
            let xTerm = ''
            if (i > 0) {
                xTerm = 'X' + (i)
                equation += ' + '
            }
            if (i == rowNum) {
                equation += `<b><span id="xTooltip${i}" href=# data-toggle="tooltip" data-placement="bottom" title="Measured input of ${inputTexts[i].toLowerCase()} (this graph)">(` + 
                new Number(coefficients[i]).toFixed(2) + '<span style="font-size:14px">±' + new Number(stdErrs[i]).toFixed(2) + 
                `</span>)` + xTerm + '</span></b>'
            } else {
                equation += `<span id="xTooltip${i}" href=# data-toggle="tooltip" data-placement="bottom" title="Measured input of ${inputTexts[i].toLowerCase()}">(` + 
                new Number(coefficients[i]).toFixed(2) + '<span style="font-size:14px">±' + new Number(stdErrs[i]).toFixed(2) + 
                `</span>)` + xTerm + '</span>'
            }
        }
    } else {
        equation = 'One or more coefficients are NaN'
    }
    $('#equationDiv').html(equation)
    $('#infoDiv').html('Hover over any variable for an explanation of what it is.')
    $('[data-toggle="tooltip"').tooltip()

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
    let xValsFalse = xVals.filter((value, i, arr) => { if (yVals[i] == 0) return value })
    let xValsTrue = xVals.filter((value, i, arr) => { if (yVals[i] == 1) return value })
    let trace0 = {
        y: xValsFalse,
        boxpoints: 'all',
        type: 'box',
        name: yTitle + ' FALSE'
    }
    let trace1 = {
        y: xValsTrue,
        boxpoints: 'all',
        type: 'box',
        name: yTitle + ' TRUE'
    }
    let layout = {
        margin: { t: 35 },
        plot_bgcolor: '#F6F6F3',
        paper_bgcolor: '#F6F6F3',
        title: 'Data points for correlation between ' + xTitle.toLowerCase() + ' and ' + yTitle.toLowerCase().substr(0, yTitle.length-1)+yTitle.charAt(yTitle.length-1),
        yaxis: {
            title: xTitle,
            titlefont: {
                family: 'Courier New, monospace',
                size: 12,
                color: '#7f7f7f'
            }
        },
        showlegend: false
    }
    let layout2 = {
        margin: { t: 35 },
        plot_bgcolor: '#F6F6F3',
        paper_bgcolor: '#F6F6F3',
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
    Plotly.newPlot(graphDiv, [trace0, trace1], layout)
    Plotly.newPlot(graphDiv2, [trace], layout2)
})

function getParameterByName(name, url) {
    if (!url) url = window.location.href.replace(/%20/g, ' ').replace('#', 'num');
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