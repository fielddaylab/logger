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
        $.get('responsePage.php', { 'isBasicFeatures': true, 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val()}, (data, status, jqXHR) => {
            if ($('#gameSelect').val() === "WAVES") {
                let dataObj = {data:JSON.parse(JSON.stringify(data.event_data)), times:data.times, events:JSON.parse(JSON.stringify(data.events)), levels:data.levels}
                $('#basicFeatures').empty()
                // Variables holding "basic facts" for waves game, filled by database data
                let timePerChallenge = dataObj.times
                let avgTime
                let totalTime = 0
                let numFails

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

                let timesList = $('<ul></ul>').attr('id', 'times').addClass('collapse in').css('font-size', '18px')
                $('#basicFeatures').append($(`<span><li>Times: <a href='#times' data-toggle='collapse' id='timesCollapseBtn' class='collapseBtn'>[−]</a></li></span>`).append(timesList)
                    .on('hide.bs.collapse', () => {$('#timesCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#timesCollapseBtn').html('[−]')}))
                let failsList = $('<ul></ul>').attr('id', 'fails').addClass('collapse in').css({'font-size':'18px'})
                $('#basicFeatures').append($(`<span><li style='margin-top:5px'>Failures: <a href='#fails' data-toggle='collapse' id='failsCollapseBtn' class='collapseBtn'>[−]</a></li></span>`).append(failsList)
                    .on('hide.bs.collapse', () => {$('#failsCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#failsCollapseBtn').html('[−]')}))
                let movesList = $('<ul></ul>').attr('id', 'moves').addClass('collapse in').css({'font-size':'18px'})
                $('#basicFeatures').append($(`<span><li style='margin-top:5px'>Number of moves: <a href='#moves' data-toggle='collapse' id='movesCollapseBtn' class='collapseBtn'>[−]</a></li></span>`).append(movesList)
                    .on('hide.bs.collapse', () => {$('#movesCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#movesCollapseBtn').html('[−]')}))
                if (dataObj.times !== null) {
                    let levelStartTime, levelEndTime, startIndices = [], endIndices = []
                    numFails = new Array(dataObj.times.length).fill(0)
                    numMovesPerChallenge = new Array(dataObj.times.length).fill(0)
                    for (let i = 0; i < dataObj.times.length; i++) {
                        let dataJson = JSON.parse(dataObj.data[i])
                        if (dataObj.events[i] === 'BEGIN') {
                            startIndices[dataObj.levels[i]] = i
                        } else if (dataObj.events[i] === 'COMPLETE') {
                            endIndices[dataObj.levels[i]] = i
                        } else if (dataObj.events[i] === 'FAIL') {
                            numFails[dataObj.levels[i]]++
                        } else if (dataObj.events[i] === "CUSTOM" &&
                            (dataJson.event_custom === 'SLIDER_MOVE_RELEASE' ||
                            dataJson.event_custom === 'ARROW_MOVE_RELEASE')) {
                                numMovesPerChallenge[dataObj.levels[i]]++
                        }
                    }
                    for (let i = 0; i < startIndices.length; i++) {
                        levelStartTime = new Date(dataObj.times[startIndices[i]].replace(/-/g, "/"))
                        levelEndTime = new Date(dataObj.times[endIndices[i]].replace(/-/g, "/"))
                        let levelTime = (levelEndTime.getTime() - levelStartTime.getTime()) / 1000
                        totalTime += levelTime
                        $('#times').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${levelTime} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    }
                    avgTime = totalTime / startIndices.length
                    $('#times').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                    $('#times').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${totalTime} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    $('#times').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${avgTime.toFixed(2)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    for (let i = 0; i < startIndices.length; i++) {
                        // append fails
                        $('#fails').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${numFails[i]}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                        // append moves
                        $('#moves').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${numMovesPerChallenge[i]}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    }
                }
            }
            off()
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError()
        })
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