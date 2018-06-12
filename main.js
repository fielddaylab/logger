$(document).ready((event) => {
    $(document).on('change', '#gameSelect', (event) => {
        event.preventDefault()
        on()
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val() }, (data, status, jqXHR) => {
            $('#sessions').text(data.numSessions + ' sessions available')
            for (let i = 0; i < data.numSessions; i++) {
                $('#sessionSelect').append($('<option>', { value:data.sessions[i], text:data.sessions[i]}))
            }
            for (let i = 0; i < data.levels.length; i++) {
                $('#levelSelect').append($('<option>', { value:data.levels[i], text:data.levels[i]}))
            }
            let opt = $("#levelSelect option").sort(function (a,b) { return a.value.toUpperCase().localeCompare(b.value.toUpperCase(), {}, {numeric:true}) });
            $("#levelSelect").append(opt);
            off()
          }, 'json')
    })

    $(document).on('change', '#sessionSelect', (event) => {
        event.preventDefault()
        on()
        $.get('responsePage.php', { 'sessionID': $('#sessionSelect').val() }, (data, status, jqXHR) => {
            for (let i = 0; i < data.levels.length; i++) {
                $('#levelSelect').append($('<option>', { value:data.levels[i], text:data.levels[i]}))
            }
            off()
          }, 'json')
    })
})

function on() {
    $('#overlay').css('display', 'block')
}

function off() {
    $('#overlay').css('display', 'none')
}