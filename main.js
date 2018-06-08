$(document).ready((event) => {
    $(document).on('change', '#gameSelect', (event) => {
        event.preventDefault()
        console.log($('#gameSelect').val())
        $.get('responsePage.php', { 'gameID': $('#gameSelect').val() }, (data, status, jqXHR) => {
            $('#sessions').text(data.sessions + ' sessions available')
          }, 'json')
    })
})