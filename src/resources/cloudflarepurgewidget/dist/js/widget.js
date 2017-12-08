'use strict';

/*
 global window,
 Craft
 */

const purgeIndividualUrlsButton = document.querySelector('.purge-option.purge-individual-urls .heading'),
      purgeUrlsForm             = document.querySelector('.purge-urls-form'),
      purgeAllButton            = document.querySelector('.purge-option.purge-all .heading');

purgeUrlsForm.style.height = purgeUrlsForm.clientHeight + 'px';
purgeUrlsForm.classList.add('hidden');

purgeIndividualUrlsButton.addEventListener('click', event => {
  const heading = event.target;

  purgeUrlsForm.classList.toggle('hidden');

  setTimeout(() => {
    heading.classList.toggle('active');
  }, 100);
});

purgeAllButton.addEventListener('click', event => {
  if (confirm(window.__CLOUDFLARE_PLUGIN.messages.purgeAllUrlsConfirmation)) {
    window.location.href = window.__CLOUDFLARE_PLUGIN.actions.purgeAll;
  }
});
