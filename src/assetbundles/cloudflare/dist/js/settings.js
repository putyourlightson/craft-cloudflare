'use strict';

/*
 global window,
 Craft
 */

const zoneSelect            = document.getElementById('settings-zone'),
    verifyContainer         = document.querySelector('.cloudflare-verify'),
    verifyCredentialsButton = document.getElementById('settings-cf-test'),
    purgeUrlsButton         = document.getElementById('settings-purge-urls'),
    purgeUrlsField          = document.getElementById('settings-urls'),
    emailField              = document.getElementById('settings-email');

verifyCredentialsButton.addEventListener('click', event => {
    event.preventDefault();
    
    const apiKeyField = document.getElementById('settings-apiKey');

    const apiKey = apiKeyField.value,
        email  = emailField.value;

    if (!apiKey || !email) {
        return alert(window.__CLOUDFLARE_PLUGIN.messages.noCredentialsEntered);
    }

    const selectedZoneId = zoneSelect.querySelector('option:checked') ? zoneSelect.querySelector('option:checked').value : false;

    Craft.postActionRequest(
        window.__CLOUDFLARE_PLUGIN.actions.fetchZones,
        {apiKey, email},
        (response, statusText, request) => {
            // check for errors
            if (statusText === 'error' || !response) {
                alert(window.__CLOUDFLARE_PLUGIN.messages.credentialVerificationFailed);

                verifyContainer.classList.remove('success');
                verifyContainer.classList.add('fail');

                console.error('Credential verification failed with response: ', response);

                return false;
            }

            // clear existing options
            Array.from(zoneSelect.querySelectorAll('option'))
                .forEach(option => option.remove());

            // append zone options from Cloudflare
            for (let i = 0; i < response.length; i++) {
                const row    = response[i];
                const option = document.createElement('option');

                option.value       = row.id;
                option.textContent = row.name;

                zoneSelect.appendChild(option);
            }

            // restore selection
            if (selectedZoneId) {
                zoneSelect.value = selectedZoneId;
            }

            verifyContainer.classList.remove('fail');
            verifyContainer.classList.add('success');
        }
    );
});

if (purgeUrlsButton) {
    purgeUrlsButton.addEventListener('click', event => {
        event.preventDefault();

        const urls = purgeUrlsField.value;

        Craft.postActionRequest(
            window.__CLOUDFLARE_PLUGIN.actions.purgeUrls,
            {urls},
            (response, statusText, request) => {
                if (statusText === 'error') {
                    console.error('URL purge failed with response:', response);
                    return alert(window.__CLOUDFLARE_PLUGIN.messages.purgeUrlsFailed);
                }

                purgeUrlsField.value = '';

                return alert(window.__CLOUDFLARE_PLUGIN.messages.purgeUrlsSucceeded);
            }
        );
    });
}
