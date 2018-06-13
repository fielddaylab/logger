$(document).ready((event) => {
    $('.js-example-basic-single').select2() //initialize select boxes
    let tester = $('#tester')[0]
    Plotly.newPlot(tester, [], {margin: { t: 0 }})
    
    $(document).on('change', '#gameSelect', (event) => {
        event.preventDefault()
        on()
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val() }, (data, status, jqXHR) => {
            if (data.levels !== null) {
                $('#sessions').text(data.numSessions + ' sessions available')
                for (let i = 0; i < data.numSessions; i++) {
                    $('#sessionSelect').append($('<option>', { value:data.sessions[i], text:data.sessions[i]}))
                }
                for (let i = 0; i < data.levels.length; i++) {
                    $('#levelSelect').append($('<option>', { value:data.levels[i], text:data.levels[i]}))
                }
                let opt = $("#levelSelect option").sort(function (a,b) { return a.value.toUpperCase().localeCompare(b.value.toUpperCase(), {}, {numeric:true}) })
                $("#levelSelect").append(opt)
                if ($('#single').hasClass('active')) {
                    // initialization of single tab
                    selectSession(event)
                } else {
                    // do initialization of all tab
                }
            } else {
                off()
            }
          }, 'json').error((jqXHR, textStatus, errorThrown) => {
              off()
              showError()
          })
    })

    $(document).on('change', '#sessionSelect', (event) => {
        selectSession(event)
    })

    function selectSession(event) {
        if (event) event.preventDefault()
        on()
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val() }, (data, status, jqXHR) => {
            $("#scoreDisplay").html(data.numCorrect + " / " + data.numQuestions)
            $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val(), 'level': $('#levelSelect').val() }, (data, status, jqXHR) => {
                if ($('#gameSelect').val() === "WAVES") {
                    let dataObj = {data:JSON.parse(JSON.stringify(data.event_data)), times:data.times}
                    drawWavesChart(dataObj)
                    getWavesData()
                }
                off()
              }, 'json').error((jqXHR, textStatus, errorThrown) => {
                  off()
                  showError()
              })
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError()
        })
    }

    $(document).on('change', '#levelSelect', (event) => {
        event.preventDefault()
        on()
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val(), 'level': $('#levelSelect').val()}, (data, status, jqXHR) => {
            if ($('#gameSelect').val() === "WAVES") {
                let dataObj = {data:JSON.parse(JSON.stringify(data.event_data)), times:data.times}
                drawWavesChart(dataObj)
            }
            off()
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError()
        })
    })

    function getWavesData() {
        $('#basicFeatures').empty()
        // Variables holding "basic facts" for waves game, filled by database data
        let timePerChallenge
        let avgTime
        let totalTime
        let numMovesPerChallenge
        let totalMoves
        let avgMoves
        let moveTypeChangesPerLevel
        let moveTypeChangesTotal
        let moveTypeChangesAvg
        let knobStdDev
        let knobAvg
        let knobSumPerLevel
        let knobTotal

        let timesList = $('<ul></ul>').attr('id', 'times')
        timesList.css('font-size', '18px')

        $('#basicFeatures').append($(`<li>Times:</li>`).append(timesList))
        for (let i = 0; i < 4; i++) {
            $('#times').append($(`<li>Level ${i}: ${2}</li>`).css('font-size', '16px'))
        }
        $('#basicFeatures').append($(`<li>Var2: 5</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
        // $('#basicFeatures').append($(`<li>Time: ${variable}</li>`)).css('font-size', '18px')
    }

    function drawWavesChart(inData) {
        let xAmp = []
        let xFreq = []
        let xOff = []
        let yAmp = []
        let yFreq = []
        let yOff = []
        if (inData.data !== null) {
            hideNoData()
            for (let i = 0; i < inData.data.length; i++) {
                let jsonData = JSON.parse(inData.data[i])
                if (jsonData.slider === "AMPLITUDE") {
                    xAmp.push(inData.times[i])
                    yAmp.push(jsonData.end_val)
                } else if (jsonData.slider === "WAVELENGTH") { 
                    xFreq.push(inData.times[i])
                    yFreq.push(jsonData.end_val)
                } else if (jsonData.slider === "OFFSET") {
                    xOff.push(inData.times[i])
                    yOff.push(jsonData.end_val)
                }
            }
        } else {
            showNoData()
        }

        let ampTrace = {
            x: xAmp,
            y: yAmp,
            line: {color: "red"},
            name: 'Amplitude',
            mode: 'lines+markers'
        }
        let freqTrace = {
            x: xFreq,
            y: yFreq,
            line: {color: "blue"},
            name: 'Frequency',
            mode: 'lines+markers'
        }
        let offTrace = {
            x: xOff,
            y: yOff,
            line: {color: "green"},
            name: 'Offset',
            mode: 'lines+markers'
        }
        let wavesData = [ampTrace, freqTrace, offTrace]
        let layout = {
            margin: { t: 0 },
            showlegend: true
        }
        Plotly.newPlot(tester, wavesData, layout)
    }
    
    function on() {
        $('#loadingOverlay').css('display', 'block')
    }
    
    function off() {
        $('#loadingOverlay').css('display', 'none')
    }

    function showError() {
        $('#errorMessage').css('visibility', 'visible')
    }

    function showNoData() {
        $('#noDataOverlay').css('display', 'block')
    }

    function hideNoData() {
        $('#noDataOverlay').css('display', 'none')
    }
})