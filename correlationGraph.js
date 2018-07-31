$(document).ready(() => {
    let graphDiv = $('#graphDiv')[0]

    let parameters = {
        'gameID': getParameterByName('gameID'),
        'row': getParameterByName('row'),
        'col': getParameterByName('col')
    }
    $.get('responsePage.php', parameters, (data, status, jqXHR) => {
        let trace = {
            x: data,
            y: [1, 2, 3, 4, 5],
            type: 'scatter'
        }
        let layout = {
            margin: { t: 35 },
            plot_bgcolor: '#F6F6F3',
            paper_bgcolor: '#F6F6F3',
            height: 200,
            xaxis: {
                autotick: false,
                dtick: 1,
                title: 'Number of questions answered',
                titlefont: {
                    family: 'Courier New, monospace',
                    size: 12,
                    color: '#7f7f7f'
                }
            },
            yaxis: {
                title: 'Number of sessions',
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