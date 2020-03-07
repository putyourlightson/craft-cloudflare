'use strict';

/** global Craft */

const credentialSpinner = document.getElementById('settings-credential-spinner');
const zoneSelect = document.getElementById('settings-zone-select');
const zoneSelectWrap = document.getElementById('settings-zone-id-select');
const zoneInputWrap = document.getElementById('settings-zone-id-input')
const verifyContainer = document.querySelector('.cloudflare-verify');
const verifyCredentialsButton = document.getElementById('settings-cf-test');
const purgeUrlsButton = document.getElementById('settings-purge-urls');
const purgeUrlsField = document.getElementById('settings-urls');
const purgeAllButton = document.getElementById('settings-purge-all');

// widget purge URL pane toggle/heading
const purgeUrlsToggle = document.querySelector('.purge-option.purge-individual-urls .heading');
const purgeUrlsFormWrap = document.querySelector('.purge-urls-form');

if (verifyCredentialsButton) {
    verifyCredentialsButton.addEventListener('click', event => {
        event.preventDefault();

        const settings = getAuthSettings();

        if (settings === false) {
            Craft.cp.displayError(Craft.t('cloudflare', 'Please enter required API credentials.'));
            return;
        }

        showSpinner();

        Craft.postActionRequest(
            'cloudflare/default/verify-connection',
            settings,
            (response, statusText) => {
                hideSpinner();

                // check for errors
                if (statusText === 'error' || !response) {
                    Craft.cp.displayError(Craft.t('cloudflare', 'Could not verify API credentials.'));

                    verifyContainer.classList.remove('success');
                    verifyContainer.classList.add('fail');

                    console.error('Credential verification failed with response: ', response);

                    return false;
                }

                if (response.success === false) {
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
}

if (purgeUrlsButton) {
    purgeUrlsButton.addEventListener('click', event => {
        event.preventDefault();
        purgeUrls(purgeUrlsField.value);
    });
}

if (purgeAllButton) {
    purgeAllButton.addEventListener('click', event => {
        event.preventDefault();
        purgeAll();
    });
}

if (purgeUrlsToggle) {
    purgeUrlsFormWrap.classList.add('hidden');

    purgeUrlsToggle.addEventListener('click', event => {
        const heading = event.target;

        purgeUrlsFormWrap.classList.toggle('hidden');

        setTimeout(() => {
            heading.classList.toggle('active');
        }, 100);
    });
}

function fetchZones() {
    const settings = getAuthSettings();
    const selectedZoneId = zoneSelect.querySelector('option:checked') ? zoneSelect.querySelector('option:checked').value : false;
    showSpinner();

    Craft.postActionRequest(
        'cloudflare/default/fetch-zones',
        settings,
        (response, statusText) => {
            hideSpinner();

            // check for errors
            if (statusText === 'error' || !response) {
                Craft.cp.displayError(Craft.t('cloudflare', 'Could not verify API credentials.'));

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

            if (response.length === 0) {
                zoneSelectWrap.classList.add('hidden');
                zoneInputWrap.classList.remove('hidden');
            } else {
                zoneSelectWrap.classList.remove('hidden');
                zoneInputWrap.classList.add('hidden');
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
    const apiKey = apiKeyField.value || false;
    const email = emailField.value || false;

    // make sure required fields exist
    if (
        (authType === 'key' && (!apiKey || !email))
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

function purgeUrls(urls) {
    Craft.postActionRequest(
        'cloudflare/default/purge-urls',
        {urls},
        (response, statusText, request) => {
            const successful = handleResponse('URL purge', response);

            if (successful) {
                // empty the URL field
                purgeUrlsField.value = '';
            }
        }
    );
}

function purgeAll() {
    if (confirm(Craft.t('cloudflare', 'You definitely want to purge the entire cache, right?'))) {
        Craft.postActionRequest(
            'cloudflare/default/purge-all',
            {},
            (response, statusText, request) => {
                handleResponse('Zone purge', response);
            }
        );
    }
}

function handleResponse(intendedAction, response) {

    // successful?
    const success = typeof response.success !== 'undefined' && response.success;

    // Craft's ->asErrorJson will return {"error":"[message]"}
    const craftError = typeof response.error !== 'undefined' ? response.error : null;

    // Cloudflare errors will be an array on the "errors" key
    const apiErrors = typeof response.errors !== 'undefined' ? response.errors : null;

    // generic errors will be on a "message" key when success is false
    const genericError = (! success && typeof response.message !== 'undefined') ? response.message : null;

    if ( ! success) {
        if (craftError) {
            // add Craft’s error to the console
            console.error(intendedAction + ' failed:', craftError);
        } else if (apiErrors && apiErrors.length) {
            // add Cloudflare’s first (presumed only) API error to the console
            console.error(intendedAction + ' failed:', apiErrors[0].message);
        } else if (genericError) {
            console.error(intendedAction + ' failed:', genericError);
        }

        Craft.cp.displayError(Craft.t('cloudflare', intendedAction + ' failed.'));

        return false;
    }

    Craft.cp.displayNotice(Craft.t('cloudflare', intendedAction + ' successful.'));

    return true;
}
