$(document).ready((event) => {
    //$('.js-example-basic-single').select2() //initialize select boxes
    //$('#sessionSelectAll').select2({ disabled: true })
    let graphLeft = $('#graphLeft')[0]
    let graphRight = $('#graphRight')[0]
    let goalsGraph1 = $('#goalsGraph1')[0]
    let goalsGraph2 = $('#goalsGraph2')[0]

    let graphLeftAll = $('#graphLeftAll')[0]
    let graphRightAll = $('#graphRightAll')[0]
    let goalsGraph1All = $('#goalsGraph1All')[0]
    let goalsGraph2All = $('#goalsGraph2All')[0]

    let totalSessions
    let currentSessions = []
    
    $(document).on('change', '#gameSelect', (event) => {
        event.preventDefault()
        on()
        $('#gameIDForm').val($('#gameSelect').val())
        fastClear($('#levelSelect'))
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val() }, (data, status, jqXHR) => {
            if (data.levels !== null) {
                totalSessions = data.numSessions
                currentSessions = data.sessions
                $('#sessions').text('Showing ' + Math.min(data.numSessions, $('#maxSessions').val()) + ' of ' + totalSessions + ' available sessions')
                // Get default dates from first and last times
                let startDate = new Date(data.times[data.times.lastIndexOf('0000-00-00 00:00:00')+1].replace(/-/g, "/"))
                let startdd = startDate.getDate()
                let startmm = startDate.getMonth() + 1
                let startyyyy = startDate.getFullYear()
                if (startdd < 10) { startdd = '0' + startdd }
                if (startmm < 10) { startmm = '0' + startmm }

                let endDate = new Date(data.times[data.times.length-1].replace(/-/g, "/"))
                let enddd = endDate.getDate()
                let endmm = endDate.getMonth() + 1
                let endyyyy = endDate.getFullYear()
                if (enddd < 10) { enddd = '0' + enddd }
                if (endmm < 10) { endmm = '0' + endmm }

                $('#startDate').val(startyyyy+'-'+startmm+'-'+startdd)
                $('#endDate').val(endyyyy+'-'+endmm+'-'+enddd)

                let options = []
                fastClear($('#sessionSelect'))

                for (let i = 0; i < $('#maxSessions').val(); i++) { //for (let i = 0; i < data.sessions.length; i++) {
                    let newOpt = document.createElement('option')
                    newOpt.value = data.sessions[i]
                    newOpt.text = i + ' | ' + data.sessions[i] + ' | ' + data.times[i]
                    options.push(newOpt)
                }
                $('#sessionSelect').append(options)
                $('#sessionSelect').append($('<option>18020410454796070</option>').attr({'value':18020410454796070}))
                $('#sessionSelect').val('18020410454796070') // the most interesting session

                options = []
                for (let i = 0; i < data.levels.length; i++) {
                    let newOpt = document.createElement('option')
                    newOpt.value = data.levels[i]
                    newOpt.text = data.levels[i]
                    options.push(newOpt)
                }
                $('#levelSelect').append(options)
                let opt = $('#levelSelect option').sort(function (a,b) { return a.value.toUpperCase().localeCompare(b.value.toUpperCase(), {}, {numeric:true}) })

                $('#levelSelect').append(opt)
                $('#levelSelect').val($('#levelSelect option:first').val())

                selectSession(event, false)
                // do initialization of all tab
                selectGameAll(event)
            } else {
                off()
                hideError()
            }
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError(jqXHR.responseText)
        })
    })

    $(document).on('change', '#sessionSelect', (event) => {
        selectSession(event)
    })

    $(document).on('submit', '#filterForm', (event) => {
        event.preventDefault()
        if ($('#gameSelect').val() !== 'empty') {
            $('#filterModal').modal('hide')
            on()
            let promises = []
            // Single tab stuff
            promises.push($.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'minMoves': $('#minMoves').val(), 'minQuestions': $('#minQuestions').val(),
                    'minLevels': $('#minLevels').val(), 'startDate': $('#startDate').val(), 'endDate': $('#endDate').val(), 'maxSessions': $('#maxSessions').val() }, (data, status, jqXHR) => {
                let numSessionsToDisplay = Math.min(data.sessions.length, $('#maxSessions').val())
                $('#sessions').text('Showing ' + numSessionsToDisplay + ' of ' + totalSessions + ' available sessions')
                currentSessions = data.sessions

                let options = []

                fastClear($('#sessionSelect'))

                for (let i = 0; i < numSessionsToDisplay; i++) { //for (let i = 0; i < data.sessions.length; i++) {
                    let newOpt = document.createElement('option')
                    newOpt.value = data.sessions[i]
                    newOpt.text = i + ' | ' + data.sessions[i] + ' | ' + data.times[i]
                    options.push(newOpt)
                }
                $('#sessionSelect').append(options)
                $('#sessionSelect').val($('#sessionSelect option:first').val())
                selectSession(event, false)
            }, 'json').error((jqXHR, textStatus, errorThrown) => {
                $('#filterModal').modal('hide')
                off()
                showError(jqXHR.responseText)
            }), // All page stuff
            $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'isAll': true, 'minMoves': $('#minMoves').val(), 'minQuestions': $('#minQuestions').val(),
            'minLevels': $('#minLevels').val(), 'startDate': $('#startDate').val(), 'endDate': $('#endDate').val(), 'maxSessions': $('#maxSessions').val(), 'isFiltered': true}, (data, status, jqXHR) => {
                $('#scoreDisplayAll').html(data.totalNumCorrect + ' / ' + data.totalNumQuestions + ' (' + (100*data.totalNumCorrect/data.totalNumQuestions).toFixed(1) + '%)')
                hideError()
            }, 'json').error((jqXHR, textStatus, errorThrown) => {
                off()
                showError(jqXHR.responseText)
            }), 
            $.get('responsePage.php', { 'isAll': true, 'isAggregate': true, 'isBasicFeatures': true, 'gameID': $('#gameSelect').val(), 'minMoves': $('#minMoves').val(), 'minQuestions': $('#minQuestions').val(),
                    'minLevels': $('#minLevels').val(), 'startDate': $('#startDate').val(), 'endDate': $('#endDate').val(), 'maxSessions': $('#maxSessions').val(), 'isFiltered': true }, (data, status, jqXHR) => {
                fastClear($('#basicFeaturesAll'))
                let timesList = $('<ul></ul>').attr('id', 'timesAll').addClass('collapse').css('font-size', '18px')
                $('#basicFeaturesAll').append($(`<span><li>Times: <a href='#timesAll' data-toggle='collapse' id='timesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(timesList)
                    .on('hide.bs.collapse', () => {$('#timesCollapseBtnAll').html('[+]')})
                    .on('show.bs.collapse', () => {$('#timesCollapseBtnAll').html('[−]')}))
                let movesList = $('<ul></ul>').attr('id', 'movesAll').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Number of moves: <a href='#movesAll' data-toggle='collapse' id='movesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(movesList)
                    .on('hide.bs.collapse', () => {$('#movesCollapseBtnAll').html('[+]')})
                    .on('show.bs.collapse', () => {$('#movesCollapseBtnAll').html('[−]')}))
                let typesList = $('<ul></ul>').attr('id', 'typesAll').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Move type changes: <a href='#typesAll' data-toggle='collapse' id='typesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(typesList)
                    .on('hide.bs.collapse', () => {$('#typesCollapseBtnAll').html('[+]')})
                    .on('show.bs.collapse', () => {$('#typesCollapseBtnAll').html('[−]')}))
                let stdDevList = $('<ul></ul>').attr('id', 'stdDevsAll').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Knob std devs (avg): <a href='#stdDevsAll' data-toggle='collapse' id='stdDevsCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(stdDevList)
                    .on('hide.bs.collapse', () => {$('#stdDevsCollapseBtnAll').html('[+]')})
                    .on('show.bs.collapse', () => {$('#stdDevsCollapseBtnAll').html('[−]')}))
                let amtsList = $('<ul></ul>').attr('id', 'amtsAll').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Knob max-min (avg): <a href='#amtsAll' data-toggle='collapse' id='amtsCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(amtsList)
                    .on('hide.bs.collapse', () => {$('#amtsCollapseBtnAll').html('[+]')})
                    .on('show.bs.collapse', () => {$('#amtsCollapseBtnAll').html('[−]')}))
                let amtsTotalList = $('<ul></ul>').attr('id', 'amtsTotalAll').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Knob max-min (total): <a href='#amtsTotalAll' data-toggle='collapse' id='amtsTotalCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(amtsTotalList)
                    .on('hide.bs.collapse', () => {$('#amtsTotalCollapseBtnAll').html('[+]')})
                    .on('show.bs.collapse', () => {$('#amtsTotalCollapseBtnAll').html('[−]')}))
    
                for (let i = 0; i < data.times.length; i++) {
                    // append times
                    $('#timesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.times[i].toFixed(1)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                    // append moves
                    $('#movesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.numMoves[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    
                    // append types
                    $('#typesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.moveTypeChanges[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                    // append std devs
                    $('#stdDevsAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.knobStdDevs[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                    // append knob amounts
                    $('#amtsAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.avgMaxMin[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                    // append knob total amounts
                    $('#amtsTotalAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.totalMaxMin[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                }
    
                $('#timesAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#timesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalTime.toFixed(1)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#timesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgTime.toFixed(1)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                $('#movesAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#movesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalMoves.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#movesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgMoves.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                $('#typesAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#typesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalMoveChanges.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#typesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgMoveChanges.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                $('#amtsAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#amtsAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalKnobAvgs.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#amtsAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgKnobAvgs.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
    
                $('#amtsTotalAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#amtsTotalAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalKnobTotals.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#amtsTotalAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgKnobTotals.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                //drawWavesGoalsAll(dataObj, numMovesPerChallenge[$('#levelSelect').val()])
            }, 'json').error((jqXHR, textStatus, errorThrown) => {
                $('#filterModal').modal('hide')
                off()
                showError(jqXHR.responseText)
            }))

            $.when.apply($, promises).then(() => {
                off()
            })
        } else {
            $('#formError').show()
            $('#filterModal').modal('hide')
            $('#formError').html('Please select a game before filtering sessions.')
        }
    })

    $(document).on('hide.bs.modal', '#filterModal', (event) => {
        $('#formError').hide()
    })

    function selectSession(event, shouldHideOverlay = true) {
        if (event) event.preventDefault()
        on()
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val() }, (data, status, jqXHR) => {
            $("#scoreDisplay").html(data.numCorrect + " / " + data.numQuestions)
            hideError()
            $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val(), 'level': $('#levelSelect').val() }, (data, status, jqXHR) => {
                if ($('#gameSelect').val() === "WAVES") {
                    let dataObj = {data:JSON.parse(JSON.stringify(data.event_data)), times:data.times}
                    drawWavesChart(dataObj)
                    getWavesData(shouldHideOverlay)
                }
                if (shouldHideOverlay) {
                    off()
                }
                hideError()
              }, 'json').error((jqXHR, textStatus, errorThrown) => {
                  off()
                  showError(jqXHR.responseText)
              })
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError(jqXHR.responseText)
        })
    }

    function selectGameAll(event) {
        if (event) event.preventDefault()
        on()
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'isAll': true }, (data, status, jqXHR) => {
            $('#scoreDisplayAll').html(data.totalNumCorrect + ' / ' + data.totalNumQuestions + ' (' + (100*data.totalNumCorrect/data.totalNumQuestions).toFixed(1) + '%)')
            if ($('#gameSelect').val() === "WAVES") {
                getWavesDataAll()
            }
            hideError()
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError(jqXHR.responseText)
        })
    }

    $(document).on('change', '#levelSelect', (event) => {
        event.preventDefault()
        if ($('#levelSelect').val() !== $('#levelSelectAll').val()) {
            on()
            //$('#levelSelectAll').val($('#levelSelect').val()).trigger('change')
            $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val(), 'level': $('#levelSelect').val()}, (data, status, jqXHR) => {
                if ($('#gameSelect').val() === "WAVES") {
                    let dataObj = {data:JSON.parse(JSON.stringify(data.event_data)), times:data.times}
                    drawWavesChart(dataObj)
                    getWavesData()
                }
                off()
                hideError()
            }, 'json').error((jqXHR, textStatus, errorThrown) => {
                off()
                showError(jqXHR.responseText)
            })
        }
    })

    function getWavesData(shouldHideOverlay = true) {
        on()
        fastClear($('#basicFeatures'))
        $.get('responsePage.php', { 'isBasicFeatures': true, 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val()}, (data, status, jqXHR) => {
            if ($('#gameSelect').val() === "WAVES") {
                let timesList = $('<ul></ul>').attr('id', 'times').addClass('collapse').css('font-size', '18px')
                $('#basicFeatures').append($(`<span><li>Times: <a href='#times' data-toggle='collapse' id='timesCollapseBtn' class='collapseBtn'>[+]</a></li></span>`).append(timesList)
                    .on('hide.bs.collapse', () => {$('#timesCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#timesCollapseBtn').html('[−]')}))
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
 
                let levels = data.dataObj.levels.filter((element, i, array) => {
                    return i == array.indexOf(element)
                })

                levels.forEach((i) => {
                    // append times
                    $('#times').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.levelTimes[i]} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append moves
                    $('#moves').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.numMovesPerChallenge[i]}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    
                    // append types
                    $('#types').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.moveTypeChangesPerLevel[i]}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append std devs
                    $('#stdDevs').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.knobStdDevs[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append knob amounts
                    $('#amts').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.knobAvgs[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append knob total amounts
                    $('#amtsTotal').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${(data.knobTotalAmts[i]).toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                })

                $('#times').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#times').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalTime} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#times').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgTime.toFixed(1)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#moves').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#moves').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalMoves}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#moves').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgMoves.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#types').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#types').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.moveTypeChangesTotal}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#types').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.moveTypeChangesAvg.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#amts').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#amts').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.knobAmtsTotalAvg.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#amts').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.knobAmtsAvgAvg.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#amtsTotal').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#amtsTotal').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.knobSumTotal.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#amtsTotal').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.knobTotalAvg.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                drawWavesGoals(data, shouldHideOverlay)
            }
            if (shouldHideOverlay) {
                off()
            }
            hideError()
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError(jqXHR.responseText)
        })
    }

    function getWavesDataAll() {
        on()
        fastClear($('#basicFeaturesAll'))
        $.get('responsePage.php', { 'isBasicFeatures': true, 'gameID': $('#gameSelect').val(), 'isAll': true, 'isAggregate': true }, (data, status, jqXHR) => {
            let timesList = $('<ul></ul>').attr('id', 'timesAll').addClass('collapse').css('font-size', '18px')
            $('#basicFeaturesAll').append($(`<span><li>Times: <a href='#timesAll' data-toggle='collapse' id='timesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(timesList)
                .on('hide.bs.collapse', () => {$('#timesCollapseBtnAll').html('[+]')})
                .on('show.bs.collapse', () => {$('#timesCollapseBtnAll').html('[−]')}))
            let movesList = $('<ul></ul>').attr('id', 'movesAll').addClass('collapse').css({'font-size':'18px'})
            $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Number of moves: <a href='#movesAll' data-toggle='collapse' id='movesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(movesList)
                .on('hide.bs.collapse', () => {$('#movesCollapseBtnAll').html('[+]')})
                .on('show.bs.collapse', () => {$('#movesCollapseBtnAll').html('[−]')}))
            let typesList = $('<ul></ul>').attr('id', 'typesAll').addClass('collapse').css({'font-size':'18px'})
            $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Move type changes: <a href='#typesAll' data-toggle='collapse' id='typesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(typesList)
                .on('hide.bs.collapse', () => {$('#typesCollapseBtnAll').html('[+]')})
                .on('show.bs.collapse', () => {$('#typesCollapseBtnAll').html('[−]')}))
            let stdDevList = $('<ul></ul>').attr('id', 'stdDevsAll').addClass('collapse').css({'font-size':'18px'})
            $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Knob std devs (avg): <a href='#stdDevsAll' data-toggle='collapse' id='stdDevsCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(stdDevList)
                .on('hide.bs.collapse', () => {$('#stdDevsCollapseBtnAll').html('[+]')})
                .on('show.bs.collapse', () => {$('#stdDevsCollapseBtnAll').html('[−]')}))
            let amtsList = $('<ul></ul>').attr('id', 'amtsAll').addClass('collapse').css({'font-size':'18px'})
            $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Knob max-min (avg): <a href='#amtsAll' data-toggle='collapse' id='amtsCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(amtsList)
                .on('hide.bs.collapse', () => {$('#amtsCollapseBtnAll').html('[+]')})
                .on('show.bs.collapse', () => {$('#amtsCollapseBtnAll').html('[−]')}))
            let amtsTotalList = $('<ul></ul>').attr('id', 'amtsTotalAll').addClass('collapse').css({'font-size':'18px'})
            $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Knob max-min (total): <a href='#amtsTotalAll' data-toggle='collapse' id='amtsTotalCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(amtsTotalList)
                .on('hide.bs.collapse', () => {$('#amtsTotalCollapseBtnAll').html('[+]')})
                .on('show.bs.collapse', () => {$('#amtsTotalCollapseBtnAll').html('[−]')}))

            for (let i = 0; i < data.times.length; i++) {
                // append times
                $('#timesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.times[i].toFixed(1)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                // append moves
                $('#movesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.numMoves[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                
                // append types
                $('#typesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.moveTypeChanges[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                // append std devs
                $('#stdDevsAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.knobStdDevs[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                // append knob amounts
                $('#amtsAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.avgMaxMin[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                // append knob total amounts
                $('#amtsTotalAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.totalMaxMin[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
            }

            $('#timesAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
            $('#timesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalTime.toFixed(1)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
            $('#timesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgTime.toFixed(1)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

            $('#movesAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
            $('#movesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalMoves.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
            $('#movesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgMoves.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

            $('#typesAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
            $('#typesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalMoveChanges.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
            $('#typesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgMoveChanges.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

            $('#amtsAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
            $('#amtsAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalKnobAvgs.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
            $('#amtsAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgKnobAvgs.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

            $('#amtsTotalAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
            $('#amtsTotalAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.totalKnobTotals.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
            $('#amtsTotalAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.avgKnobTotals.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

            off()
            //drawWavesGoalsAll(dataObj, numMovesPerChallenge[$('#levelSelect').val()])
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError(jqXHR.responseText)
        })
    }

    function drawWavesGoals(data, shouldHideOverlay = true) {
        // Goals stuff
        let dataObj = data.dataObj
        let numMovesPerChallenge = data.numMovesPerChallengeArray[$('#levelSelect').val()]
        $('#goalsDiv1').html('Goal 1: Completing the challenge')
        let distanceToGoal = []
        let moveGoodness = []
        if (numMovesPerChallenge) {
            distanceToGoal = new Array(numMovesPerChallenge.length).fill(0)
            moveGoodness = new Array(numMovesPerChallenge.length).fill(0) // an array of 0s
        }
        let moveNumbers = []
        let cumulativeDistance = 0
        let lastCloseness1

        for (let i in numMovesPerChallenge) {
            let dataJson = JSON.parse(dataObj.data[i])
            if (dataObj.events[i] === "CUSTOM" && (dataJson.event_custom === 'SLIDER_MOVE_RELEASE' || dataJson.event_custom === 'ARROW_MOVE_RELEASE')) {
                if (dataJson.event_custom === "SLIDER_MOVE_RELEASE") { // sliders have before and after closeness
                    if (dataJson.end_closeness < dataJson.begin_closeness) moveGoodness[i] = 1
                    else if (dataJson.end_closeness > dataJson.begin_closeness) moveGoodness[i] = -1

                    lastCloseness1 = dataJson.end_closeness
                } else { // arrow
                    if (!lastCloseness1) lastCloseness1 = dataJson.closeness
                    if (dataJson.closeness < lastCloseness1) moveGoodness[i] = -1
                    else if (dataJson.closeness > lastCloseness1) moveGoodness[i] = 1

                    lastCloseness1 = dataJson.closeness
                }
            }
            moveNumbers[i] = i
            cumulativeDistance += moveGoodness[i]
            distanceToGoal[i] = cumulativeDistance
        }

        goalSlope1 = (distanceToGoal[distanceToGoal.length-1] - distanceToGoal[0]) / (moveNumbers[moveNumbers.length-1] - moveNumbers[0])

        let closenessTrace1 = {
            x: moveNumbers,
            y: distanceToGoal,
            line: {color: 'orange'},
            name: 'Net good moves',
            mode: 'lines+markers'
        }
        let layout1 = {
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
            },
            showlegend: false,
            annotations: [{
                    x: (moveNumbers[0]+ moveNumbers[moveNumbers.length-1]) / 2,
                    y: (distanceToGoal[0] + distanceToGoal[distanceToGoal.length-1]) / 2,
                    xref: 'x',
                    yref: 'y',
                    text: 'Slope: ' + goalSlope1.toFixed(2),
                    showArrow: true,
                    arrowhead: 0,
                    ax: 0,
                    ay: -40,
                    bgcolor: 'darkgray',
                    borderpad: 4
            }]
        }
        let slopeTrace1 = {
            x: [moveNumbers[0], moveNumbers[moveNumbers.length-1]],
            y: [distanceToGoal[0], distanceToGoal[distanceToGoal.length-1]],
            line: {color: 'blue'},
            name: 'Slope',
            mode: 'lines'
        }
        let graphData1 = [closenessTrace1, slopeTrace1]

        if (graphData1[0].x.length > 0 && graphData1[0].y.length > 0 && 
            graphData1[1].x.length > 0 && graphData1[1].y.length > 0) {
            
            Plotly.newPlot(goalsGraph1, graphData1, layout1)
        }

        $('#goalsDiv2').html('Goal 2: Maxing slider values')
        $('#goalsDiv2').css('display', 'block')
        $('#goalsGraph2').css('display', 'block')
        if (numMovesPerChallenge) {
            distanceToGoal = new Array(numMovesPerChallenge.length).fill(0)
            moveGoodness = new Array(numMovesPerChallenge.length).fill(0) // an array of 0s
        }
        cumulativeDistance = 0
        indicesToSplice = []
        let graph_min_x = -50
        let graph_max_x =  50
        let graph_max_y =  50
        let graph_max_offset = graph_max_x
        let graph_max_wavelength = graph_max_x*2
        let graph_max_amplitude = graph_max_y*(3/5)
        let graph_default_offset = (graph_min_x+graph_max_x)/2
        let graph_default_wavelength = (2+(graph_max_x*2))/2
        let graph_default_amplitude = graph_max_y/4
        let lastCloseness = [], thisCloseness = []
        lastCloseness['OFFSET', 'left'] = lastCloseness['OFFSET', 'right'] = graph_max_offset-graph_default_offset
        lastCloseness['AMPLITUDE', 'left'] = lastCloseness['AMPLITUDE', 'right'] = graph_max_amplitude-graph_default_amplitude
        lastCloseness['WAVELENGTH', 'left'] = lastCloseness['WAVELENGTH', 'right'] = graph_max_wavelength-graph_default_wavelength
        for (let i in numMovesPerChallenge) {
            let dataJson = JSON.parse(dataObj.data[i])
            if (dataObj.events[i] === 'CUSTOM' && (dataJson.event_custom === 'SLIDER_MOVE_RELEASE' || dataJson.event_custom === 'ARROW_MOVE_RELEASE')) {
                if (dataJson.slider ===  'AMPLITUDE') {
                    thisCloseness[dataJson.slider, dataJson.wave] = graph_max_amplitude-dataJson.end_val
                } else if (dataJson.slider === 'OFFSET') {
                    thisCloseness[dataJson.slider, dataJson.wave] = graph_max_offset-dataJson.end_val
                } else if (dataJson.slider === 'WAVELENGTH') {
                    thisCloseness[dataJson.slider, dataJson.wave] = graph_max_wavelength-dataJson.end_val
                }

                if (dataJson.event_custom === 'SLIDER_MOVE_RELEASE') { // sliders have before and after closeness
                    if (thisCloseness[dataJson.slider, dataJson.wave] < lastCloseness[dataJson.slider, dataJson.wave]) moveGoodness[i] = 1
                    else if (thisCloseness[dataJson.slider, dataJson.wave] > lastCloseness[dataJson.slider, dataJson.wave]) moveGoodness[i] = -1

                    lastCloseness[dataJson.slider] = thisCloseness[dataJson.slider]
                } else { // arrow
                    if (thisCloseness[dataJson.slider, dataJson.wave] < lastCloseness[dataJson.slider, dataJson.wave]) moveGoodness[i] = -1
                    else if (thisCloseness[dataJson.slider, dataJson.wave] > lastCloseness[dataJson.slider, dataJson.wave]) moveGoodness[i] = 1

                    lastCloseness[dataJson.slider, dataJson.wave] = thisCloseness[dataJson.slider, dataJson.wave]
                }
            }
            moveNumbers[i] = i
            cumulativeDistance += moveGoodness[i]
            distanceToGoal[i] = cumulativeDistance
        }

        goalSlope2 = (distanceToGoal[distanceToGoal.length-1] - distanceToGoal[0]) / (moveNumbers[moveNumbers.length-1] - moveNumbers[0])

        let closenessTrace2 = {
            x: moveNumbers,
            y: distanceToGoal,
            line: {color: 'orange'},
            name: 'Net good moves',
            mode: 'lines+markers'
        }
        let layout2 = {
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
            },
            showlegend: false,
            annotations: [{
                    x: (moveNumbers[0]+ moveNumbers[moveNumbers.length-1]) / 2,
                    y: (distanceToGoal[0] + distanceToGoal[distanceToGoal.length-1]) / 2,
                    xref: 'x',
                    yref: 'y',
                    text: 'Slope: ' + goalSlope2.toFixed(2),
                    showArrow: true,
                    arrowhead: 0,
                    ax: 0,
                    ay: -40,
                    bgcolor: 'darkgray',
                    borderpad: 4
            }]
        }
        let slopeTrace2 = {
            x: [moveNumbers[0], moveNumbers[moveNumbers.length-1]],
            y: [distanceToGoal[0], distanceToGoal[distanceToGoal.length-1]],
            line: {color: 'blue'},
            name: 'Slope',
            mode: 'lines'
        }
        let graphData2 = [closenessTrace2, slopeTrace2]

        if (graphData2[0].x.length > 0 && graphData2[0].y.length > 0 && 
            graphData2[1].x.length > 0 && graphData2[1].y.length > 0) {
            
            Plotly.newPlot(goalsGraph2, graphData2, layout2)
        }
    }

    function drawWavesChart(inData) {
        let xAmpLeft = [], xAmpRight = []
        let xFreqLeft = [], xFreqRight = []
        let xOffLeft = [], xOffRight = []
        let yAmpLeft = [], yAmpRight = []
        let yFreqLeft = [], yFreqRight = []
        let yOffLeft = [], yOffRight = []
        let hasLeftData = false, hasRightData = false
        let ampLeftNum = [], ampRightNum = []
        let freqLeftNum = [], freqRightNum = []
        let offLeftNum = [], offRightNum = []
        if (inData.data !== null) {
            for (let i = 0; i < inData.data.length; i++) {
                let jsonData = JSON.parse(inData.data[i])
                if (jsonData.wave === 'left') {
                    hasLeftData = true
                    if (jsonData.slider === 'AMPLITUDE') {
                        xAmpLeft.push(inData.times[i])
                        yAmpLeft.push(jsonData.end_val)
                        ampLeftNum.push('Move ' + i)
                    } else if (jsonData.slider === 'WAVELENGTH') { 
                        xFreqLeft.push(inData.times[i])
                        yFreqLeft.push(jsonData.end_val)
                        freqLeftNum.push('Move ' + i)
                    } else if (jsonData.slider === 'OFFSET') {
                        xOffLeft.push(inData.times[i])
                        yOffLeft.push(jsonData.end_val)
                        offLeftNum.push('Move ' + i)
                    }
                } else if (jsonData.wave === 'right') {
                    hasRightData = true
                    if (jsonData.slider === 'AMPLITUDE') {
                        xAmpRight.push(inData.times[i])
                        yAmpRight.push(jsonData.end_val)
                        ampRightNum.push('Move ' + i)
                    } else if (jsonData.slider === 'WAVELENGTH') { 
                        xFreqRight.push(inData.times[i])
                        yFreqRight.push(jsonData.end_val)
                        freqRightNum.push('Move ' + i)
                    } else if (jsonData.slider === 'OFFSET') {
                        xOffRight.push(inData.times[i])
                        yOffRight.push(jsonData.end_val)
                        offRightNum.push('Move ' + i)
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
            text: ampLeftNum,
            line: {color: 'red'},
            name: 'Amplitude',
            mode: 'lines+markers'
        }
        let freqTraceLeft = {
            x: xFreqLeft,
            y: yFreqLeft,
            text: freqLeftNum,
            line: {color: 'blue'},
            name: 'Frequency',
            mode: 'lines+markers'
        }
        let offTraceLeft = {
            x: xOffLeft,
            y: yOffLeft,
            text: offLeftNum,
            line: {color: 'green'},
            name: 'Offset',
            mode: 'lines+markers'
        }

        let ampTraceRight = {
            x: xAmpRight,
            y: yAmpRight,
            text: ampRightNum,
            line: {color: 'red'},
            name: 'Amplitude',
            mode: 'lines+markers'
        }
        let freqTraceRight = {
            x: xFreqRight,
            y: yFreqRight,
            text: freqRightNum,
            line: {color: 'blue'},
            name: 'Frequency',
            mode: 'lines+markers'
        }
        let offTraceRight = {
            x: xOffRight,
            y: yOffRight,
            text: offRightNum,
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

    function showError(error) {
        $('#errorMessage').css('visibility', 'visible')
        console.log(error)
    }

    function hideError() {
        $('#errorMessage').css('visibility', 'hidden')
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
        $('#noDataOverlayGoals1').css('display', 'block')
        $('#noDataOverlayGoals2').css('display', 'block')
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
        Plotly.newPlot(goalsGraph1, [], layout)
        Plotly.newPlot(goalsGraph2, [], layout)
    }

    function hideNoDataGoals() {
        $('#noDataOverlayGoals1').css('display', 'none')
        $('#noDataOverlayGoals2').css('display', 'none')
    }

    function fastClear(selectElement) {
        let selectObj = selectElement[0]
        let selectParentNode = selectObj.parentNode
        let newSelectObj = selectObj.cloneNode(false)
        selectParentNode.replaceChild(newSelectObj, selectObj)
        return newSelectObj
    }

    setInterval(() => {
        let currentText = $('#loadingText').html()
        let newText
        if (currentText !== 'Loading . . . .') {
            newText = currentText + ' .'
        } else {
            newText = 'Loading .'
        }
        $('#loadingText').html(newText)
    }, 500)
})