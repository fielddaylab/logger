$(document).ready((event) => {
    //$('.js-example-basic-single').select2() //initialize select boxes
    //$('#sessionSelectAll').select2({ disabled: true })
    let graphLeft = $('#graphLeft')[0]
    let graphRight = $('#graphRight')[0]
    let goalsGraph1 = $('#goalsGraph1')[0]
    let goalsGraph2 = $('#goalsGraph2')[0]

    let graphLeftAll = $('#graphLeftAll')[0]
    let graphRightAll = $('#graphRightAll')[0]
    let histogramAll1 = $('#goalsGraph1All')[0]
    let histogramAll2 = $('#goalsGraph2All')[0]
    let histogramAll3 = $('#goalsGraph3All')[0]

    let totalSessions
    let currentSessions = []
    let bigData
    
    $(document).on('change', '#gameSelect', (event) => {
        console.time('gameSelect')
        event.preventDefault()
        on()
        $('#gameIDForm').val($('#gameSelect').val())
        getAllData(false, true)
    })

    $(document).on('change', '#sessionSelect', (event) => {
        getSingleData()
    })

    $(document).on('submit', '#filterForm', (event) => {
        console.time('filterForm')
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
            }),
            $.get('responsePage.php', { 'isAll': true, 'isHistogram': true, 'gameID': $('#gameSelect').val(), 'isAggregate': true, 'minMoves': $('#minMoves').val(), 'minQuestions': $('#minQuestions').val(),
                    'minLevels': $('#minLevels').val(), 'startDate': $('#startDate').val(), 'endDate': $('#endDate').val(), 'maxSessions': $('#maxSessions').val()}, (data) => {
                drawWavesHistograms(data)
            }, 'json').error((jqXHR, textStatus, errorThrown) => {
                $('#filterModal').modal('hide')
                off()
                showError(jqXHR.responseText)
            })
            ) // end of promises.push(

            $.when.apply($, promises).then(() => {
                console.timeEnd('filterForm')
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

    function getAllData(isFiltered, isFirstTime = false) {
        let parameters = {
            'gameID': $('#gameSelect').val(),
            'isFiltered': isFiltered
        }
        if (isFiltered) {
            parameters['minLevels'] = $('#minMoves').val()
            parameters['minQuestions'] = $('#minQuestions').val()
            parameters['minLevels'] = $('#minLevels').val()
            parameters['startDate'] = $('#startDate').val()
            parameters['endDate'] = $('#endDate').val()
        }
        $.get('responsePage.php', parameters, (data, status, jqXHR) => {
            $('#scoreDisplayAll').html(data.questionsTotal.totalNumCorrect + ' / ' + data.questionsTotal.totalNumQuestions + ' (' + 
                (100 * data.questionsTotal.totalNumCorrect / data.questionsTotal.totalNumQuestions).toFixed(1) + '%)')
            if (data.levels !== null) {
                if (isFirstTime) {
                    fastClear($('#levelSelect'))
                    totalSessions = data.numSessions
                    // Get default dates from first (that isn't the epoch date) and last times
                    let startDate = new Date(data.sessionsAndTimes.times[data.sessionsAndTimes.times.lastIndexOf('0000-00-00 00:00:00')+1].replace(/-/g, "/"))
                    let startdd = startDate.getDate()
                    let startmm = startDate.getMonth() + 1
                    let startyyyy = startDate.getFullYear()
                    if (startdd < 10) { startdd = '0' + startdd }
                    if (startmm < 10) { startmm = '0' + startmm }

                    let endDate = new Date(data.sessionsAndTimes.times[data.sessionsAndTimes.times.length-1].replace(/-/g, "/"))
                    let enddd = endDate.getDate()
                    let endmm = endDate.getMonth() + 1
                    let endyyyy = endDate.getFullYear()
                    if (enddd < 10) { enddd = '0' + enddd }
                    if (endmm < 10) { endmm = '0' + endmm }

                    $('#startDate').val(startyyyy+'-'+startmm+'-'+startdd)
                    $('#endDate').val(endyyyy+'-'+endmm+'-'+enddd)

                    let options = []
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
                } 
                currentSessions = data.sessionsAndTimes.sessions
                let numSessionsToDisplay = data.numSessions
                $('#sessions').text('Showing ' + numSessionsToDisplay + ' of ' + totalSessions + ' available sessions')

                let options = []
                fastClear($('#sessionSelect'))

                for (let i = 0; i < data.sessionsAndTimes.sessions.length; i++) {
                    let newOpt = document.createElement('option')
                    newOpt.value = data.sessionsAndTimes.sessions[i]
                    newOpt.text = i + ' | ' + data.sessionsAndTimes.sessions[i] + ' | ' + data.sessionsAndTimes.times[i]
                    options.push(newOpt)
                }
                $('#sessionSelect').append(options)
                $('#sessionSelect').append($('<option>18020410454796070</option>').attr({'value':18020410454796070}))
                $('#sessionSelect').val('18020410454796070') // the most interesting session

                // All page basic info
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
                for (let i = 0; i < data.basicInfoAll.times.length; i++) {
                    // append times
                    $('#timesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.times[i].toFixed(2)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append moves
                    $('#movesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.numMoves[i].toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    
                    // append types
                    $('#typesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.moveTypeChanges[i].toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append std devs
                    $('#stdDevsAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.knobStdDevs[i].toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append knob amounts
                    $('#amtsAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgMaxMin[i].toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append knob total amounts
                    $('#amtsTotalAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalMaxMin[i].toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                }

                $('#timesAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#timesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalTime.toFixed(2)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#timesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgTime.toFixed(2)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#movesAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#movesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalMoves.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#movesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgMoves.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#typesAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#typesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalMoveChanges.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#typesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgMoveChanges.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#amtsAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#amtsAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalKnobAvgs.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#amtsAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgKnobAvgs.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#amtsTotalAll').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#amtsTotalAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalKnobTotals.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#amtsTotalAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgKnobTotals.toFixed(2)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                off();
                //selectGameAll(event)
                //getSingleData()
            } else {
                off()
                hideError()
            }
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError(errorThrown)
        })
    }

    function getSingleData() {
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'isFiltered': false, 'sessionID': $('#sessionSelect').val() }, (data, status, jqXHR) => {
            $("#scoreDisplay").html(data.questionsSingle.numCorrect + " / " + data.questionsSingle.numQuestions)
            
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError(jqXHR.responseText)
        })
    }

    function selectSession(event, shouldHideOverlay = true) {
        console.time('selectSession')
        if (event) event.preventDefault()
        on()
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val() }, (data, status, jqXHR) => {
            $("#scoreDisplay").html(data.numCorrect + " / " + data.numQuestions)
            hideError()
            $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val(), 'level': $('#levelSelect').val() }, (data, status, jqXHR) => {
                if ($('#gameSelect').val() === "WAVES") {
                    let dataObj = {events:data.events, data:data.event_data, times:data.times}
                    drawWavesChart(dataObj)
                    getWavesData(shouldHideOverlay)
                }
                if (shouldHideOverlay) {
                    off()
                }
                console.timeEnd('selectSession')
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
        console.time('selectGameAll')
        if (event) event.preventDefault()
        on()
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'isAll': true }, (data, status, jqXHR) => {
            $('#scoreDisplayAll').html(data.totalNumCorrect + ' / ' + data.totalNumQuestions + ' (' + (100*data.totalNumCorrect/data.totalNumQuestions).toFixed(1) + '%)')
            if ($('#gameSelect').val() === "WAVES") {
                $.get('responsePage.php', { 'isAll': true, 'isHistogram': true, 'gameID': $('#gameSelect').val(), 'isAggregate': true }, (data) => {
                drawWavesHistograms(data)
                console.timeEnd('selectGameAll')
            }, 'json').error((jqXHR, textStatus, errorThrown) => {
                $('#filterModal').modal('hide')
                off()
                showError(jqXHR.responseText)
            })
                getWavesDataAll()
            }
            hideError()
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError(jqXHR.responseText)
        })
    }

    $(document).on('change', '#levelSelect', (event) => {
        console.time('levelSelect')
        event.preventDefault()
        if ($('#levelSelect').val() !== $('#levelSelectAll').val()) {
            on()
            //$('#levelSelectAll').val($('#levelSelect').val()).trigger('change')
            $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val(), 'level': $('#levelSelect').val()}, (data, status, jqXHR) => {
                if ($('#gameSelect').val() === "WAVES") {
                    let dataObj = {events:data.events, data:data.event_data, times:data.times}
                    drawWavesChart(dataObj)
                    getWavesData(true, false)
                }
                console.timeEnd('levelSelect')
                off()
                hideError()
            }, 'json').error((jqXHR, textStatus, errorThrown) => {
                off()
                showError(jqXHR.responseText)
            })
        }
    })

    function getWavesData(shouldHideOverlay = true, shouldClearLists = true) {
        on()
        console.time('getWavesData')
        if (shouldClearLists)
            fastClear($('#basicFeatures'))
        $.get('responsePage.php', { 'isBasicFeatures': true, 'gameID': $('#gameSelect').val(), 'sessionID': $('#sessionSelect').val()}, (data, status, jqXHR) => {
            if ($('#gameSelect').val() === "WAVES" && shouldClearLists) {
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
            }
            console.timeEnd('getWavesData')
            drawWavesGoals(shouldHideOverlay)
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
        console.time('getWavesDataAll')
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
            console.timeEnd('getWavesDataAll')
            getWavesTableData()
            off()
            //drawWavesHistogramsAll()
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError(jqXHR.responseText)
        })
    }

    function getWavesTableData() {
        console.time('getWavesTableData')
        // $.get...
        $('#tableAllBody tr').each((i, ival) => {
            $(ival).find('td').each((j, jval) => {
                $(jval).html(i+', '+j)
            })
        })
        console.timeEnd('getWavesTableData')
    }

    function drawWavesHistograms(data) {
        console.time('drawWavesHistograms')
        $('#goalsDiv1All').html('Histogram 1: Questions answered')
        let trace = {
            x: data.numsQuestions,
            type: 'histogram'
        }
        let layout1 = {
            margin: { t: 35 },
            plot_bgcolor: '#F6F6F3',
            paper_bgcolor: '#F6F6F3',
            height: 200,
            xaxis: {
                autotick: false,
                tick0: 0,
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
        Plotly.newPlot(histogramAll1, [trace], layout1)

        $('#goalsDiv2All').html('Histogram 2: Total number of moves')
        $('#goalsDiv2All').css('display', 'block')
        $('#goalsGraph2All').css('display', 'block')
        let trace2 = {
            x: data.numMoves,
            type: 'histogram'
        }
        let layout2 = {
            margin: { t: 35 },
            height: 200,
            plot_bgcolor: '#F6F6F3',
            paper_bgcolor: '#F6F6F3',
            xaxis: {
                title: 'Total number of moves',
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

        Plotly.newPlot(histogramAll2, [trace2], layout2)

        $('#goalsDiv3All').html('Histogram 3: Number of levels completed')
        $('#goalsDiv3All').css('display', 'block')
        $('#goalsGraph3All').css('display', 'block')
        let trace3 = {
            x: data.numLevels,
            type: 'histogram'
        }
        let layout3 = {
            margin: { t: 35 },
            height: 200,
            plot_bgcolor: '#F6F6F3',
            paper_bgcolor: '#F6F6F3',
            xaxis: {
                title: 'Number of levels completed',
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

        Plotly.newPlot(histogramAll3, [trace3], layout3)
        console.timeEnd('drawWavesHistograms')
    }

    function drawWavesGoals(shouldHideOverlay = true) {
        // Goals stuff
        console.time('drawWavesGoals')
        on();
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val(), 'isGoals': true, 'sessionID': $('#sessionSelect').val(), 'level': $('#levelSelect').val() }, (data, status, jqXHR) => {
            $('#goalsDiv1').html('Goal 1: Completing the challenge')

            let goalSlope1 = data.goalSlope1
            let goalSlope2 = data.goalSlope2

            let distanceTrace1 = {
                x: data.moveNumbers,
                y: data.absDistanceToGoal1,
                xaxis: 'x1',
                yaxis: 'y2',
                line: {color: 'green'},
                name: 'Distance to goal'
            }
            let closenessTrace1 = {
                x: data.moveNumbers,
                y: data.distanceToGoal1,
                line: {color: 'orange'},
                name: 'Net good moves',
            }
            let layout = {
                margin: { t: 35 },
                title: `Level ${$('#levelSelect').val()}`,
                plot_bgcolor: '#F6F6F3',
                paper_bgcolor: '#F6F6F3',
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
                        color: '#7f7f7f',
                    },
                    rangemode: 'tozero'
                },
                yaxis2: {
                    title: 'Dist. to goal',
                    titlefont: {
                        family: 'Courier New, monospace',
                        size: 12,
                        color: 'green'
                    },
                    side: 'right',
                    overlaying: 'y',
                    rangemode: 'tozero'
                },
                showlegend: true,
                legend: {
                    x: 1.1,
                    y: 1
                }
            }
            let graphData1 = [closenessTrace1, distanceTrace1]
    
            if (graphData1[0].x.length > 0 && graphData1[0].y.length > 0 && 
                graphData1[1].x.length > 0 && graphData1[1].y.length > 0) {
                $('#slopeDiv1').html('Net good moves slope: ' + goalSlope1.toFixed(2))
                Plotly.newPlot(goalsGraph1, graphData1, layout)
            }
    
            $('#goalsDiv2').html('Goal 2: Maxing slider values')
            $('#goalsDiv2').css('display', 'block')
            $('#goalsGraph2').css('display', 'block')
    
            let distanceTrace2 = {
                x: data.moveNumbers,
                y: data.absDistanceToGoal2,
                xaxis: 'x1',
                yaxis: 'y2',
                overlaying: 'y',
                line: {color: 'green'},
                name: 'Distance to goal'
            }
            let closenessTrace2 = {
                x: data.moveNumbers,
                y: data.distanceToGoal2,
                line: {color: 'orange'},
                name: 'Net good moves'
            }
            let graphData2 = [closenessTrace2, distanceTrace2]
    
            if (graphData2[0].x.length > 0 && graphData2[0].y.length > 0 && 
                graphData2[1].x.length > 0 && graphData2[1].y.length > 0) {
                $('#slopeDiv2').html('Net good moves slope: ' + goalSlope2.toFixed(2))
                Plotly.newPlot(goalsGraph2, graphData2, layout)
            }
            hideError()
            console.timeEnd('drawWavesGoals')
            if (shouldHideOverlay)
                off();
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError(jqXHR.responseText)
        })
    }

    function drawWavesChart(inData) {
        console.time('drawWavesChart')
        let xSucceed = []
        let xAmpLeft = [], xAmpRight = []
        let xFreqLeft = [], xFreqRight = []
        let xOffLeft = [], xOffRight = []
        let yAmpLeft = [], yAmpRight = []
        let yFreqLeft = [], yFreqRight = []
        let yOffLeft = [], yOffRight = []
        let hasLeftData = false, hasRightData = false, hasSuccessState = false
        let ampLeftNum = [], ampRightNum = []
        let freqLeftNum = [], freqRightNum = []
        let offLeftNum = [], offRightNum = []
        let moveCounter = 0
        if (inData.data !== null) {
            for (let i = 0; i < inData.data.length; i++) {
                let jsonData = JSON.parse(inData.data[i])
                if (jsonData.wave !== undefined) {
                    if (jsonData.wave === 'left') {
                        hasLeftData = true
                        if (jsonData.slider === 'AMPLITUDE') {
                            xAmpLeft.push(inData.times[i])
                            yAmpLeft.push(jsonData.end_val)
                            ampLeftNum.push('Move ' + moveCounter)
                        } else if (jsonData.slider === 'WAVELENGTH') { 
                            xFreqLeft.push(inData.times[i])
                            yFreqLeft.push(jsonData.end_val)
                            freqLeftNum.push('Move ' + moveCounter)
                        } else if (jsonData.slider === 'OFFSET') {
                            xOffLeft.push(inData.times[i])
                            yOffLeft.push(jsonData.end_val)
                            offLeftNum.push('Move ' + moveCounter)
                        }
                        moveCounter++
                    } else if (jsonData.wave === 'right') {
                        hasRightData = true
                        if (jsonData.slider === 'AMPLITUDE') {
                            xAmpRight.push(inData.times[i])
                            yAmpRight.push(jsonData.end_val)
                            ampRightNum.push('Move ' + moveCounter)
                        } else if (jsonData.slider === 'WAVELENGTH') { 
                            xFreqRight.push(inData.times[i])
                            yFreqRight.push(jsonData.end_val)
                            freqRightNum.push('Move ' + moveCounter)
                        } else if (jsonData.slider === 'OFFSET') {
                            xOffRight.push(inData.times[i])
                            yOffRight.push(jsonData.end_val)
                            offRightNum.push('Move ' + moveCounter)
                        }
                        moveCounter++
                    }
                }
                if (inData.events[i] === 'SUCCEED') {
                    hasSuccessState = true
                    xSucceed.push(inData.times[i])
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
        let legendTrace = {
            x: [null],
            y: [null],
            line: {color: '#9467bd'},
            name: 'Success state',
            mode: 'lines'
        }
        let wavesDataLeft = [ampTraceLeft, freqTraceLeft, offTraceLeft]
        let wavesDataRight = [ampTraceRight, freqTraceRight, offTraceRight]

        let layoutLeft = {
            margin: { t: 35 },
            title: 'Left Sliders',
            showlegend: true,
            shapes: []
        }
        let layoutRight = {
            margin: { t: 35 },
            title: 'Right Sliders',
            showlegend: true,
            shapes: []
        }
        if (hasLeftData) {
            if (hasSuccessState) {
                wavesDataLeft.push(legendTrace)
            }
            xSucceed.forEach((val, index) => {
                layoutLeft.shapes.push({
                    type: 'line',
                    xref: 'x',
                    yref: 'paper',
                    x0: val,
                    x1: val,
                    y0: 0,
                    y1: 1,
                    line: {
                        color: '#9467bd'
                    }
                })
            })
        }
        if (hasRightData) {
            if (hasSuccessState) {
                wavesDataRight.push(legendTrace)
            }
            xSucceed.forEach((val, index) => {
                layoutRight.shapes.push({
                    type: 'line',
                    xref: 'x',
                    yref: 'paper',
                    x0: val,
                    x1: val,
                    y0: 0,
                    y1: 1,
                    line: {
                        color: '#9467bd'
                    }
                })
            })
        }

        Plotly.newPlot(graphLeft, wavesDataLeft, layoutLeft)
        Plotly.newPlot(graphRight, wavesDataRight, layoutRight)
        console.timeEnd('drawWavesChart')
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
            plot_bgcolor: '#F6F6F3',
            paper_bgcolor: '#F6F6F3',
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