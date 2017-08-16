$("#settings-cf-test").on('click', function(e){
    e.preventDefault();

    if ($("#settings-apiKey").val() !== '' && $("#settings-email").val() !== '') {
        $.ajax({
            url: window.FETCH_ZONES_ACTION,
            type: 'GET',
            data: { "apiKey": $("#settings-apiKey").val(), "email": $("#settings-email").val() },
            success: function(data){
                $("#settings-zone option").remove();
                for (var i = 0; i < data.result.length; i++) {
                    var row = data.result[i];
                    $("#settings-zone").append('<option value="'+row.id+'">'+row.name+'</option>');
                }
            },
            error: function(data) {
                alert('Failed.');
                console.log(data);
            }
        });
    } else {
        alert('Please enter an API key and email address first.');
    }
});

$("#settings-purge-urls").click(function(e){
    e.preventDefault();

    $.ajax({
        url: window.PURGE_URLS_ACTION,
        type: 'POST',
        data: { "urls": $("#settings-urls").val() },
        success: function(data){
            $("#settings-urls").val('');
            alert("URL(s) purged.");
        },
        error: function(data) {
            alert('Failed.');
            console.log(data);
        }
    });
});
