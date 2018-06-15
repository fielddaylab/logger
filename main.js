$(document).ready((event) => {
    $('.js-example-basic-single').select2() //initialize select boxes
    let graphLeft = $('#graphLeft')[0]
    let graphRight = $('#graphRight')[0]
    let goalsGraph1 = $('#goalsGraph1')[0]
    
    $(document).on('change', '#gameSelect', (event) => {
        event.preventDefault()
        on()
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val() }, (data, status, jqXHR) => {
            if (data.levels !== null) {
                $('#sessions').text(data.numSessions + ' sessions available')
                for (let i = 0; i < data.numSessions; i++) {
                    $('#sessionSelect').append($('<option>', { value:data.sessions[i], text:data.sessions[i]}))
                }
                $('#sessionSelect').val('18020410454796070') // the most interesting session
                for (let i = 0; i < data.levels.length; i++) {
                    $('#levelSelect').append($('<option>', { value:data.levels[i], text:data.levels[i]}))
                }
                let opt = $('#levelSelect option').sort(function (a,b) { return a.value.toUpperCase().localeCompare(b.value.toUpperCase(), {}, {numeric:true}) })
                $('#levelSelect').append(opt)
                $('#levelSelect').val($('#levelSelect option:first').val())
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
                getWavesData()
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
                let avgTime
                let totalTime = 0
                let numFails
                let numMovesPerChallenge
                let totalMoves = 0
                let avgMoves
                let moveTypeChangesPerLevel
                let moveTypeChangesTotal = 0
                let moveTypeChangesAvg
                let knobStdDevs
                let knobNumStdDevs
                let knobAmtsTotal = 0
                let knobAmtsAvg
                let knobSumTotal = 0
                let knobSumAvg

                let timesList = $('<ul></ul>').attr('id', 'times').addClass('collapse').css('font-size', '18px')
                $('#basicFeatures').append($(`<span><li>Times: <a href='#times' data-toggle='collapse' id='timesCollapseBtn' class='collapseBtn'>[+]</a></li></span>`).append(timesList)
                    .on('hide.bs.collapse', () => {$('#timesCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#timesCollapseBtn').html('[−]')}))
                let failsList = $('<ul></ul>').attr('id', 'fails').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeatures').append($(`<span><li style='margin-top:5px'>Failures: <a href='#fails' data-toggle='collapse' id='failsCollapseBtn' class='collapseBtn'>[+]</a></li></span>`).append(failsList)
                    .on('hide.bs.collapse', () => {$('#failsCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#failsCollapseBtn').html('[−]')}))
                let movesList = $('<ul></ul>').attr('id', 'moves').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeatures').append($(`<span><li style='margin-top:5px'>Number of moves: <a href='#moves' data-toggle='collapse' id='movesCollapseBtn' class='collapseBtn'>[+]</a></li></span>`).append(movesList)
                    .on('hide.bs.collapse', () => {$('#movesCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#movesCollapseBtn').html('[−]')}))
                let typesList = $('<ul></ul>').attr('id', 'types').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeatures').append($(`<span><li style='margin-top:5px'>Move type changes: <a href='#types' data-toggle='collapse' id='typesCollapseBtn' class='collapseBtn'>[+]</a></li></span>`).append(typesList)
                    .on('hide.bs.collapse', () => {$('#typesCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#typesCollapseBtn').html('[−]')}))
                let stdDevList = $('<ul></ul>').attr('id', 'stdDevs').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeatures').append($(`<span><li style='margin-top:5px'>Knob std devs (avg): <a href='#stdDevs' data-toggle='collapse' id='stdDevsCollapseBtn' class='collapseBtn'>[+]</a></li></span>`).append(stdDevList)
                    .on('hide.bs.collapse', () => {$('#stdDevsCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#stdDevsCollapseBtn').html('[−]')}))
                let amtsList = $('<ul></ul>').attr('id', 'amts').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeatures').append($(`<span><li style='margin-top:5px'>Knob max-min (avg): <a href='#amts' data-toggle='collapse' id='amtsCollapseBtn' class='collapseBtn'>[+]</a></li></span>`).append(amtsList)
                    .on('hide.bs.collapse', () => {$('#amtsCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#amtsCollapseBtn').html('[−]')}))
                let amtsTotalList = $('<ul></ul>').attr('id', 'amtsTotal').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeatures').append($(`<span><li style='margin-top:5px'>Knob max-min (total): <a href='#amtsTotal' data-toggle='collapse' id='amtsTotalCollapseBtn' class='collapseBtn'>[+]</a></li></span>`).append(amtsTotalList)
                    .on('hide.bs.collapse', () => {$('#amtsTotalCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#amtsTotalCollapseBtn').html('[−]')}))
                
                if (dataObj.times !== null) {
                    // Basic features stuff
                    let levelStartTime, levelEndTime, lastSlider = null, startIndices = [], endIndices = [], moveTypeChangesPerLevel = [], knobStdDevs = [], knobNumStdDevs = [], knobAmts = []
                    numFails = new Array($('#levelSelect option').size()).fill(0)
                    numMovesPerChallenge = new Array($('#levelSelect option').size()).fill(0)
                    moveTypeChangesPerLevel = new Array($('#levelSelect option').size()).fill(0)
                    knobStdDevs = new Array($('#levelSelect option').size()).fill(0)
                    knobNumStdDevs = new Array($('#levelSelect option').size()).fill(0)
                    knobAmts = new Array($('#levelSelect option').size()).fill(0)
                    startIndices = new Array($('#levelSelect option').size()).fill(undefined)
                    endIndices = new Array($('#levelSelect option').size()).fill(undefined)
                    for (let i in dataObj.times) { //for (let i = 0; i < dataObj.times.length; i++) {
                        let dataJson = JSON.parse(dataObj.data[i])
                        if (dataObj.events[i] === 'BEGIN') {
                            if (startIndices[dataObj.levels[i]] === undefined) { // check this space isn't filled by a previous attempt on the same level
                                startIndices[dataObj.levels[i]] = i
                            }
                        } else if (dataObj.events[i] === 'COMPLETE') {
                            if (endIndices[dataObj.levels[i]] === undefined) {
                                endIndices[dataObj.levels[i]] = i
                            }
                        } else if (dataObj.events[i] === 'FAIL') {
                            numFails[dataObj.levels[i]]++
                        } else if (dataObj.events[i] === "CUSTOM" && (dataJson.event_custom === 'SLIDER_MOVE_RELEASE' || dataJson.event_custom === 'ARROW_MOVE_RELEASE')) {
                            if (lastSlider !== dataJson.slider) {
                                moveTypeChangesPerLevel[dataObj.levels[i]]++
                            }
                            lastSlider = dataJson.slider
                            numMovesPerChallenge[dataObj.levels[i]]++
                            if (dataJson.event_custom === 'SLIDER_MOVE_RELEASE') { // arrows don't have std devs
                                knobNumStdDevs[dataObj.levels[i]]++
                                knobStdDevs[dataObj.levels[i]] += dataJson.stdev_val
                                knobAmts[dataObj.levels[i]] += (dataJson.max_val-dataJson.min_val)
                            }
                        }
                    }
                    for (let i = 0; i < Object.keys(startIndices).length; i++) {
                        if (startIndices[i] !== undefined) {
                            let levelTime = "-";
                            if (dataObj.times[endIndices[i]] && dataObj.times[startIndices[i]]) {
                                levelStartTime = new Date(dataObj.times[startIndices[i]].replace(/-/g, "/"))
                                levelEndTime = new Date(dataObj.times[endIndices[i]].replace(/-/g, "/"))
                                levelTime = (levelEndTime.getTime() - levelStartTime.getTime()) / 1000
                                totalTime += levelTime
                            }
    
                            totalMoves += numMovesPerChallenge[i]
                            moveTypeChangesTotal += moveTypeChangesPerLevel[i]
                            if (knobNumStdDevs[i] !== 0) {
                                knobAmtsTotal += (knobAmts[i]/knobNumStdDevs[i])
                            }
                            
                            knobSumTotal += knobAmts[i]
    
                            // append times
                            $('#times').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${levelTime} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                            // append fails
                            $('#fails').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${numFails[i]}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                            // append moves
                            $('#moves').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${numMovesPerChallenge[i]}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                            
                            // append types
                            $('#types').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${moveTypeChangesPerLevel[i]}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                            // append std devs
                            let knobAvgStdDev
                            if (knobNumStdDevs[i] === 0) {
                                knobAvgStdDev = 0
                            } else {
                                knobAvgStdDev = (knobStdDevs[i]/knobNumStdDevs[i])
                            }
                            $('#stdDevs').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${knobAvgStdDev.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                            // append knob amounts
                            let knobAvgAmt
                            if (knobNumStdDevs[i] === 0) {
                                knobAvgAmt = 0
                            } else {
                                knobAvgAmt = (knobAmts[i]/knobNumStdDevs[i])
                            }
                            $('#amts').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${knobAvgAmt.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                            // append knob total amounts
                            $('#amtsTotal').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${(knobAmts[i]).toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                        }
                    }
                    avgTime = totalTime / startIndices.filter(function(value) { return value !== undefined }).length
                    $('#times').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                    $('#times').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${totalTime} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    $('#times').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${avgTime.toFixed(2)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    avgMoves = totalMoves / startIndices.filter(function(value) { return value !== undefined }).length
                    $('#moves').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                    $('#moves').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${totalMoves}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    $('#moves').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${avgMoves.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    moveTypeChangesAvg = moveTypeChangesTotal / startIndices.filter(function(value) { return value !== undefined }).length
                    $('#types').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                    $('#types').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${moveTypeChangesTotal}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    $('#types').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${moveTypeChangesAvg.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    knobAmtsAvg = knobAmtsTotal / startIndices.filter(function(value) { return value !== undefined }).length
                    $('#amts').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                    $('#amts').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${knobAmtsTotal.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    $('#amts').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${knobAmtsAvg.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    knobSumAvg = knobSumTotal / startIndices.filter(function(value) { return value !== undefined }).length
                    $('#amtsTotal').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                    $('#amtsTotal').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${knobSumTotal.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    $('#amtsTotal').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${knobSumAvg.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    drawWavesGoals(dataObj, numMovesPerChallenge)
                }
            }
            off()
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError()
        })
    }

    function drawWavesGoals(dataObj, numMovesPerChallenge) {
        // Goals stuff
        $('#goalsDiv').html('Goal 1: Success')

        let distanceToGoal = []
        distanceToGoal = new Array(numMovesPerChallenge[$('#levelSelect').val()]).fill(0)
        let moveGoodness = distanceToGoal // an array of 0s
        let moveNumbers = []
        let cumulativeDistance = 0;
        for (let i = 0; i < numMovesPerChallenge[$('#levelSelect').val()]; i++) {
            let dataJson = JSON.parse(dataObj.data[i])
            let lastCloseness;
            moveNumbers.push(i)
            if (dataObj.events[i] === "CUSTOM" && (dataJson.event_custom === 'SLIDER_MOVE_RELEASE' || dataJson.event_custom === 'ARROW_MOVE_RELEASE')) {
                if (dataJson.event_custom === "SLIDER_MOVE_RELEASE") { // sliders have before and after closeness
                    if (dataJson.end_closeness < dataJson.begin_closeness) moveGoodness[i] = 1
                    else if (dataJson.end_closeness > dataJson.begin_closeness) moveGoodness[i] = -1

                    lastCloseness = dataJson.end_closeness
                } else { // arrow
                    if (!lastCloseness) lastCloseness = dataJson.closeness
                    if (dataJson.closeness < lastCloseness) moveGoodness[i] = -1
                    else if (dataJson.closeness > lastCloseness) moveGoodness[i] = 1

                    lastCloseness = dataJson.closeness
                }
                cumulativeDistance += moveGoodness[i]
                distanceToGoal[i] = cumulativeDistance
            }
        }
        let closenessTrace = {
            x: moveNumbers,
            y: distanceToGoal,
            line: {color: 'orange'},
            name: 'Net good moves',
            mode: 'lines+markers'
        }
        let graphData = [closenessTrace]
        let layout = {
            margin: { t: 35 },
            title: `Level ${$('#levelSelect').val()}`,
            height: 200,
            xaxis: {
                title: 'Move number',
                titlefont: {
                  family: 'Courier New, monospace',
                  size: 12,
                  color: '#7f7f7f'
                }
              },
              yaxis: {
                title: 'Net good moves',
                titlefont: {
                  family: 'Courier New, monospace',
                  size: 12,
                  color: '#7f7f7f'
                }
              }
        }
        Plotly.newPlot(goalsGraph1, graphData, layout)
    }

    function drawWavesChart(inData) {
        let xAmpLeft = [], xAmpRight = []
        let xFreqLeft = [], xFreqRight = []
        let xOffLeft = [], xOffRight = []
        let yAmpLeft = [], yAmpRight = []
        let yFreqLeft = [], yFreqRight = []
        let yOffLeft = [], yOffRight = []
        let hasLeftData = false, hasRightData = false
        if (inData.data !== null) {
            for (let i = 0; i < inData.data.length; i++) {
                let jsonData = JSON.parse(inData.data[i])
                if (jsonData.wave === 'left') {
                    hasLeftData = true
                    if (jsonData.slider === 'AMPLITUDE') {
                        xAmpLeft.push(inData.times[i])
                        yAmpLeft.push(jsonData.end_val)
                    } else if (jsonData.slider === 'WAVELENGTH') { 
                        xFreqLeft.push(inData.times[i])
                        yFreqLeft.push(jsonData.end_val)
                    } else if (jsonData.slider === 'OFFSET') {
                        xOffLeft.push(inData.times[i])
                        yOffLeft.push(jsonData.end_val)
                    }
                } else if (jsonData.wave === 'right') {
                    hasRightData = true
                    if (jsonData.slider === 'AMPLITUDE') {
                        xAmpRight.push(inData.times[i])
                        yAmpRight.push(jsonData.end_val)
                    } else if (jsonData.slider === 'WAVELENGTH') { 
                        xFreqRight.push(inData.times[i])
                        yFreqRight.push(jsonData.end_val)
                    } else if (jsonData.slider === 'OFFSET') {
                        xOffRight.push(inData.times[i])
                        yOffRight.push(jsonData.end_val)
                    }
                }
            }
            if (hasLeftData) {
                hideNoDataLeft()
                hideNoDataGoals()
            } else {
                showNoDataLeft()
            }
            if (hasRightData) {
                hideNoDataGoals()
                hideNoDataRight()
            } else {
                showNoDataRight()
            }
        } else {
            showNoDataLeft()
            showNoDataRight()
            showNoDataGoals()
        }

        let ampTraceLeft = {
            x: xAmpLeft,
            y: yAmpLeft,
            line: {color: 'red'},
            name: 'Amplitude',
            mode: 'lines+markers'
        }
        let freqTraceLeft = {
            x: xFreqLeft,
            y: yFreqLeft,
            line: {color: 'blue'},
            name: 'Frequency',
            mode: 'lines+markers'
        }
        let offTraceLeft = {
            x: xOffLeft,
            y: yOffLeft,
            line: {color: 'green'},
            name: 'Offset',
            mode: 'lines+markers'
        }

        let ampTraceRight = {
            x: xAmpRight,
            y: yAmpRight,
            line: {color: 'red'},
            name: 'Amplitude',
            mode: 'lines+markers'
        }
        let freqTraceRight = {
            x: xFreqRight,
            y: yFreqRight,
            line: {color: 'blue'},
            name: 'Frequency',
            mode: 'lines+markers'
        }
        let offTraceRight = {
            x: xOffRight,
            y: yOffRight,
            line: {color: 'green'},
            name: 'Offset',
            mode: 'lines+markers'
        }
        let wavesDataLeft = [ampTraceLeft, freqTraceLeft, offTraceLeft]
        let wavesDataRight = [ampTraceRight, freqTraceRight, offTraceRight]
        let layoutLeft = {
            margin: { t: 35 },
            title: 'Left Sliders',
            showlegend: true
        }
        let layoutRight = {
            margin: { t: 35 },
            title: 'Right Sliders',
            showlegend: true
        }
        Plotly.newPlot(graphLeft, wavesDataLeft, layoutLeft)
        Plotly.newPlot(graphRight, wavesDataRight, layoutRight)
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

    function showNoDataLeft() {
        $('#noDataOverlayLeft').css('display', 'block')
        $('#noDataOverlayGoals').css('display', 'block')
    }

    function hideNoDataLeft() {
        $('#noDataOverlayLeft').css('display', 'none')
        $('#noDataOverlayGoals').css('display', 'none')
    }

    function showNoDataRight() {
        $('#noDataOverlayRight').css('display', 'block')
    }

    function hideNoDataRight() {
        $('#noDataOverlayRight').css('display', 'none')
    }

    function showNoDataGoals() {
        $('#noDataOverlayGoals').css('display', 'block')
    }

    function hideNoDataGoals() {
        $('#noDataOverlayGoals').css('display', 'none')
    }
})