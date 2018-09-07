$(document).ready((event) => {
    //$('.js-example-basic-single').select2() //initialize select boxes
    //$('#sessionSelectAll').select2({ disabled: true })
    new ClipboardJS('#copyBtn', {
        text: (trigger) => {
            return $('#sessionSelect').val()
        }
    })
    let graphLeft = $('#graphLeft')[0]
    let graphRight = $('#graphRight')[0]
    let goalsGraph1 = $('#goalsGraph1')[0]
    let goalsGraph2 = $('#goalsGraph2')[0]

    let graphLeftAll = $('#graphLeftAll')[0]
    let graphRightAll = $('#graphRightAll')[0]
    let histogramAll1 = $('#goalsGraph1All')[0]
    let histogramAll2 = $('#goalsGraph2All')[0]
    let histogramAll3 = $('#goalsGraph3All')[0]
    let clusterGraph = $('#goalsGraph4All')[0]

    let endDate = new Date()
    let enddd = endDate.getDate()
    let endmm = endDate.getMonth() + 1
    let endyyyy = endDate.getFullYear()
    if (enddd < 10) { enddd = '0' + enddd }
    if (endmm < 10) { endmm = '0' + endmm }
    $('#endDate').val(endyyyy+'-'+endmm+'-'+enddd)

    let lvls = [1, 3, 5, 7, 11, 13, 15, 19, 21, 23, 25, 27, 31]
    lvls.forEach((value, index, arr) => {
        let newRow = $(`<tr class=rowLvl>`)
        newRow.append(
            `
            <th scope="row">% good moves lvl ${value}</th>
            <td style="border-left-width:4px; "></td>
            <td></td>
            <td></td>
            <td scope="col" style="border-right-width:4px;"></td>
            <td scope="col" style="border-left-width:4px; "></td>
            <td></td>
            <td style="border-right-width:4px;"></td>
            <td style="border-left-width:4px; "></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="border-right-width:4px;"></td>
            <td style="border-left-width:4px; "></td>
            <td style="border-left-width:4px; "></td>
            `
        )
        $('#predictTableBody').append(newRow)
    })
    $('#predictTableBody .rowLvl').each((i, value) => {
        $(value).children('td').each((j, jval) => {
            if (i+1 > j) {
                $(jval).css('background-color', 'rgb(235,235,228)')
                $(jval).addClass('disabled-cell')
            }
        })
    })
    lvls.forEach((value, index, arr) => {
        let newRow = $(`<tr class=rowLvl>`)
        newRow.append(
            `
            <th scope="row">% good moves lvl ${value}</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            `
        )
        $('#numLevelsBody').append(newRow)
    })
    $('#numLevelsBody .rowLvl').each((i, value) => {
        $(value).children('td').each((j, jval) => {
            if (i+1 > j) {
                $(jval).css('background-color', 'rgb(235,235,228)')
                $(jval).addClass('disabled-cell')
            }
        })
    })

    let totalSessions
    let errorTracker = 0
    window.onerror = () => {
        errorTracker = 0
        off()
        $('#errorMessage').css('visibility', 'visible').html('A JavaScript error has occurred. See console for details.') 
    }
    
    $(document).on('change', '#gameSelect', (event) => {
        // console.time('gameSelect')
        event.preventDefault()
        $('#gameIDForm').val($('#gameSelect').val())
        getAllData(true)
    })

    $(document).on('change', '#sessionSelect', (event) => {
        on()
        getSingleData(true, false)
        getSingleData(false, true)
    })

    $(document).on('submit', '#filterForm', (event) => {
        // console.time('filterForm')
        event.preventDefault()
        if ($('#gameSelect').val() !== 'empty') {
            $('#filterModal').modal('hide')
            //on()
            if ($('#sessionInput').val() !== '') {
                if ($(`#sessionSelect option[value="${$('#sessionInput').val()}"]`).length === 0) {
                    let newOpt = document.createElement('option')
                    newOpt.value = $('#sessionInput').val()
                    $('#sessionSelect').append(newOpt)
                }
                $('#sessionSelect').val($('#sessionInput').val())
                getSingleData(true, false)
                getSingleData(false, true)
            } else {
                getAllData(false)
            }
        } else {
            $('#formError').show()
            //$('#filterModal').modal('hide')
            $('#formError').html('Please select a game before filtering sessions.')
        }
    })

    $(document).on('hide.bs.modal', '#filterModal', (event) => {
        $('#formError').hide()
    })

    $(document).on('input', '#sessionInput', (event) => {
        if ($('#sessionInput').val() !== '') {
            // Disable the other filters to avoid confusion
            $('#minMoves').prop('disabled', true)
            $('#minQuestions').prop('disabled', true)
            $('#minLevels').prop('disabled', true)
            $('#startDate').prop('disabled', true)
            $('#endDate').prop('disabled', true)
            $('#maxRows').prop('disabled', true)
            $('#maxLevels').prop('disabled', true)
            $('#maxLogReg').prop('disabled', true)
        } else {
            $('#minMoves').prop('disabled', false)
            $('#minQuestions').prop('disabled', false)
            $('#minLevels').prop('disabled', false)
            $('#startDate').prop('disabled', false)
            $('#endDate').prop('disabled', false)
            $('#maxRows').prop('disabled', false)
            $('#maxLevels').prop('disabled', false)
            $('#maxLogReg').prop('disabled', false)
        }
    })

    $(document).on('change', '#levelSelect', (event) => {
        // console.time('levelSelect')
        event.preventDefault()
        if ($('#levelSelect').val() !== $('#levelSelectAll').val()) {
            on()
            if ($('#gameSelect').val() === "WAVES") {
                getSingleData(false, true)
            }
            // console.timeEnd('levelSelect')
            off()
            hideError()
        }
    })

    function getAllData(isFirstTime = false) {
        let parametersBasic = {
            'gameID': $('#gameSelect').val(),
            'maxRows': $('#maxRows').val(),
            'minMoves': $('#minMoves').val(),
            'minQuestions': $('#minQuestions').val(),
            'minLevels': $('#minLevels').val(),
            'maxLevels': $('#maxLevels').val(),
            'startDate': $('#startDate').val(),
            'endDate': $('#endDate').val(),
            'numMovesPerChallenge': $('#numMovesPerChallenge').prop('checked') ? true : undefined,
            'knobAvgs': $('#knobAvgs').prop('checked') ? true : undefined,
            'levelTimes': $('#levelTimes').prop('checked') ? true : undefined,
            'percentGoodMovesAll': $('#percentGoodMovesAll').prop('checked') ? true : undefined,
            'moveTypeChangesPerLevel': $('#moveTypeChangesPerLevel').prop('checked') ? true : undefined,
            'knobStdDevs': $('#knobStdDevs').prop('checked') ? true : undefined,
            'knobTotalAmts': $('#knobTotalAmts').prop('checked') ? true : undefined,
        }
        let numSimultaneous = navigator.hardwareConcurrency
        let queue = new networkQueue(numSimultaneous)

        let numCols = $('#tableAllBody').find('tr:first td').length
        if (true) {
            for (let i = 0; i < numCols; i++) {
                let columnElements = $(`#tableAllBody tr td:nth-child(${i+2})`)
                let column
                switch (i) {
                    case 0:
                        column = 'q00'; break
                    case 1:
                        column = 'q01'; break
                    case 2:
                        column = 'q02'; break
                    case 3:
                        column = 'q03'; break
                    case 4:
                        column = 'q10'; break
                    case 5:
                        column = 'q11'; break
                    case 6:
                        column = 'q12'; break
                    case 7:
                        column = 'q13'; break
                    case 8:
                        column = 'q20'; break
                    case 9:
                        column = 'q21'; break
                    case 10:
                        column = 'q22'; break
                    case 11:
                        column = 'q23'; break
                    case 12:
                        column = 'q30'; break
                    case 13:
                        column = 'q31'; break
                    case 14:
                        column = 'q32'; break
                    case 15:
                        column = 'q33'; break
                }

                let parametersQues = {
                    'gameID': $('#gameSelect').val(),
                    'maxRows': $('#maxRows').val(),
                    'minMoves': $('#minMoves').val(),
                    'minQuestions': 1,
                    'minLevels': $('#minLevels').val(),
                    'maxLevels': $('#maxLevels').val(),
                    'startDate': $('#startDate').val(),
                    'endDate': $('#endDate').val()
                }
                parametersQues['column'] = column
                let loadTimer, backgroundColors = [], borderBottoms = [], borderTops = []

                switch (column) {
                    case 'q00':
                    case 'q11':
                    case 'q20':
                    case 'q31':
                        columnElements.each((j, jval) => {
                            // Color the correct answer for each question
                            $(jval).addClass('success')
                        })
                        break
                }
                columnElements.each((index, value) => {
                    backgroundColors.push($(value).css('background-color'))
                    borderBottoms.push($(value).css('border-bottom'))
                    borderTops.push($(value).css('border-top'))
                    $(value).css({
                        'background-color': 'rgba(0, 0, 0, 0.15)',
                        'border-top': 'none',
                        'border-bottom': 'none'
                    })
                    if (index === 2) {
                        $(value).addClass('colLoadingText')
                        let rand = Math.random()
                        if (rand < 0.333) {
                            $(value).text('.')
                        } else if (rand < 0.666) {
                            $(value).text('. .')
                        } else {
                            $(value).text('. . .')
                        }
                        $(value).css({
                            'vertical-align': 'middle',
                            'text-align': 'center',
                            'font-size': '16px'
                        })
                        loadTimer = setInterval(() => {
                            let currentText = $(value).html()
                            let newText
                            if (currentText !== '. . . .') {
                                newText = currentText + ' .'
                            } else {
                                newText = '.'
                            }
                            $(value).html(newText)
                        }, 400 + Math.random() * 200)
                    }
                })

                let callbackFunc = (data) => {
                    clearInterval(loadTimer)
                    // Store the computation values for retrieval when the link is clicked
                    // localStorage.setItem(`regressionVars${column}`, JSON.stringify(data.regressionVars))
                    // localStorage.setItem(`equationVars${column}`, JSON.stringify(data.equationVars))
                    columnElements.each((j, jval) => {
                        $(jval).css({
                            'vertical-align': 'middle',
                            'background-color': backgroundColors[j],
                            'border-top': borderTops[j],
                            'border-bottom': borderBottoms[j]
                        })
                        let innerText = $('<div>')
                        if (typeof data.pValues[j] === 'number') {
                            innerText.html(data.pValues[j].toFixed(5))
                            if (data.pValues[j] < 0.05) {
                                $(innerText).css('background-color', '#82e072')
                            }
                        } else {
                            innerText.html('No data')
                        }
                        $(jval).html(innerText)
                        $(jval).wrapInner(`<a href="correlationGraph.html?gameID=${$('#gameSelect').val()}&row=${j}&col=${i}" target="_blank"></a>`)
        
                        $(innerText).css({'color': 'black', 'text-align': 'center', 'font': '14px "Open Sans", sans-serif'})
                    })
                    off()
                }

                req = {
                    parameters: parametersQues,
                    callback: callbackFunc
                }
                queue.push(req)
            }
        }

        numCols = $('#predictTableBody').find('tr:first td').length
        if (true) {
            for (let i = 0; i < numCols; i++) {
                let parametersChallenge = {
                    'gameID': $('#gameSelect').val(),
                    'maxRows': $('#maxRows').val(),
                    'minMoves': $('#minMoves').val(),
                    'minQuestions': $('#minQuestions').val(),
                    'minLevels': $('#minLevels').val(),
                    'maxLevels': $('#maxLevels').val(),
                    'startDate': $('#startDate').val(),
                    'endDate': $('#endDate').val()
                }
                let columnElements = $(`#predictTableBody tr td:nth-child(${i+2})`).not('.disabled-cell')
                let column
                switch (i) {
                    case 0:
                        column = 'lvl1'; break
                    case 1:
                        column = 'lvl3'; break
                    case 2:
                        column = 'lvl5'; break
                    case 3:
                        column = 'lvl7'; break
                    case 4:
                        column = 'lvl11'; break
                    case 5:
                        column = 'lvl13'; break
                    case 6:
                        column = 'lvl15'; break
                    case 7:
                        column = 'lvl19'; break
                    case 8:
                        column = 'lvl21'; break
                    case 9:
                        column = 'lvl23'; break
                    case 10:
                        column = 'lvl25'; break
                    case 11:
                        column = 'lvl27'; break
                    case 12:
                        column = 'lvl31'; break
                    case 13:
                        column = 'lvl33'; break
                }

                parametersChallenge['predictTable'] = true
                parametersChallenge['predictColumn'] = column
                let loadTimer, backgroundColors = [], borderBottoms = [], borderTops = []
                columnElements.each((index, value) => {
                    backgroundColors.push($(value).css('background-color'))
                    borderBottoms.push($(value).css('border-bottom'))
                    borderTops.push($(value).css('border-top'))
                    $(value).css({
                        'background-color': 'rgba(0, 0, 0, 0.15)',
                        'border-top': 'none',
                        'border-bottom': 'none'
                    })
                    if (index === 2) {
                        $(value).addClass('colLoadingText')
                        let rand = Math.random()
                        if (rand < 0.333) {
                            $(value).text('.')
                        } else if (rand < 0.666) {
                            $(value).text('. .')
                        } else {
                            $(value).text('. . .')
                        }
                        $(value).css({
                            'vertical-align': 'middle',
                            'text-align': 'center',
                            'font-size': '16px'
                        })
                        loadTimer = setInterval(() => {
                            let currentText = $(value).html()
                            let newText
                            if (currentText !== '. . . .') {
                                newText = currentText + ' .'
                            } else {
                                newText = '.'
                            }
                            $(value).html(newText)
                        }, 400 + Math.random() * 200)
                    }
                })

                let callbackFunc = (data) => {
                    clearInterval(loadTimer)
                    columnElements.each((j, jval) => {
                        $(jval).css({
                            'vertical-align': 'middle',
                            'background-color': backgroundColors[j],
                            'border-top': borderTops[j],
                            'border-bottom': borderBottoms[j]
                        })
                        let innerText = $('<div>')
                        if (data && data.pValues) {
                            if (typeof data.pValues[j] === 'number') {
                                innerText.html(data.pValues[j].toFixed(5))
                                if (data.pValues[j] < 0.05) {
                                    $(innerText).css('background-color', '#82e072')
                                }
                            } else {
                                innerText.html('No data')
                            }
                        } else {
                            innerText.html('No data')
                        }
                        $(jval).html(innerText)
        
                        $(innerText).css({'color': 'black', 'text-align': 'center', 'font': '14px "Open Sans", sans-serif'})
                    })
                    off()
                }

                req = {
                    parameters: parametersChallenge,
                    callback: callbackFunc
                }
                queue.push(req)
            }
        }

        numCols = $('#numLevelsBody').find('tr:first td').length
        if (true) {
            for (let i = 0; i < numCols; i++) {
                let columnElements = $(`#numLevelsBody tr td:nth-child(${i+2})`).not('.disabled-cell')
                let column

                switch (i) {
                    case 0:
                        column = 'lvl1'; break
                    case 1:
                        column = 'lvl3'; break
                    case 2:
                        column = 'lvl5'; break
                    case 3:
                        column = 'lvl7'; break
                    case 4:
                        column = 'lvl11'; break
                    case 5:
                        column = 'lvl13'; break
                    case 6:
                        column = 'lvl15'; break
                    case 7:
                        column = 'lvl19'; break
                    case 8:
                        column = 'lvl21'; break
                    case 9:
                        column = 'lvl23'; break
                    case 10:
                        column = 'lvl25'; break
                    case 11:
                        column = 'lvl27'; break
                    case 12:
                        column = 'lvl31'; break
                    case 13:
                        column = 'lvl33'; break
                }

                let parametersLevels = {
                    'gameID': $('#gameSelect').val(),
                    'maxRows': $('#maxRows').val(),
                    'minMoves': $('#minMoves').val(),
                    'minQuestions': $('#minQuestions').val(),
                    'minLevels': $('#minLevels').val(),
                    'maxLevels': $('#maxLevels').val(),
                    'startDate': $('#startDate').val(),
                    'endDate': $('#endDate').val(),
                    'numLevelsTable': true,
                    'numLevelsColumn': column
                }

                let loadTimer, backgroundColors = [], borderBottoms = [], borderTops = []

                columnElements.each((index, value) => {
                    backgroundColors.push($(value).css('background-color'))
                    borderBottoms.push($(value).css('border-bottom'))
                    borderTops.push($(value).css('border-top'))
                    $(value).css({
                        'background-color': 'rgba(0, 0, 0, 0.15)',
                        'border-top': 'none',
                        'border-bottom': 'none'
                    })
                    if (index === 2) {
                        $(value).addClass('colLoadingText')
                        let rand = Math.random()
                        if (rand < 0.333) {
                            $(value).text('.')
                        } else if (rand < 0.666) {
                            $(value).text('. .')
                        } else {
                            $(value).text('. . .')
                        }
                        $(value).css({
                            'vertical-align': 'middle',
                            'text-align': 'center',
                            'font-size': '16px'
                        })
                        loadTimer = setInterval(() => {
                            let currentText = $(value).html()
                            let newText
                            if (currentText !== '. . . .') {
                                newText = currentText + ' .'
                            } else {
                                newText = '.'
                            }
                            $(value).html(newText)
                        }, 400 + Math.random() * 200)
                    }
                })
                let callbackFunc = (data) => {
                    clearInterval(loadTimer)
                    columnElements.each((j, jval) => {
                        $(jval).css({
                            'vertical-align': 'middle',
                            'background-color': backgroundColors[j],
                            'border-top': borderTops[j],
                            'border-bottom': borderBottoms[j]
                        })
                        let innerText = $('<div>')
                        if (data && data.pValues) {
                            if (typeof data.pValues[j] === 'number') {
                                innerText.html(data.pValues[j].toFixed(5))
                                if (data.pValues[j] < 0.05) {
                                    $(innerText).css('background-color', '#82e072')
                                }
                            } else {
                                innerText.html('No data')
                            }
                        } else {
                            innerText.html('No data')
                        }

                        $(jval).html(innerText)
        
                        $(innerText).css({'color': 'black', 'text-align': 'center', 'font': '14px "Open Sans", sans-serif'})
                    })
                    off()
                }

                req = {
                    parameters: parametersLevels,
                    callback: callbackFunc
                }
                queue.push(req)
            }
        }

        $.get('responsePage.php', parametersBasic, (data, status, jqXHR) => {
            $('#scoreDisplayAll').html(data.questionsTotal.totalNumCorrect + ' / ' + data.questionsTotal.totalNumQuestions + ' (' + 
                (100 * data.questionsTotal.totalNumCorrect / data.questionsTotal.totalNumQuestions).toFixed(1) + '%)')
            if (data.levels !== null) {
                let numSessionsToDisplay = data.numSessions
                totalSessions = data.totalNumSessions
                $('#sessions').text('Showing ' + numSessionsToDisplay + ' of ' + totalSessions + ' available sessions')

                let options = []
                fastClear($('#sessionSelect'))

                let sessions = data.sessionsAndTimes.sessions
                let times = data.sessionsAndTimes.times

                for (let i = 0; i < sessions.length; i++) {
                    let newOpt = document.createElement('option')
                    newOpt.value = sessions[i]
                    newOpt.text = i + ' | ' + sessions[i] + ' | ' + times[i]
                    options.push(newOpt)
                }
                $('#sessionSelect').append(options)
                if ($('#sessionSelect option[value="18020410454796070"]').length > 0) {
                    $('#sessionSelect').val('18020410454796070') // the most interesting session
                } else {
                    if ($('#sessionSelect option').length > 0) {
                        $('#sessionSelect').val($('#sessionSelect option:first').val())
                    } else {
                        showNoDataGoals()
                        showNoDataLeft()
                        showNoDataRight()
                        fastClear($('#basicFeatures'))
                        $('#scoreDisplay').html('- / -')
                    }
                }
                    
                if (isFirstTime) {
                    // Get basic info for the level
                    getSingleData(true, false)

                    // Get the levels and then information for a specific level (0)
                    fastClear($('#levelSelect'))

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
                    getSingleData(false, true)
                }
                // All page basic info
                fastClear($('#basicFeaturesAll'))
                let timesList = $('<ul></ul>').attr('id', 'timesAll').addClass('collapse').css('font-size', '18px')
                $('#basicFeaturesAll').append($(`<span><li>Times: <a href='#timesAll' data-toggle='collapse' id='timesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(timesList)
                    .on('hide.bs.collapse', () => {$('#timesCollapseBtnAll').html('[+]')})
                    .on('show.bs.collapse', () => {$('#timesCollapseBtnAll').html('[−]')}))
                let movesList = $('<ul></ul>').attr('id', 'movesAll').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Number of slider moves: <a href='#movesAll' data-toggle='collapse' id='movesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(movesList)
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

                for (let i = Object.keys(data.basicInfoAll.times)[0]; i <= Object.keys(data.basicInfoAll.times)[Object.keys(data.basicInfoAll.times).length-1]; i++) {
                    if (data.basicInfoAll.times[i] === 'NaN') continue;
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
                let dataHistogram = { 'numsQuestions': data.questionsAll.numsQuestions, 'numMoves': data.numMovesAll, 'numLevels': data.numLevelsAll, 'clusters': data.clusters }
                if ($('#sessionSelect option').length > 0) {
                    getSingleData(true, false) 
                } else if ($('#sessionSelect option').length === 0) {
                    $('#scoreDisplayAll').html('- / -')
                    fastClear($('#basicFeaturesAll'))
                    showNoDataHistograms()
                    off()
                }
                $('#percentCompleteRow').children('td').each((index, value) => {
                    if (typeof data.lvlsPercentComplete[index] === 'number') {
                        $(value).html(data.lvlsPercentComplete[index].toPrecision(4) + ' %')
                    } else {
                        $(value).html(data.lvlsPercentComplete[index] + ' %')
                    }
                })
                drawWavesHistograms(dataHistogram)
            } else {
                off()
                hideError()
            }
        }, 'json').fail((jqXHR, textStatus, errorThrown) => {
            off()
            console.log('Error triggered by getAllData')
            showError(errorThrown)
        })

        return queue
    }

    function getSingleData(shouldClearLists, shouldSendLevel) {
        let reqParams = { 
            'gameID': $('#gameSelect').val(), 
            'isFiltered': false, 
            'sessionID': $('#sessionSelect').val(),
            'maxRows': $('#maxRows').val()
        }
        if (shouldSendLevel) reqParams['level'] = $('#levelSelect').val()

        if (shouldClearLists)
            fastClear($('#basicFeatures'))
        $.get('responsePage.php', reqParams, (data, status, jqXHR) => {
            if ($('#gameSelect').val() === "WAVES" && shouldClearLists && !shouldSendLevel) {
                fastClear($('#basicFeatures'))
                $("#scoreDisplay").html(data.questionsSingle.numCorrect + " / " + data.questionsSingle.numQuestions)
                let timesList = $('<ul></ul>').attr('id', 'times').addClass('collapse').css('font-size', '18px')
                $('#basicFeatures').append($(`<span><li>Times: <a href='#times' data-toggle='collapse' id='timesCollapseBtn' class='collapseBtn'>[+]</a></li></span>`).append(timesList)
                    .on('hide.bs.collapse', () => {$('#timesCollapseBtn').html('[+]')})
                    .on('show.bs.collapse', () => {$('#timesCollapseBtn').html('[−]')}))
                let movesList = $('<ul></ul>').attr('id', 'moves').addClass('collapse').css({'font-size':'18px'})
                $('#basicFeatures').append($(`<span><li style='margin-top:5px'>Number of slider moves: <a href='#moves' data-toggle='collapse' id='movesCollapseBtn' class='collapseBtn'>[+]</a></li></span>`).append(movesList)
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

                for (let i = 0; i < data.basicInfoSingle.levelTimes.length; i++) {
                    // append times
                    $('#times').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.levelTimes[i]} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append moves
                    $('#moves').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.numMovesPerChallenge[i]}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                    
                    // append types
                    $('#types').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.moveTypeChangesPerLevel[i]}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append std devs
                    $('#stdDevs').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.knobStdDevs[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append knob amounts
                    $('#amts').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.knobAvgs[i].toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                    // append knob total amounts
                    $('#amtsTotal').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${(data.basicInfoSingle.knobTotalAmts[i]).toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                }

                $('#times').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#times').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.totalTime} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#times').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.avgTime.toFixed(1)} sec</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#moves').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#moves').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.totalMoves}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#moves').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.avgMoves.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#types').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#types').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.moveTypeChangesTotal}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#types').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.moveTypeChangesAvg.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#amts').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#amts').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.knobAmtsTotalAvg.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#amts').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.knobAmtsAvgAvg.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))

                $('#amtsTotal').append($('<hr>').css({'margin-bottom':'3px', 'margin-top':'3px'}))
                $('#amtsTotal').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.knobSumTotal.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
                $('#amtsTotal').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoSingle.knobTotalAvg.toFixed(1)}</div>`).css({'font-size':'14px', 'float':'right', 'padding-right':'100px'})))
            }
            if (shouldSendLevel) {
                let dataObj = {events:data.graphDataSingle.events, data:data.graphDataSingle.event_data, times:data.graphDataSingle.times}
                drawWavesChart(dataObj)
                drawWavesGoals(data)
            }
            // console.timeEnd('getWavesData')

            off()
            hideError()
        }, 'json').fail((jqXHR, textStatus, errorThrown) => {
            off()
            console.log(reqParams)
            console.log('Error triggered by getSingleData')
            showError(jqXHR.responseText)
        })
    }

    function drawWavesHistograms(data) {
        // console.time('drawWavesHistograms')
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

        $('#goalsDiv2All').html('Histogram 2: Total Number of slider moves')
        $('#goalsDiv2All').css('display', 'block')
        $('#goalsGraph2All').css('display', 'block')
        let trace2 = {
            x: data.numMoves,
            autobinx: false,
            xbins: {
                start: 0,
                end: data.numMoves.length-1,
                size: 25
            },
            type: 'histogram'
        }
        let layout2 = {
            margin: { t: 35 },
            height: 200,
            plot_bgcolor: '#F6F6F3',
            paper_bgcolor: '#F6F6F3',
            xaxis: {
                title: 'Total Number of slider moves',
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
        // console.timeEnd('drawWavesHistograms')

        $('#goalsDiv4All').html('Cluster graph, dunn = ' + data.clusters.dunn)
        $('#goalsDiv4All').css('display', 'block')
        eigen = '<ul>';
        (data.clusters.eigenvectors || []).forEach((eigenCol, i) => {
            eigen += '<li><p>PCA eigenvector ' + (i + 1) + '</p><ul>';
            eigenCol.forEach((val, j) => {
                eigen += '<li>' + data.clusters.sourceColumns[j] + ': ' + val + '</li>';
            });
            eigen += '</ul></li>';
        });
        eigen += '</ul>';
        $('#goalsEigen4All').html(eigen);
        $('#goalsEigen4All').css('display', 'block')
        $('#goalsGraph4All').css('display', 'block')
        let trace4 = (data.clusters.clusters || []).map(function(cluster, i){
            return {
                x: cluster.map((ary) => ary[0]),
                y: cluster.map((ary) => ary[1]),
                text: cluster.map((ary) => ary[2]),
                mode: 'markers',
                type: 'scatter',
            };
        });
        let layout4 = {
            margin: { t: 35 },
            height: 500,
            plot_bgcolor: '#F6F6F3',
            paper_bgcolor: '#F6F6F3',
            xaxis: {
                title: data.clusters.col1,
                titlefont: {
                    family: 'Courier New, monospace',
                    size: 12,
                    color: '#7f7f7f'
                }
            },
            yaxis: {
                title: data.clusters.col2,
                titlefont: {
                    family: 'Courier New, monospace',
                    size: 12,
                    color: '#7f7f7f'
                }
            },
            showlegend: false
        }

        Plotly.newPlot(clusterGraph, trace4, layout4)
        // console.timeEnd('drawWavesHistograms')
    }

    function drawWavesGoals(data) {
        // Goals stuff
        // console.time('drawWavesGoals')
        $('#goalsDiv1').html('Goal 1: Completing the challenge')

        let goalSlope1 = data.goalsSingle.goalSlope1
        let goalSlope2 = data.goalsSingle.goalSlope2

        let distanceTrace1 = {
            x: data.goalsSingle.moveNumbers,
            y: data.goalsSingle.absDistanceToGoal1,
            xaxis: 'x1',
            yaxis: 'y2',
            line: {color: 'green'},
            name: 'Distance to goal'
        }
        let closenessTrace1 = {
            x: data.goalsSingle.moveNumbers,
            y: data.goalsSingle.distanceToGoal1,
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
            x: data.goalsSingle.moveNumbers,
            y: data.goalsSingle.absDistanceToGoal2,
            xaxis: 'x1',
            yaxis: 'y2',
            overlaying: 'y',
            line: {color: 'green'},
            name: 'Distance to goal'
        }
        let closenessTrace2 = {
            x: data.goalsSingle.moveNumbers,
            y: data.goalsSingle.distanceToGoal2,
            line: {color: 'orange'},
            name: 'Net good moves'
        }
        let graphData2 = [closenessTrace2, distanceTrace2]

        if (graphData2[0].x.length > 0 && graphData2[0].y.length > 0 && 
            graphData2[1].x.length > 0 && graphData2[1].y.length > 0) {
            $('#slopeDiv2').html('Net good moves slope: ' + goalSlope2.toFixed(2))
            Plotly.newPlot(goalsGraph2, graphData2, layout)
        }
        // console.timeEnd('drawWavesGoals')
    }

    function drawWavesChart(inData) {
        // console.time('drawWavesChart')
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
        // console.timeEnd('drawWavesChart')
    }
    
    function on() {
        $('#loadingOverlay').css('display', 'block')
    }
    
    function off() {
        $('#loadingOverlay').css('display', 'none')
    }

    function showError(error) {
        $('#errorMessage').css('visibility', 'visible')
        $('#errorMessage').html('An internal server error has occurred. See console for details.')
        console.log(error)
    }

    function hideError() {
        errorTracker++
        if (errorTracker > 2) {
            $('#errorMessage').css('visibility', 'hidden')
            errorTracker = 0
        }
            
    }

    function showNoDataLeft() {
        $('#noDataOverlayLeft').css('display', 'block')
        $('#noDataOverlayGoals').css('display', 'block')
        let layout = {
            margin: { t: 35 },
            title: 'Left Sliders',
            showlegend: true,
            shapes: []
        }
        Plotly.newPlot(graphLeft, [], layout)
    }

    function hideNoDataLeft() {
        $('#noDataOverlayLeft').css('display', 'none')
        $('#noDataOverlayGoals').css('display', 'none')
    }

    function showNoDataRight() {
        $('#noDataOverlayRight').css('display', 'block')
        let layout = {
            margin: { t: 35 },
            title: 'Left Sliders',
            showlegend: true,
            shapes: []
        }
        Plotly.newPlot(graphRight, [], layout)
    }

    function hideNoDataRight() {
        $('#noDataOverlayRight').css('display', 'none')
    }

    function showNoDataGoals() {
        $('#noDataOverlayGoals1').css('display', 'block')
        $('#noDataOverlayGoals2').css('display', 'block')
        $('#slopeDiv1').html('Net good moves slope: 0')
        $('#slopeDiv2').html('Net good moves slope: 0')
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

    function showNoDataHistograms() {
        //$('#noDataOverlayGoals1').css('display', 'block')
        //$('#noDataOverlayGoals2').css('display', 'block')
        let layout = {
            plot_bgcolor: '#F6F6F3',
            paper_bgcolor: '#F6F6F3',
            margin: { t: 35 },
            height: 200
        }
        Plotly.newPlot(goalsGraph1All, [], layout)
        Plotly.newPlot(goalsGraph2All, [], layout)
        Plotly.newPlot(goalsGraph3All, [], layout)
    }

    function fastClear(selectElement) {
        let selectObj = selectElement[0]
        let selectParentNode = selectObj.parentNode
        let newSelectObj = selectObj.cloneNode(false)
        selectParentNode.replaceChild(newSelectObj, selectObj)
        return newSelectObj
    }

    $('#exportBtn').click(() => {
        let exportQueue = getAllData(false, true)
        exportQueue.emptyFunc = function () { $('#exportModal').modal() }
    })

    let networkQueue = function(numSimultaneous = 2) {
        let self = this
        self.queue = []
        self.numActiveCalls = 0
        self.numSimultaneous = numSimultaneous
        self.promises = []
        self.emptyFunc = undefined
        self.push = function(call) {
            self.queue.push(call)
            if (self.numActiveCalls < self.numSimultaneous) {
                self.execute()
            }
        }
        self.execute = function() {
            if (self.queue.length <= 0) {
                self.doWhenEmpty()
                return
            }
            self.numActiveCalls++
            let call = self.queue.shift()
            self.promises.push(
                $.get('responsePage.php', call.parameters, (data, status, jqXHR) => { call.callback(data); },
                'json').fail((jqXHR, textStatus, errorThrown) => {
                    off()
                    showError(errorThrown)
                }).then(() => {
                    self.numActiveCalls--
                    self.execute()
                })
            )
        }
        self.doWhenEmpty = function() {
            if (self.emptyFunc) {
                self.emptyFunc()
            }
        }
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
