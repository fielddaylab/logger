$(document).ready((event) => {
    $(document).on('change', '#gameSelect', (event) => {
        event.preventDefault()
        console.log($('#gameSelect').val())
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val() }, (data, status, jqXHR) => {
            $('#sessions').text(data.numSessions + ' sessions available')
            for (let i = 0; i < data.numSessions; i++) {
                $('#sessionSelect').append($('<option>', { value:data.sessions[i], text:data.sessions[i]}))
            }
          }, 'json')
    })
})