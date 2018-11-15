$(document).ready(() => {
    let graphDiv = $('#graphDiv')[0]
    let graphDiv2 = $('#graphDiv2')[0]
    
    let gameID = getParameterByName('gameID')
    let row = getParameterByName('row')
    let colNum = getParameterByName('i')
    let col = getParameterByName('col')
    let rowNum = getParameterByName('j')
    let table = getParameterByName('table')

    let model = JSON.parse(localStorage.getItem('model'))
    let allFeatures = flattenObj(model.features[table])

    let dataLocal = JSON.parse(localStorage.getItem(`data_${table}_${col}`))
    let inputTexts = JSON.parse(localStorage.getItem(`row_names_${table}_${col}`))
    allFeatures = filter(allFeatures, (k) => { return inputTexts.indexOf(k) > -1 })

    let coefficients = dataLocal['coefficients']
    let stdErrs = dataLocal['stdErrs']
    let file = model.columns[table].headers[col].href
    if (table === 'binomialQuestion') { 
        // if table is for binomial, row should not change because that represents how many levels of data are present
        // rowNum, however, should default to 1 (the first thing after the constant)
        // and be set later to which index of coefficient the user clicks
        file += row
        file += '.txt'
        rowNum = 1
        coefficients = coefficients[rowNum] // done once (for this level of data anyway)
        stdErrs = stdErrs[rowNum] // also done once
    }
    
    function getDataAndGraphs(coefIndex) { // the only thing that actually changes is 'j', aka the coefficient index
        let reqRow
        if (table === 'binomialQuestion') {
            reqRow = inputTexts[coefIndex]
        } else {
            reqRow = coefIndex
        }
        $.get('dataReader.php', { 'table': table, 'row': reqRow, 'col': col, 'file': file }, (data) => {
            let regressionVars = data['inputs']
            let regressionOutputs = data['outputs']

            let xVals = [], yVals = []
            if (regressionVars)
                xVals = regressionVars
            if (regressionOutputs)
                yVals = regressionOutputs
            let xTitle = allFeatures[reqRow]
            let yTitle = model.columns[table].headers[col].title
            if (table === 'levelCompletion') {
                yTitle = 'completion of ' + yTitle.toLowerCase() // this is a little more clear
            } else if (table === 'numLevels') {
                yTitle = 'number of levels completed at the boundary of ' + yTitle.toLowerCase()
            }
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
                    if (iNum == coefIndex || i === coefIndex) {
                        equation += `<b><span id="xTooltip${iNum}" href=# data-toggle="tooltip" data-placement="bottom" title="Measured input of ${xTitle.toLowerCase()} (this graph)">(` + 
                        new Number(coefficients[i]).toFixed(2) + '<span style="font-size:14px">±' + new Number(stdErrs[i]).toFixed(2) + 
                        `</span>)` + xTerm + '</span></b>'
                    } else {
                        equation += `${iNum > 0 ? ('<span class="coefficient" data-row="'+ (table === 'binomialQuestion' ? iNum : i) +'">') : ''}<span id="xTooltip${iNum}" href=# data-toggle="tooltip" data-placement="bottom" title="Measured input of ${allFeatures[inputTexts[iNum]].toLowerCase()}">(${new Number(coefficients[i]).toFixed(2)}<span style="font-size:14px">±${new Number(stdErrs[i]).toFixed(2)}</span>)${xTerm}</span>${iNum > 0 ? '</span>' : ''}`
                    }
                    iNum++
                }
            } else {
                equation = 'One or more coefficients are NaN'
            }
            $('#equationDiv').html($(equation))
            $('#infoDiv').html('Hover over any variable for an explanation of what it is.<br>Click any variable to see its graphs.')
            $('#downloadDiv').html(`<a href="download.php?file=${file}">Download this data as CSV</a>`)
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
                title: 'Data points for correlation between <b>' + xTitle.toLowerCase() + '</b> and <b>' + yTitle.toLowerCase() + '</b>',
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
                    title: (table === 'numLevels' ? 'Num levels completed' : yTitle),
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
    }
    if (table === 'binomialQuestion') {
        getDataAndGraphs(rowNum)
    } else {
        getDataAndGraphs(row)
    }

    // also bind an click handler for each coefficient to this function
    $('#equationDiv').on('click', '.coefficient', function(event) { getDataAndGraphs($(this).attr('data-row')) })

    function flattenObj(obj) {
        let flattenedObj = {}
        Object.keys(obj).forEach(key => {
            if (typeof obj[key] === 'object') {
                $.extend(flattenedObj, flattenObj(obj[key]))
            } else {
                flattenedObj[key] = obj[key]
            }
        })
        return flattenedObj
    }
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

function filter(obj, func) {
    let filteredObj = {}
    let keys = Object.keys(obj)
    for (let i in keys) {
        if (func(keys[i])) {
            filteredObj[keys[i]] = obj[keys[i]]
        }
    }
    return filteredObj
}