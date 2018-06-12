$(document).ready((event) => {
    $('.js-example-basic-single').select2()
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
                selectSession(event)
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
            off()
        }, 'json').error((jqXHR, textStatus, errorThrown) => {
            off()
            showError()
        })
    }
    
    function on() {
        $('#overlay').css('display', 'block')
    }
    
    function off() {
        $('#overlay').css('display', 'none')
    }

    function showError() {
        $('#errorMessage').css('visibility', 'visible')
    }
})