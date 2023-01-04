"use strict";

/** global Craft */

const credentialSpinner = document.getElementById(
  "settings-credential-spinner"
);
const zoneSelect = document.getElementById("settings-zone-select");
const zoneSelectWrap = document.getElementById("settings-zone-id-select");
const zoneInputWrap = document.getElementById("settings-zone-id-input");
const zoneInputElement = document.getElementById("settings-zone-input");
const verifyContainer = document.querySelector(".cloudflare-verify");
const verifyCredentialsButton = document.getElementById("settings-cf-test");
const purgeUrlsButton = document.getElementById("settings-purge-urls");
const purgeUrlsField = document.getElementById("settings-urls");
const purgeAllButton = document.getElementById("settings-purge-all");

// widget purge URL pane toggle/heading
const purgeUrlsToggle = document.querySelector(
  ".purge-option.purge-individual-urls .heading"
);
const purgeUrlsFormWrap = document.querySelector(".purge-urls-form");

if (verifyCredentialsButton) {
  verifyCredentialsButton.addEventListener("click", (event) => {
    event.preventDefault();

    const settings = getAuthSettings();

    if (settings === false) {
      Craft.cp.displayError(
        Craft.t("cloudflare", "Please enter required API credentials.")
      );
      return;
    }

    showSpinner();
    checkCredentials(settings);
  });
}

if (purgeUrlsButton) {
  purgeUrlsButton.addEventListener("click", (event) => {
    event.preventDefault();
    purgeUrls(purgeUrlsField.value);
  });
}

if (purgeAllButton) {
  purgeAllButton.addEventListener("click", (event) => {
    event.preventDefault();
    purgeAll();
  });
}

if (purgeUrlsToggle) {
  purgeUrlsFormWrap.classList.add("hidden");

  purgeUrlsToggle.addEventListener("click", (event) => {
    const heading = event.target;

    purgeUrlsFormWrap.classList.toggle("hidden");

    setTimeout(() => {
      heading.classList.toggle("active");
    }, 100);
  });
}

function fetchZones() {
  const settings = getAuthSettings();
  const selectedZoneId = zoneSelect.querySelector("option:checked")
    ? zoneSelect.querySelector("option:checked").value
    : false;
  showSpinner();

  Craft.postActionRequest(
    "cloudflare/default/fetch-zones",
    settings,
    (response, statusText) => {
      hideSpinner();

      // check for errors
      if (statusText === "error" || !response) {
        Craft.cp.displayError(
          Craft.t("cloudflare", "Could not verify API credentials.")
        );

        verifyContainer.classList.remove("verified");
        verifyContainer.classList.add("failed");

        console.error(
          "Credential verification failed with response: ",
          response
        );

        return false;
      }

      // clear existing options
      Array.from(zoneSelect.querySelectorAll("option")).forEach((option) =>
        option.remove()
      );

      // append zone options from Cloudflare
      for (let i = 0; i < response.length; i++) {
        const row = response[i];
        const option = document.createElement("option");

        option.value = row.id;
        option.textContent = row.name;

        zoneSelect.appendChild(option);
      }

      // restore selection
      if (selectedZoneId) {
        zoneSelect.value = selectedZoneId;
      }

      if (response.length === 0) {
        // hide + disable menu, enable + display input
        zoneSelect.disabled = true;
        zoneSelectWrap.classList.add("hidden");
        zoneInputElement.disabled = false;
        zoneInputWrap.classList.remove("hidden");
      } else {
        // hide + disable input, enable + display menu
        zoneSelect.disabled = false;
        zoneSelectWrap.classList.remove("hidden");
        zoneInputElement.disabled = true;
        zoneInputWrap.classList.add("hidden");
      }

      verifyContainer.classList.remove("failed");
      verifyContainer.classList.add("verified");
    }
  );
}

function showSpinner() {
  verifyContainer.classList.remove("verified", "failed");
  verifyCredentialsButton.classList.add("loading");
}

function hideSpinner() {
  verifyCredentialsButton.classList.remove("loading");
}

function checkCredentials(settings) {
  Craft.sendActionRequest('POST', 'cloudflare/default/verify-connection', { data: settings })
    .then((response) => {
      hideSpinner();

      // if we succeeded, populate Cloudflare Zone options
      fetchZones();
    })
    .catch(({response}) => {
      // Handle non-2xx responses ...
      hideSpinner();
      verifyContainer.classList.remove("verified");
      verifyContainer.classList.add("failed");

      console.error(
          "Credential verification failed with response: ",
          response
      );

      if (typeof response.data.errors !== "undefined" && response.data.errors.length > 0) {
        // Fail credentials, skip louder error
        return false;
      }

      // Display louder error if we couldn’t even *check* the credentials
      Craft.cp.displayError(
        Craft.t("cloudflare", "Could not verify API credentials.")
      );

      return false;
    });
}

function getAuthSettings() {
  // fetch field references here since they may not have been available earlier
  const authTypeField = document.getElementById("settings-authType");
  const apiTokenField = document.getElementById("settings-apiToken");
  const apiKeyField = document.getElementById("settings-apiKey");
  const emailField = document.getElementById("settings-email");

  const authType = authTypeField.value || false;
  const apiToken = apiTokenField.value || false;
  const apiKey = apiKeyField.value || false;
  const email = emailField.value || false;

  // make sure required fields exist
  if (
    (authType === "key" && (!apiKey || !email)) ||
    (authType === "token" && !apiToken)
  ) {
    return false;
  }

  if (authType === "key") {
    return {
      authType,
      apiKey,
      email,
    };
  }

  if (authType === "token") {
    return {
      authType,
      apiToken,
    };
  }

  return false;
}

function purgeUrls(urls) {
  Craft.sendActionRequest('POST', 'cloudflare/default/purge-urls', { data: { urls: urls } })
    .then((response) => {
      const wasSuccessful = typeof response.data.success !== "undefined" && response.data.success;

      if (! wasSuccessful) {
        console.error("URL purge failed:", response);
        Craft.cp.displayError(Craft.t("cloudflare",  "URL purge failed."));
        return;
      }

      // empty the URL field
      purgeUrlsField.value = "";

      Craft.cp.displayNotice(
          Craft.t("cloudflare", "URL purge successful.")
      );
    })
    .catch(({response}) => {
      console.error("URL purge failed:", response);
      Craft.cp.displayError(Craft.t("cloudflare",  "URL purge failed."));
      return;
    });
}

function purgeAll() {
  if (
    confirm(
      Craft.t(
        "cloudflare",
        "You definitely want to purge the entire cache, right?"
      )
    )
  ) {
    Craft.sendActionRequest('POST', 'cloudflare/default/purge-all')
      .then((response) => {
        const wasSuccessful = typeof response.data.success !== "undefined" && response.data.success;

        if (! wasSuccessful) {
          console.error("Zone purge failed:", response);
          Craft.cp.displayError(Craft.t("cloudflare",  "Zone purge failed."));
          return;
        }

        Craft.cp.displayNotice(
            Craft.t("cloudflare", "Zone purge successful.")
        );
      });
  }
}
