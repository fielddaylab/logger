$(document).ready(() => {
    let graphDiv = $('#graphDiv')[0]
    let graphDiv2 = $('#graphDiv2')[0]
    
    let gameID = getParameterByName('gameID')
    let row = getParameterByName('row')
    let colNum = getParameterByName('i')
    let col = getParameterByName('col')
    let rowNum = getParameterByName('j')
    let table = getParameterByName('table')
    let dataLocal = JSON.parse(localStorage.getItem(`data_${table}_${col}`))
    let inputTexts = JSON.parse(localStorage.getItem(`row_names_${table}`))
    let coefficients = dataLocal['coefficients']
    let stdErrs = dataLocal['stdErrs']
    $.get('dataReader.php', { 'table': table, 'row': row, 'col': col }, (data) => {
        let regressionVars = data['inputs']
        let regressionOutputs = data['outputs']

        let xVals = [], yVals = []
        if (regressionVars)
            xVals = regressionVars
        if (regressionOutputs)
            yVals = regressionOutputs
        let xTitle = row
        let yTitle = col
        let equation = ''
        if (coefficients && stdErrs) {
            equation = `<span id="yTooltip" href=# data-toggle="tooltip" data-placement="bottom" title="Predicted output of ${yTitle}">Y\'</span> = `
            let iNum = 0;
            for (let i in coefficients) {
                let xTerm = ''
                if (iNum > 0) {
                    xTerm = 'X' + (iNum)
                    equation += ' + '
                }
                if (iNum == rowNum) {
                    equation += `<b><span id="xTooltip${iNum}" href=# data-toggle="tooltip" data-placement="bottom" title="Measured input of ${inputTexts[iNum]} (this graph)">(` + 
                    new Number(coefficients[i]).toFixed(2) + '<span style="font-size:14px">±' + new Number(stdErrs[i]).toFixed(2) + 
                    `</span>)` + xTerm + '</span></b>'
                } else {
                    equation += `<span id="xTooltip${iNum}" href=# data-toggle="tooltip" data-placement="bottom" title="Measured input of ${inputTexts[iNum]}">(` + 
                    new Number(coefficients[i]).toFixed(2) + '<span style="font-size:14px">±' + new Number(stdErrs[i]).toFixed(2) + 
                    `</span>)` + xTerm + '</span>'
                }
                iNum++
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
        let xValsFalse, xValsTrue
        if (table === 'numLevels') {
            let column = col.substr(3)
            xValsFalse = xVals.filter((value, i, arr) => { return (yVals[i] < column) })
            xValsTrue = xVals.filter((value, i, arr) => { return (yVals[i] >= column) })
        } else {
            xValsFalse = xVals.filter((value, i, arr) => { return (yVals[i] == 0) })
            xValsTrue = xVals.filter((value, i, arr) => { return (yVals[i] == 1) })
        }
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
            title: 'Data points for correlation between ' + xTitle + ' and ' + yTitle.toLowerCase().substr(0, yTitle.length-1)+yTitle.charAt(yTitle.length-1),
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