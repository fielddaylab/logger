$(document).ready(() => {
    let graphDiv = $('#graphDiv')[0]
    let graphDiv2 = $('#graphDiv2')[0]
    
    let gameID = getParameterByName('gameID')
    let row = getParameterByName('row')
    let col = getParameterByName('col')
    let regressionVars = JSON.parse(localStorage.getItem('regressionVars'))
    let equationVars = JSON.parse(localStorage.getItem('equationVars'))
    let inputTexts = ['# slider moves', '# type changes', '# levels completed', 'total time', 'avg knob max-min', '% correct questions']
    let intercepts = equationVars['intercepts']
    let coefficients = equationVars['coefficients']
    let stdErrs = equationVars['stdErrs']
    let rSqrs = equationVars['rSqrs']

    if (typeof rSqrs[col] === 'number') $('#rSqrDiv').text('R-squared: ' + rSqrs[col].toFixed(3))
    else $('#rSqrDiv').text('R-squared: ' + rSqrs[col])

    let xVals = [], yVals = []
    if (regressionVars[col][0])
        xVals = arrayColumn(regressionVars[col][0], parseInt(row, 10)+1)
    if (regressionVars[col][1])
        yVals = arrayColumn(regressionVars[col][1], 0)

    let xTitle, yTitle
    switch (row) {
        case '0':
            xTitle = '# slider moves'; break
        case '1':
            xTitle = '# move type changes'; break
        case '2':
            xTitle = '# levels completed'; break
        case '3':
            xTitle = 'Total time'; break
        case '4':
            xTitle = 'Knob max-min (average)'; break
        case '5':
            xTitle = '% correct questions'; break
    }
    switch (col) {
        case '0':
            yTitle = 'Completion of challenge 5'; break
        case '1':
            yTitle = 'Completion of challenge 1'; break
        case '2':
            yTitle = 'Completion of challenge 3'; break
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
    let equation = ''
    if (intercepts[col] && coefficients[col] && stdErrs[col]) {
        equation = `<span id="yTooltip" href=# data-toggle="tooltip" data-placement="bottom" title="Predicted output of ${yTitle.toLowerCase()}">Y\'</span> = (` + 
            intercepts[col].toFixed(2) + '<span style="font-size:14px">±' + stdErrs[col][0].toFixed(2) + '</span>)'
        for (let i = 0; i < coefficients[col].length; i++) {
            if (i == row) {
                equation += ' + (' + coefficients[col][i].toFixed(2) + '<span style="font-size:14px">±' + stdErrs[col][i].toFixed(2) + 
                    `</span>)<b><span id="xTooltip${i}" href=# data-toggle="tooltip" data-placement="bottom" title="Measured input of ${inputTexts[i]} (this graph)">X` + (i+1) + '</span></b>'
            } else {
                equation += ' + (' + coefficients[col][i].toFixed(2) + '<span style="font-size:14px">±' + stdErrs[col][i].toFixed(2) + 
                `</span>)<span id="xTooltip${i}" href=# data-toggle="tooltip" data-placement="bottom" title="Measured input of ${inputTexts[i]}">X` + (i+1) + '</span>'
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