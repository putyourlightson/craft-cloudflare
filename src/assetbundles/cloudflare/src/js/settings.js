'use strict';

/*
 global window,
 Craft
 */

const credentialSpinner = document.getElementById('settings-credential-spinner');

const zoneSelect = document.getElementById('settings-zone');

const verifyContainer = document.querySelector('.cloudflare-verify');
const verifyCredentialsButton = document.getElementById('settings-cf-test');
const purgeUrlsButton = document.getElementById('settings-purge-urls');
const purgeUrlsField = document.getElementById('settings-urls');
const authTypeField = document.getElementById('settings-authType');
const apiTokenField = document.getElementById('settings-apiToken');
const apiKeyField = document.getElementById('settings-apiKey');
const emailField = document.getElementById('settings-email');

verifyCredentialsButton.addEventListener('click', event => {
    event.preventDefault();

    const settings = getAuthSettings();

    if (settings === false) {
        return alert(window.__CLOUDFLARE_PLUGIN.messages.credentialsMissing);
    }

    const selectedZoneId = zoneSelect.querySelector('option:checked') ? zoneSelect.querySelector('option:checked').value : false;
    showSpinner();

    Craft.postActionRequest(
        window.__CLOUDFLARE_PLUGIN.actions.verifyCredentials,
        settings,
        (response, statusText, request) => {
            hideSpinner();

            // check for errors
            if (statusText === 'error' || !response) {
                alert(window.__CLOUDFLARE_PLUGIN.messages.credentialVerificationFailed);

                verifyContainer.classList.remove('success');
                verifyContainer.classList.add('fail');

                console.error('Credential verification failed with response: ', response);

                return false;
            }

            if (response.success == false) {
                verifyContainer.classList.remove('success');
                verifyContainer.classList.add('fail');

                console.error('Credential verification failed with response: ', response);
                return false;
            }

            // if we succeeded, populate Cloudflare Zone options
            fetchZones();
        }
    );
});

function fetchZones() {
    const settings = getAuthSettings();
    const selectedZoneId = zoneSelect.querySelector('option:checked') ? zoneSelect.querySelector('option:checked').value : false;
    showSpinner();

    Craft.postActionRequest(
        window.__CLOUDFLARE_PLUGIN.actions.fetchZones,
        settings,
        (response, statusText, request) => {
            hideSpinner();

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

}

function showSpinner() {
    verifyContainer.classList.remove('success');
    verifyContainer.classList.remove('fail');

    credentialSpinner.classList.remove('hidden');
}

function hideSpinner() {
    credentialSpinner.classList.add('hidden');
}

function getAuthSettings() {
    // fetch field references here since they may not have been available earlier
    const authTypeField = document.getElementById('settings-authType');
    const apiTokenField = document.getElementById('settings-apiToken');
    const apiKeyField = document.getElementById('settings-apiKey');
    const emailField = document.getElementById('settings-email');

    const authType = authTypeField.value || false;
    const apiToken = apiTokenField.value || false;
    const apiKey = apiKeyField.value || false,
        email  = emailField.value || false;

    // make sure required fields exist
    if (
        (authType === 'key' && !apiKey || !email)
        || (authType === 'token' && ! apiToken)
    ) {
        return false;
    }

    if (authType === 'key') {
        return {
            authType,
            apiKey,
            email
        };
    }

    if (authType === 'token') {
        return {
            authType,
            apiToken
        };
    }

    return false;
}

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
