$zoneSelect      = $("#settings-zone");
$purgeUrlField   = $("#settings-urls");
$verifyContainer = $(".cloudflare-verify");

$("#settings-cf-test").on('click', function(e){
    e.preventDefault();

    if ($("#settings-apiKey").val() !== '' && $("#settings-email").val() !== '') {

        selectedZoneId = $("#settings-zone option:checked").val();

        $.ajax({
            url: window.FETCH_ZONES_ACTION,
            type: 'GET',
            data: { "apiKey": $("#settings-apiKey").val(), "email": $("#settings-email").val() },
            success: function(data){
                if (data !== null && data.hasOwnProperty('result')) {
                    // clear existing options
                    $zoneSelect.find('option').remove();

                    // append zone options from Cloudflare
                    for (var i = 0; i < data.result.length; i++) {
                        var row = data.result[i];
                        $zoneSelect.append('<option value="'+row.id+'">'+row.name+'</option>');
                    }

                    // restore selection
                    if (selectedZoneId) {
                        $zoneSelect.val(selectedZoneId);
                    }

                    $verifyContainer.removeClass("fail").addClass("success");
                } else {
                    alert('Failed.');
                    $verifyContainer.removeClass("success").addClass("fail");
                    console.log(data);
                }
            },
            error: function(data) {
                alert('Failed.');
                $verifyContainer.removeClass("success").addClass("fail");
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
        data: { "urls": $purgeUrlField.val() },
        success: function(data){
            $purgeUrlField.val('');
            alert("URL(s) purged.");
        },
        error: function(data) {
            alert('Failed.');
            console.log(data);
        }
    });
});
