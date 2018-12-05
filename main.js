$(document).ready((event) => {
    let numSimultaneous = 4 // default value
    $.get('config.json', {}, (data, status, jqXHR) => { 
        numSimultaneous = data.numSimultaneous

        // Do everything else after this request has finished successfully
        new ClipboardJS('#copyBtn', {
            text: (trigger) => {
                return $('#sessionSelect').val()
            }
        })
        let graphLeft = $('#graphLeft')[0]
        let graphRight = $('#graphRight')[0]
        let goalsGraph1 = $('#goalsGraph1')[0]
        let goalsGraph2 = $('#goalsGraph2')[0]
    
        let histogramAll1 = $('#goalsGraph1All')[0]
        let histogramAll2 = $('#goalsGraph2All')[0]
        let histogramAll3 = $('#goalsGraph3All')[0]
        let histogramAll8 = $('#goalsGraph8All')[0]
        let clusterGraph = $('#clusterGraph')[0]
    
        let requestStartTime
    
        // Minimize each table
        let collapserNames = []
        $('.table-row-collapse').each((index, value) => {
            let inputElementText = $(value).find('b:first()').text()
            let id = camelize(inputElementText)
            collapserNames.push(id)
            $(value).find('b:first()').parent().after($(`<a href='#${id+'Collapser'}' data-toggle='collapse' id='${id}Btn' style="float:none;margin-left:10px;" class='collapseBtn'>[+]</a>`))
            $(value).find('span:first()').attr('id', id+'Collapser').addClass('collapse')
                .on('hide.bs.collapse', () => {
                    $(`#${id}Btn`).html('[+]')
                })
                .on('show.bs.collapse', () => {
                    $(`#${id}Btn`).html('[−]')
                })
        })
    
        let theQueue
        let totalSessions
        let errorTracker = 0
        let queueExists = false
        let model
        let allFeatures

        window.onerror = () => {
            errorTracker = 0
            off()
            $('#errorMessage').css('visibility', 'visible').html('A JavaScript error has occurred. See console for details.') 
        }

        let defaultStates = $('.defaultState').clone(true).toArray()
        
        $(document).on('change', '#gameSelect', (event) => {
            event.preventDefault()

            // Send request that gets the model and populate tables and headers
            $.get('model.json', {}, data => {
                // Reset all the tables and filters so they're not just appended to infinitely
                fastClear($('#featuresList'))
                $.each($('.defaultState'), function (i, element) {
                    $(this).html(defaultStates[i].innerHTML)
                })

                model = data[$('#gameSelect').val()]
                // store model in localStorage so correlationGraph.html can see it
                localStorage.setItem('model', JSON.stringify(model))
                allFeatures = flattenObj(model.features)
                // numLevelsBody
                if (true) { // this is simply so I can collapse this section of code
                    let algorithmNames = model.algorithms.numLevels
                    let rowNames = { ...model.features.numLevels.general, ...model.features.numLevels.specific }
                    let headers = model.columns.numLevels.headers // each one has title and href
                    let headerSpans = model.columns.numLevels.headerSpans
                    let headerSpanIndexes = $.map(headerSpans, (val, i) => { // array of indexes where a headerSpan starts
                        let currentIndex = 0;
                        for (let j = 0; j <= i-1; j++) {
                            currentIndex += headerSpans[j].colSpan
                        }
                        return currentIndex
                    })
                    let hasHeader = Object.keys(headerSpans).length > 0

                    if (hasHeader) {
                        $('#numLevelsHead tr:eq(0) th:eq(0)').attr('rowspan', 2)
                        $('#numLevelsHead tr:eq(0)').after($('<tr></tr>'))
                    }

                    $.each(headerSpans, (i, headerSpan) => {
                        let headerSpanElement = $(`<th class="challenge-header" scope="col" colspan="${headerSpan.colSpan}">${headerSpan.title}</th>`)
                        $('#numLevelsHead tr:eq(0)').append(headerSpanElement)
                    })

                    let zeroIndex = 0
                    $.each(headers, (i, header) => {
                        let style = ''
                        if (zeroIndex == 0 || headerSpanIndexes.indexOf(zeroIndex) !== -1) { // first col and new headerspans have thicker left borders
                            style = 'border-left-width:4px;'
                        }
                        let headerElement = $(`<th scope="col" style="${style}"><a target="_blank" href="download.php?file=${header.href}">${header.title}</a></th>`)
                        $(`#numLevelsHead tr:eq(${hasHeader ? 1 : 0})`).append(headerElement)

                        let numSessionsElement = $(`<td style="${style}">-</td>`)
                        $(`#numLevelsNumSessionsRow`).append(numSessionsElement)
                        zeroIndex++
                    })
                    
                    $.each(rowNames, (i, rowName) => {
                        let newRow = $('<tr></tr>')
                        zeroIndex = 0
                        newRow.append($(`<th scope="row">${rowName}</th>`))
                        $.each(headers, (j, header) => {
                            let style = ''
                            if (zeroIndex == 0 || headerSpanIndexes.indexOf(zeroIndex) !== -1) { // first col and new headerspans have thicker left borders
                                style = 'border-left-width:4px;'
                            }
                            newRow.append($(`<td style="${style}"></td>`))
                            zeroIndex++
                        })
                        $('#numLevelsBody').append(newRow)
                    })

                    let perLevelZeroIndex = 0
                    $.each(model.features.levelCompletion.perLevel, (i, rowName) => {
                        let newRow = $('<tr></tr>')
                        zeroIndex = 0
                        newRow.append($(`<th scope="row">${rowName}</th>`))
                        $.each(headers, (j, header) => {
                            let style = ''
                            if (zeroIndex == 0 || headerSpanIndexes.indexOf(zeroIndex) !== -1) { // first col and new headerspans have thicker left borders
                                style = 'border-left-width:4px;'
                            }
                            let newCell = $(`<td style="${style}"></td>`)
                            if (perLevelZeroIndex >= zeroIndex) {
                                $(newCell).css('background-color', 'rgb(221, 221, 221)')
                                $(newCell).addClass('disabled-cell')
                            }
                            newRow.append(newCell)
                            zeroIndex++
                        })
                        $('#numLevelsBody').append(newRow)
                        perLevelZeroIndex++
                    })
                    $.each(algorithmNames, (i, name) => {
                        zeroIndex = 0
                        let newRow = $(`<tr ${(i === 0) ? 'style="border-top: 4px solid rgb(221, 221, 221);"' : ''}></tr>`)
                        newRow.append(`<th scope="row">${name}</th>`)
                        $.each(headers, (j, header) => {
                            let style = ''
                            if (zeroIndex == 0 || headerSpanIndexes.indexOf(zeroIndex) !== -1) { // first col and new headerspans have thicker left borders
                                style = 'border-left-width:4px;'
                            }
                            newRow.append($(`<td style="${style}"></td>`))
                            zeroIndex++
                        })
                        $('#numLevelsBody').append(newRow)
                    })
                }
                // levelCompletionBody
                if (true) {
                    let algorithmNames = model.algorithms.levelCompletion
                    let rowNames = { ...model.features.levelCompletion.general, ...model.features.levelCompletion.specific }
                    let headers = model.columns.levelCompletion.headers // each one has title and href
                    let headerSpans = model.columns.levelCompletion.headerSpans
                    let headerSpanIndexes = $.map(headerSpans, (val, i) => { // array of indexes where a headerSpan starts
                        let currentIndex = 0;
                        for (let j = 0; j <= i-1; j++) {
                            currentIndex += headerSpans[j].colSpan
                        }
                        return currentIndex
                    })
                    let hasHeader = Object.keys(headerSpans).length > 0

                    if (hasHeader) {
                        $('#levelCompletionHead tr:eq(0) th:eq(0)').attr('rowspan', 2)
                        $('#levelCompletionHead tr:eq(0)').after($('<tr></tr>'))
                    }

                    $.each(headerSpans, (i, headerSpan) => {
                        let headerSpanElement = $(`<th class="challenge-header" scope="col" colspan="${headerSpan.colSpan}">${headerSpan.title}</th>`)
                        $('#levelCompletionHead tr:eq(0)').append(headerSpanElement)
                    })

                    let zeroIndex = 0
                    $.each(headers, (i, header) => {
                        let style = ''
                        if (zeroIndex == 0 || headerSpanIndexes.indexOf(zeroIndex) !== -1) { // first col and new headerspans have thicker left borders
                            style = 'border-left-width:4px;'
                        }
                        let headerElement = $(`<th scope="col" style="${style}"><a target="_blank" href="download.php?file=${header.href}">${header.title}</a></th>`)
                        $(`#levelCompletionHead tr:eq(${hasHeader ? 1 : 0})`).append(headerElement)

                        let percentCompleteElement = $(`<td style="${style}">- %</td>`)
                        $(`#levelCompletionPercentCompleteRow`).append(percentCompleteElement)

                        let numSessionsElement = $(`<td style="${style}">-</td>`)
                        $(`#levelCompletionNumSessionsRow`).append(numSessionsElement)
                        zeroIndex++
                    })
                    
                    $.each(rowNames, (i, rowName) => {
                        let newRow = $('<tr></tr>')
                        zeroIndex = 0
                        newRow.append($(`<th scope="row">${rowName}</th>`))
                        $.each(headers, (j, header) => {
                            let style = ''
                            if (zeroIndex == 0 || headerSpanIndexes.indexOf(zeroIndex) !== -1) { // first col and new headerspans have thicker left borders
                                style = 'border-left-width:4px;'
                            }
                            newRow.append($(`<td style="${style}"></td>`))
                            zeroIndex++
                        })
                        $('#levelCompletionBody').append(newRow)
                    })

                    let perLevelZeroIndex = 0
                    $.each(model.features.levelCompletion.perLevel, (i, rowName) => {
                        let newRow = $('<tr></tr>')
                        zeroIndex = 0
                        newRow.append($(`<th scope="row">${rowName}</th>`))
                        $.each(headers, (j, header) => {
                            let style = ''
                            if (zeroIndex == 0 || headerSpanIndexes.indexOf(zeroIndex) !== -1) { // first col and new headerspans have thicker left borders
                                style = 'border-left-width:4px;'
                            }
                            let newCell = $(`<td style="${style}"></td>`)
                            if (perLevelZeroIndex >= zeroIndex) {
                                $(newCell).css('background-color', 'rgb(221, 221, 221)')
                                $(newCell).addClass('disabled-cell')
                            }
                            newRow.append(newCell)
                            zeroIndex++
                        })
                        $('#levelCompletionBody').append(newRow)
                        perLevelZeroIndex++
                    })
                    $.each(algorithmNames, (i, name) => {
                        zeroIndex = 0
                        let newRow = $(`<tr ${(i === 0) ? 'style="border-top: 4px solid rgb(221, 221, 221);"' : ''}></tr>`)
                        newRow.append(`<th scope="row">${name}</th>`)
                        $.each(headers, (j, header) => {
                            let style = ''
                            if (zeroIndex == 0 || headerSpanIndexes.indexOf(zeroIndex) !== -1) { // first col and new headerspans have thicker left borders
                                style = 'border-left-width:4px;'
                            }
                            newRow.append($(`<td style="${style}"></td>`))
                            zeroIndex++
                        })
                        $('#levelCompletionBody').append(newRow)
                    })
                }
                // tableAllBody aka question answers from challenge 1
                if (false) {
                    let rowNames = [
                        'Constant term',
                        '# slider moves',
                        '# type changes',
                        '# levels completed',
                        'Time',
                        'Avg knob max-min', 
                        'Avg % good moves',
                        '# offset moves',
                        '# wavelength moves',
                        '# amplitude moves',
                        '# failures',
                        '% offset moves',
                        '% wavelength moves',
                        '% amplitude moves'
                    ]
                    let numQuestions = model.questionLevels.length
                    let algorithmNames = model.algorithms.multinomialQuestionTable
                    $(rowNames).each((i, rowName) => {
                        $('#tableAllBody').append(`
                        <tr>
                            <th scope="row">${rowName}</th>
                            ${'<td></td>'.repeat(16)}
                        </tr>
                        `)
                    })
                    $(['Log reg'].concat(algorithmNames)).each((i, value) => {
                        $('#tableAllBody').append(
                            $(`
                            <tr ${(i === 0) ? 'style="border-top: 4px solid rgb(221, 221, 221);"' : ''}>
                                <th scope="row">${value} accuracy</th>
                                ${'<td></td>'.repeat(16)}
                            </tr>
                            `)
                        )
                    })
                }
                // binomialQuestionBody
                if (true) {
                    let numQuestions = model.questionLevels.length
                    let algorithmNames = model.algorithms.binomialQuestion

                    for (let i = 1; i <= numQuestions; i++) {
                        ['A', 'B', 'C', 'D'].forEach(answer => {
                            $('#binomialQuestionHeader tr:eq(0)').append(`<th scope="col">Q${i}/${answer}</th>`)
                            $('#binomialQuestionHeader tr:eq(1)').append(`<td style="text-align:center;">-</td>`)
                        })
                    }
                    for (let i = 1; i <= model.questionLevels[0]; i++) {
                        let rowText
                        if (i === 1) rowText = 'L1 only'
                        else rowText = 'L1-L'+i

                        let rowElement = $('<tr>').append($('<th>').attr('rowspan', algorithmNames.length + 1).css({'width':'6%', 'vertical-align':'middle', 'border-bottom-width':'4px'}).text(rowText))
                        $('#binomialQuestionBody').append(rowElement)
                    }
                    $('#binomialQuestionBody tr').each((i, ival) => {
                        for (let j = 0; j < algorithmNames.length; j++) {
                            let rowContent = $(`<tr><td style="width:9%; ${(j === 0) ? 'border-bottom-width:4px;' : ''}">${algorithmNames[algorithmNames.length-1-j]}</td></tr>`)
                            for (let k = 0; k < numQuestions * 4; k++) {
                                $(rowContent).append(`<td style="width:${5}px;${(j === 0) ? 'border-bottom-width:4px;' : ''}"></td>`)
                            }
                            $(ival).after($(rowContent))
                        }
                    })
                }
                // multinomial table
                if (true) {
                    let numQuestions = model.questionLevels.length
                    let algorithmNames = model.algorithms.multinomialQuestion
                     
                    for (let i = 1; i <= numQuestions; i++) {
                        $('#multinomialQuestionHeader tr:eq(0)').append(`<th scope="col">Q${i}</th>`)
                        $('#multinomialQuestionHeader tr:eq(1)').append(`<td style="text-align:center;">-</td>`)
                    }
                    for (let i = 1; i <= model.questionLevels[0]; i++) {
                        let rowText
                        if (i === 1) rowText = 'L1 only'
                        else rowText = 'L1-L'+i

                        let rowElement = $('<tr>').append($('<th>').attr('rowspan', algorithmNames.length + 1).css({'vertical-align':'middle', 'border-bottom-width':'4px', 'width': '6%'}).text(rowText))
                        $('#multinomialQuestionBody').append(rowElement)
                    }
                    $('#multinomialQuestionBody tr').each((i, ival) => {
                        for (let j = 0; j < algorithmNames.length; j++) {
                            let rowContent = $(`<tr><td style="width:9%; ${(j === 0) ? 'border-bottom-width:4px;' : ''}">${algorithmNames[algorithmNames.length-1-j]}</td></tr>`)
                            for (let k = 0; k < numQuestions; k++) {
                                $(rowContent).append(`<td style="width:${5}px;${(j === 0) ? 'border-bottom-width:4px;' : ''}"></td>`)
                            }
                            $(ival).after($(rowContent))
                        }
                    })
                }

                $(Object.keys(allFeatures)).each((index, value) => {
                    if (index > 0) $('#featuresList').append(`
                    <li>
                        <input type="checkbox" name="${value}" id="${value}" ${(value == 'OFFSET' || value == 'WAVELENGTH' || value == 'AMPLITUDE') ? '' : 'checked'}>
                        <label for="${value}" style="font-weight:400;">${allFeatures[value]}</label>
                    </li>
                    `)
                })
            
                $('#mainContainer').hide().show(0) // force the page to redraw so collapsed elements don't open upwards
            }, 'json')
        })
        // Trigger game select change to populate tables for default value
        $('#gameSelect').trigger('change')
    
        $(document).on('click', '#goButton', (event) => {
            event.preventDefault()
            if (($('#gameSelect').val() !== 'WAVES' && $('#gameSelect').val() !== 'CRYSTAL') || !($('#numLevelsTableCheckbox').is(':checked') || $('#levelCompletionCheckbox').is(':checked') ||
                $('#questionsCheckbox').is(':checked') || $('#levelRangeQuestionCheckbox').is(':checked') || $('#otherFeaturesCheckbox').is(':checked') ||
                $('#multinomialQuestionCheckbox').is(':checked'))) {
                $('#invalidGame').fadeIn(100)
            } else {
                $('#invalidGame').hide()
                if (!queueExists) {
                    queueExists = true
                    requestStartTime = new Date()
                    $('#doneDiv').html('Working')
                    $('#doneDiv').show()
                    let workingTimer = setInterval(() => {
                        let currentText = $('#doneDiv').html()
                        let newText
                        if ((currentText.match(/\./g) || []).length < 4) {
                            newText = currentText + ' .'
                        } else {
                            newText = 'Working'
                        }
                        $('#doneDiv').html(newText).css('color', 'blue')
                    }, 500)
                    theQueue = getAllData(true)
                    theQueue.emptyFunc = () => {
                        clearInterval(workingTimer)
                        queueExists = false
                        let timeString = ''
                        let reqTime = Math.round((new Date() - requestStartTime) / 1000)
                        if (reqTime >= 60) timeString = `${Math.round(reqTime / 60)}m ${reqTime % 60}s`
                        else timeString = reqTime + 's'
                        if (!theQueue.aborted) $('#doneDiv').html(`Done. (${timeString})`).css('color', 'green')
                        else $('#doneDiv').html('Aborted.').css('color', 'red')
                    }
                }
            }
        })
    
        $(document).on('click', '#abortButton', (event) => {
            event.preventDefault()
            if (queueExists) {
                theQueue.abort()
            }
        })
    
        $(document).on('change', '#sessionSelect', (event) => {
            on()
            getSingleData(true, false)
            getSingleData(false, true)
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
            let numLevelsTableChecked = $('#numLevelsTableCheckbox').is(':checked'),
                levelCompletionTableChecked = $('#levelCompletionCheckbox').is(':checked'),
                questionTableChecked = $('#questionsCheckbox').is(':checked'),
                binaryQuestionTableChecked = $('#levelRangeQuestionCheckbox').is(':checked'),
                multinomialQuestionTableChecked = $('#multinomialQuestionCheckbox').is(':checked'),
                otherFeaturesChecked = $('#otherFeaturesCheckbox').is(':checked'),
                shouldUseAvgs = $('#useAvgs').is(':checked')
            let useCache = $('#useCache').is(':checked')
            let insertIntoCache = $('#insertIntoCache').is(':checked')
            let featuresListParameters = {}
            $(Object.keys(allFeatures)).each((index, value) => {
                if (index > 0) featuresListParameters[value] = $(`#${value}`).is(':checked')
            })
            let parametersBasic = {
                'gameID': $('#gameSelect').val(),
                'maxRows': $('#maxRows').val(),
                'minMoves': $('#minMoves').val(),
                'minQuestions': $('#minQuestions').val(),
                'startDate': $('#startDate').val(),
                'endDate': $('#endDate').val(),
                'shouldUseAvgs': shouldUseAvgs,
                'useCache': useCache,
                'insertIntoCache': insertIntoCache,
                'table': 'basic',
                'numMovesPerChallenge': $('#numMovesPerChallengeCluster').prop('checked') ? true : undefined,
                'knobAvgs': $('#knobAvgsCluster').prop('checked') ? true : undefined,
                'levelTimes': $('#levelTimesCluster').prop('checked') ? true : undefined,
                'percentGoodMovesAll': $('#percentGoodMovesAllCluster').prop('checked') ? true : undefined,
                'moveTypeChangesPerLevel': $('#moveTypeChangesPerLevelCluster').prop('checked') ? true : undefined,
                'knobStdDevs': $('#knobStdDevsCluster').prop('checked') ? true : undefined,
                'knobTotalAmts': $('#knobTotalAmtsCluster').prop('checked') ? true : undefined,
            }
            let queue = new networkQueue(numSimultaneous)
            let numCols, numTables = 0

            let tables = {
                'numLevels': { 
                    'checked': numLevelsTableChecked,
                    'table': 'numLevels',
                    'tableBody': 'numLevelsBody',
                    'numSessions': 'numLevelsNumSessionsRow',
                    'nthChild': true
                },
                'levelCompletion': { 
                    'checked': levelCompletionTableChecked,
                    'table': 'levelCompletion',
                    'tableBody': 'levelCompletionBody',
                    'numSessions': 'levelCompletionNumSessionsRow',
                    'nthChild': true
                },
                'binaryQuestion': { 
                    'checked': binaryQuestionTableChecked,
                    'table': 'binomialQuestion',
                    'tableBody': 'binomialQuestionBody',
                    'numSessions': 'binomialQuestionNumSessionsRow',
                    'nthChild': false
                },
                'multinomialQuestion': { 
                    'checked': multinomialQuestionTableChecked,
                    'table': 'multinomialQuestion',
                    'tableBody': 'multinomialQuestionBody',
                    'numSessions': 'multinomialQuestionNumSessionsRow',
                    'nthChild': false
                }
            }

            $.each(tables, (idx, currentTable) => {
                if (currentTable['checked']) {
                    let table = currentTable['table']
                    let tableBody = currentTable['tableBody']
                    let tableSessionsRow = currentTable['numSessions']
                    let nthChild = currentTable['nthChild']
                    numCols = Object.keys(model.columns[table].headers).length//$(`#${tableBody}`).find(`${nthChild ? 'tr:first td' : 'tr:not(:nth-of-type(1)):first td'}`).length
                    $(`#${collapserNames[numTables]}Collapser`).collapse('show')
                    $(`#${tableSessionsRow}`).children().each((key, value) => { if (key > 0) $(value).html('-') })
                    for (let i = 0; i < numCols; i++) {
                        let columnElements = $(`#${tableBody} tr td:nth-${nthChild ? 'child('+(i+2)+')' : 'of-type('+(i+2)+')'}`).not('.disabled-cell')
                        let column = Object.keys(model.columns[table].headers)[i]
        
                        let parameters = {
                            'gameID': $('#gameSelect').val(),
                            'maxRows': $('#maxRows').val(),
                            'minMoves': $('#minMoves').val(),
                            'minQuestions': $('#minQuestions').val(),
                            'minLevels': $('#minLevels').val(),
                            'maxLevels': $('#maxLevels').val(),
                            'startDate': $('#startDate').val(),
                            'endDate': $('#endDate').val(),
                            'column': column,
                            'table': table,
                            'shouldUseAvgs': shouldUseAvgs,
                            'useCache': useCache,
                            'insertIntoCache': insertIntoCache
                        }
                        let numAlgorithms = model.algorithms[table].length

                        let featuresParams = {}
                        $.each($('#featuresList input'), (j, val) => {
                            let flatFeatures = Object.keys({...model.features[table].general, ...model.features[table].specific})
                            featuresParams[val.id] = ($(val).is(':checked') && flatFeatures.indexOf(val.id) > -1)
                        })
                        // make parameters for per level inputs separately so they can 'cascade' in the table
                        $.each(Object.keys(model.features[table].perLevel), (j, val) => {
                            featuresParams[val] = (j < i && $(`#${val}`).is(':checked'))
                        })
        
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
                            } else {
                                $(value).text('')
                            }
                        })
                        let callbackFunc = (data) => {
                            clearInterval(loadTimer)
                            localStorage.setItem(`data_${table}_${column}`, JSON.stringify({ 'coefficients': data.coefficients||arrayColumn(data, 'coefficients'), 'stdErrs': data.stdErrs||arrayColumn(data, 'stdErrs') }))
                            let rowNames = Object.values(flattenObj(model.features[table]))
                            let numSessionsString = (data[1]) ? data[1].numSessionsString : data.numSessionsString
                            $(`#${tableSessionsRow} td:nth-child(${i+2})`).html(numSessionsString)
                            let rowNamesUsed = null
                            if (data.coefficients)
                                rowNamesUsed = Object.keys(data.coefficients)
                            else if (data[1].coefficients)
                                rowNamesUsed = Object.keys(data[1].coefficients)
                            localStorage.setItem(`row_names_${table}_${column}`, JSON.stringify(rowNamesUsed))
                            columnElements.each((j, jval) => {
                                $(jval).css({
                                    'vertical-align': 'middle',
                                    'background-color': backgroundColors[j],
                                    'border-top': borderTops[j],
                                    'border-bottom': borderBottoms[j]
                                })
                                $(jval).removeClass('activeLoadingElement')
                                let innerText = $('<div>')
                                innerText.html('-')
                                let rowName
                                if (data.pValues) {
                                    rowName = getKeyByValue($(`#${tableBody} tr th`).eq(j).text())
                                } else {
                                    rowName = $(jval).siblings(`td:first`).text()
                                }
                                if (data.pValues || data[1].pValues || data[1].percentCorrect) {
                                    if (j < columnElements.length - numAlgorithms && data.pValues) {
                                        rowName = getKeyByValue($(`#${tableBody} tr th`).eq(j).text())
                                        if (typeof data.pValues[rowName] === 'number' && !isNaN(data.pValues[rowName]) && typeof data.coefficients[rowName] === 'number' && !isNaN(data.coefficients[rowName])) {
                                            innerText.html(data.coefficients[rowName].toFixed(4) + ',<br>' + data.pValues[rowName].toFixed(4))
                                            if (data.pValues[rowName] < 0.05) {
                                                $(innerText).css('background-color', '#82e072')
                                            }
                                        } else if (typeof data.pValues[rowName] === 'string' && typeof data.coefficients[rowName] === 'string') {
                                            innerText.html(data.coefficients[rowName] + ',<br>' + data.pValues[rowName]) // expecting "NA" so no toFixed(4)
                                        } else if (data.pValues[rowName] === undefined || data.coefficients[rowName] === undefined) {
                                            innerText.html('-') // row was not checked for use in regression equation
                                        }
                                        $(jval).html(innerText)
                                        if (data.pValues[rowName] && rowName !== '(Intercept)')
                                            $(jval).wrapInner(`<a href="correlationGraph.html?gameID=${$('#gameSelect').val()}&table=${table}&row=${getKeyByValue(rowNames[j])}&col=${column}&i=${i}&j=${j}" target="_blank"></a>`)
                                    } else {
                                        let percentsCorrect, expectedAccuracy
                                        if (data.pValues) {
                                            percentsCorrect = data.percentCorrect[model.algorithms[table][j-columnElements.length+numAlgorithms]]
                                        } else {
                                            percentsCorrect = data[Math.floor(j / (numAlgorithms)) + 1].percentCorrect[rowName]
                                        }
                                        if (Array.isArray(percentsCorrect))
                                            percentsCorrect = $.map(percentsCorrect, (val, i) => { return parseFloat(val) })

                                        if (percentsCorrect && typeof percentsCorrect[0] === 'number' && !isNaN(percentsCorrect[0])) {
                                            if ((data[1] && (expectedAccuracy = data[1].expectedAccuracy)) || (expectedAccuracy = data.expectedAccuracy)) {
                                                if (percentsCorrect[0] > expectedAccuracy) {
                                                    $(innerText).css('background-color', '#82e072')
                                                }
                                                let percentsText = ''
                                                for (let percent in percentsCorrect) {
                                                    percentsText += percentsCorrect[percent].toFixed(4) + '<br>'
                                                }
                                                innerText.html(percentsText)
                                            } else {
                                                innerText.html(percentsCorrect[0].toFixed(5))
                                            }
                                        } else {
                                            innerText.html('-')
                                        }
                                        if (table === 'binomialQuestion' && j % numAlgorithms === 0) {
                                            $(innerText).wrapInner(`<a target="_blank" href="correlationGraph.html?gameID=${$('#gameSelect').val()}&table=${table}&row=${Math.floor(j/(numAlgorithms)+1)}&col=${column}&i=${i}&j=${Math.floor(j/(numAlgorithms))}"></a>`)
                                        } else if (table === 'multinomialQuestion') {
                                            $(innerText).wrapInner(`<a target="_blank" href="download.php?file=${model.columns.multinomialQuestion.headers[column].href + Math.floor(j/(numAlgorithms)+1)}.txt"></a>`)
                                        }
                                        $(jval).html(innerText)
                                    }
                                } else {
                                    $(jval).html(innerText)
                                }
                
                                $(innerText).css({'color': 'black', 'text-align': 'center', 'font': '14px "Open Sans", sans-serif'})
                            })
                            off()
                        }
        
                        req = {
                            parameters: {...parameters, 'features': featuresParams},
                            callback: callbackFunc,
                            failCallback: function(jqXHR, textStatus, errorThrown) {
                                showError(errorThrown)
                                columnElements.each((j, jval) => {
                                    $(jval).text('')
                                    $(jval).css({
                                        'vertical-align': 'middle',
                                        'background-color': 'rgba(255, 128, 128, 0.15)',
                                        'border-top': borderTops[j],
                                        'border-bottom': borderBottoms[j]
                                    })
                                    clearInterval(loadTimer)
                                    $(jval).removeClass('activeLoadingElement')
                                })
                            }
                        }
                        queue.push(req, loadTimer, columnElements.slice(), { 'backgroundColors': backgroundColors.slice(), 'borderBottoms': borderBottoms.slice(), 'borderTops': borderTops.slice() })
                    }
                }
                numTables++
            })
    
            if (otherFeaturesChecked) $(`#${collapserNames[numTables]}Collapser`).collapse('show')
    
            let mainCallback = (data) => {
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
                    let dataHistogram
                    if (otherFeaturesChecked) {
                        let timesList = $('<ul></ul>').attr('id', 'timesAll').addClass('collapse').css('font-size', '18px')
                        $('#basicFeaturesAll').append($(`<span><li>Times: <a href='#timesAll' data-toggle='collapse' id='timesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(timesList)
                            .on('hide.bs.collapse', () => { $('#timesCollapseBtnAll').html('[+]') })
                            .on('show.bs.collapse', () => { $('#timesCollapseBtnAll').html('[−]') }))
                        let movesList = $('<ul></ul>').attr('id', 'movesAll').addClass('collapse').css({ 'font-size': '18px' })
                        $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Number of slider moves: <a href='#movesAll' data-toggle='collapse' id='movesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(movesList)
                            .on('hide.bs.collapse', () => { $('#movesCollapseBtnAll').html('[+]') })
                            .on('show.bs.collapse', () => { $('#movesCollapseBtnAll').html('[−]') }))
                        let typesList = $('<ul></ul>').attr('id', 'typesAll').addClass('collapse').css({ 'font-size': '18px' })
                        $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Move type changes: <a href='#typesAll' data-toggle='collapse' id='typesCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(typesList)
                            .on('hide.bs.collapse', () => { $('#typesCollapseBtnAll').html('[+]') })
                            .on('show.bs.collapse', () => { $('#typesCollapseBtnAll').html('[−]') }))
                        let stdDevList = $('<ul></ul>').attr('id', 'stdDevsAll').addClass('collapse').css({ 'font-size': '18px' })
                        $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Knob std devs (avg): <a href='#stdDevsAll' data-toggle='collapse' id='stdDevsCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(stdDevList)
                            .on('hide.bs.collapse', () => { $('#stdDevsCollapseBtnAll').html('[+]') })
                            .on('show.bs.collapse', () => { $('#stdDevsCollapseBtnAll').html('[−]') }))
                        let amtsList = $('<ul></ul>').attr('id', 'amtsAll').addClass('collapse').css({ 'font-size': '18px' })
                        $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Knob max-min (avg): <a href='#amtsAll' data-toggle='collapse' id='amtsCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(amtsList)
                            .on('hide.bs.collapse', () => { $('#amtsCollapseBtnAll').html('[+]') })
                            .on('show.bs.collapse', () => { $('#amtsCollapseBtnAll').html('[−]') }))
                        let amtsTotalList = $('<ul></ul>').attr('id', 'amtsTotalAll').addClass('collapse').css({ 'font-size': '18px' })
                        $('#basicFeaturesAll').append($(`<span><li style='margin-top:5px'>Knob max-min (total): <a href='#amtsTotalAll' data-toggle='collapse' id='amtsTotalCollapseBtnAll' class='collapseBtn'>[+]</a></li></span>`).append(amtsTotalList)
                            .on('hide.bs.collapse', () => { $('#amtsTotalCollapseBtnAll').html('[+]') })
                            .on('show.bs.collapse', () => { $('#amtsTotalCollapseBtnAll').html('[−]') }))
    
                        for (let i = Object.keys(data.basicInfoAll.times)[0]; i <= Object.keys(data.basicInfoAll.times)[Object.keys(data.basicInfoAll.times).length - 1]; i++) {
                            if (data.basicInfoAll.times[i] === 'NaN') continue;
                            // append times
                            $('#timesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.times[i].toFixed(2)} sec</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
    
                            // append moves
                            $('#movesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.numMoves[i].toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
    
                            // append types
                            $('#typesAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.moveTypeChanges[i].toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
    
                            // append std devs
                            $('#stdDevsAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.knobStdDevs[i].toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
    
                            // append knob amounts
                            $('#amtsAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgMaxMin[i].toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
    
                            // append knob total amounts
                            $('#amtsTotalAll').append($(`<li>Level ${i}: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalMaxMin[i].toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
                        }
    
                        $('#timesAll').append($('<hr>').css({ 'margin-bottom': '3px', 'margin-top': '3px' }))
                        $('#timesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalTime.toFixed(2)} sec</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
                        $('#timesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgTime.toFixed(2)} sec</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
    
                        $('#movesAll').append($('<hr>').css({ 'margin-bottom': '3px', 'margin-top': '3px' }))
                        $('#movesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalMoves.toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
                        $('#movesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgMoves.toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
    
                        $('#typesAll').append($('<hr>').css({ 'margin-bottom': '3px', 'margin-top': '3px' }))
                        $('#typesAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalMoveChanges.toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
                        $('#typesAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgMoveChanges.toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
    
                        $('#amtsAll').append($('<hr>').css({ 'margin-bottom': '3px', 'margin-top': '3px' }))
                        $('#amtsAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalKnobAvgs.toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
                        $('#amtsAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgKnobAvgs.toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
    
                        $('#amtsTotalAll').append($('<hr>').css({ 'margin-bottom': '3px', 'margin-top': '3px' }))
                        $('#amtsTotalAll').append($(`<li>Total: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.totalKnobTotals.toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
                        $('#amtsTotalAll').append($(`<li>Avg: </li>`).css('font-size', '14px').append($(`<div>${data.basicInfoAll.avgKnobTotals.toFixed(2)}</div>`).css({ 'font-size': '14px', 'float': 'right', 'padding-right': '100px' })))
                        dataHistogram = {
                            'questionAnswereds': data.questionAnswereds, 'numsQuestions': data.questionsAll.numsQuestions, 'numMoves': data.numMovesAll,
                            'numLevels': data.numLevelsAll, 'numTypeChanges': data.numTypeChangesAll, 'clusters': data.clusters
                        }
                    }
    
                    if ($('#sessionSelect option').length > 0) {
                        getSingleData(true, false) 
                    } else if ($('#sessionSelect option').length === 0) {
                        $('#scoreDisplayAll').html('- / -')
                        fastClear($('#basicFeaturesAll'))
                        showNoDataHistograms()
                        off()
                    }
                    $('#levelCompletionPercentCompleteRow').children('td').each((index, value) => {
                        if (typeof data.lvlsPercentComplete[index] === 'number') {
                            $(value).html(data.lvlsPercentComplete[index].toPrecision(4) + ' %')
                        } else {
                            $(value).html(data.lvlsPercentComplete[index] + ' %')
                        }
                    })
                    if (otherFeaturesChecked) {
                        drawWavesHistograms(dataHistogram)
                    }
                } else {
                    off()
                    hideError()
                }
            }
            mainReq = {
                parameters: parametersBasic,
                callback: mainCallback
            }
            queue.push(mainReq)
    
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
            let questions = []
            for (let i = 0; i < 4; i++) {
                questions[i] = arrayColumn(Object.values(data.questionAnswereds), i).map((val) => {
                    switch (val) {
                        case 0:
                            return 'A'
                        case 1:
                            return 'B'
                        case 2:
                            return 'C'
                        case 3:
                            return 'D'
                    }
                }).sort()
            }
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
    
            for (let i = 0; i < 4; i++) {
                $(`#goalsDiv${i + 4}All`).html(`Histogram ${i + 4}: Question ${i + 1} answers`)
                $(`#goalsDiv${i + 4}All`).css(`display`, `block`)
                $(`#goalsGraph${i + 4}All`).css(`display`, `block`)
                let trace = {
                    x: questions[i],
                    type: 'histogram'
                }
                let layout = {
                    margin: { t: 35 },
                    height: 200,
                    plot_bgcolor: '#F6F6F3',
                    paper_bgcolor: '#F6F6F3',
                    xaxis: {
                        title: 'Question answer',
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
    
                Plotly.newPlot($(`#goalsGraph${i+4}All`)[0], [trace], layout)
            }
    
            $(`#goalsDiv8All`).html(`Histogram 8: Type changes`)
            $(`#goalsDiv8All`).css(`display`, `block`)
            $(`#goalsGraph8All`).css(`display`, `block`)
            let trace8 = {
                x: data.numTypeChanges,
                type: 'histogram'
            }
            let layout8 = {
                margin: { t: 35 },
                height: 200,
                plot_bgcolor: '#F6F6F3',
                paper_bgcolor: '#F6F6F3',
                xaxis: {
                    title: 'Number of type changes',
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
    
            Plotly.newPlot(histogramAll8, [trace8], layout8)
    
            $('#clusterGraph').html('Cluster graph, dunn = ' + data.clusters.dunn)
            $('#clusterGraph').css('display', 'block')
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
            $('#clusterGraph').css('display', 'block')
            let trace5 = (data.clusters.clusters || []).map(function(cluster, i){
                return {
                    x: cluster.map((ary) => ary[0]),
                    y: cluster.map((ary) => ary[1]),
                    text: cluster.map((ary) => ary[2]),
                    mode: 'markers',
                    type: 'scatter',
                };
            });
            let layout5 = {
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
    
            Plotly.newPlot(clusterGraph, trace5, layout5)
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
    
        let networkQueue = function(numSimultaneous = 2) {
            let self = this
            self.queue = []
            self.numActiveCalls = 0
            self.numSimultaneous = numSimultaneous
            self.promises = []
            self.jqXHRs = []
            self.emptyFunc = undefined
            self.aborted = false
            self.loadTimers = [] // Used for aborting to reset columns
            self.columnElements = []
            self.columnStyles = []
            self.activeColElements = [] // Columns the server is actually working on, not waiting to send
            self.activeColStyles = []
            self.activeColTimers = []
            self.push = function(call, loadTimer, colElements, colStyles) {
                self.loadTimers.push(loadTimer)
                self.columnElements.push(colElements)
                self.columnStyles.push(colStyles)
    
                self.queue.push(call)
                if (self.numActiveCalls < self.numSimultaneous) {
                    self.execute()
                }
            }
            self.execute = function() {
                if (self.numActiveCalls <= 0) {
                    self.doWhenEmpty()
                }
                if (self.queue.length <= 0) {
                    return
                }
                self.numActiveCalls++
                let call = self.queue.shift()
                let currentCol = self.columnElements.shift()
                self.activeColElements.push(currentCol)
                self.activeColStyles.push(self.columnStyles.shift())
                self.activeColTimers.push(self.loadTimers.shift())
                $(currentCol).each((cellIndex, element) => {
                    $(element).addClass('activeLoadingElement')
                })
                let jqXHR = $.get('responsePage.php', call.parameters, (data, status, jqXHR) => { call.callback(data) }, 'json')
                self.jqXHRs.push(jqXHR)
                self.promises.push(
                    jqXHR.fail((jqXHR, textStatus, errorThrown) => {
                        off()
                        if (!self.aborted) showError(errorThrown)
                    }).then(() => {
                        self.numActiveCalls--
                        self.execute()
                    }, (jqXHR, textStatus, errorThrown) => { // handler of fail, still say this request is done and execute another one
                        if (call.failCallback) call.failCallback(jqXHR, textStatus, errorThrown)
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
            self.abort = function() {
                self.aborted = true
                if (self.queue.length <= 0 && self.numActiveCalls <= 0) return
                $(self.jqXHRs).each((index, jqXHR) => {
                    jqXHR.abort()
                })
                $(self.loadTimers).each((index, timer) => {
                    clearInterval(timer)
                })
                $(self.activeColTimers).each((index, timer) => {
                    clearInterval(timer)
                })
                $(self.columnElements).each((index, colElements) => {
                    $(colElements).each((cellIndex, element) => {
                        let text = $(element).text()
                        if (text === '.' || text === '. .' || text === '. . .' || text === '. . . .') $(element).text('')
                        $(element).css({
                            'vertical-align': 'middle',
                            'background-color': self.columnStyles[index].backgroundColors[cellIndex],
                            'border-top': self.columnStyles[index].borderTops[cellIndex],
                            'border-bottom': self.columnStyles[index].borderBottoms[cellIndex]
                        })
                    })
                })
                $(self.activeColElements).each((index, colElements) => {
                    $(colElements).each((cellIndex, element) => {
                        $(element).text('')
                        $(element).css({
                            'vertical-align': 'middle',
                            'background-color': self.activeColStyles[index].backgroundColors[cellIndex],
                            'border-top': self.activeColStyles[index].borderTops[cellIndex],
                            'border-bottom': self.activeColStyles[index].borderBottoms[cellIndex]
                        })
                    })
                })
                self.queue = []
                self.promises = []
                self.jqXHRs = []
                self.doWhenEmpty()
            }
        }
    
        function arrayColumn(array, columnName) {
            if (typeof array === 'string') array = JSON.parse(array)
            return $.map(array, function (value, index) {
                return value[columnName]
            })
        }
    
        function camelize(str) {
            return str.replace(/(?:^\w|[A-Z]|\b\w|\s+)/g, function(match, index) {
                if (+match === 0) return ""; // or if (/\s+/.test(match)) for white spaces
                return index == 0 ? match.toLowerCase() : match.toUpperCase();
            });
        }
    
        $('#toggleFeatures').on('click', (event) => {
            event.preventDefault()
            if ($('#featuresList input:not(:checked)').length > 0) {
                $('#featuresList input').each((index, value) => {
                    $(value).prop('checked', true)
                })
            } else {
                $('#featuresList input').each((index, value) => {
                    $(value).prop('checked', false)
                })
            }
        })
    
        function getKeyByValue(str) {
            return Object.keys(allFeatures).find(key => allFeatures[key] === str)
        }

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
    }, 'json')
})
