<?php
namespace Craft;

class Cloudflare_QuickAccessWidget extends BaseWidget {
  public function getName()
  {
    return Craft::t('Quick Access to Cloudflare');
  }

  public function getBodyHtml()
  {
    $settings = craft()->cloudflare->settings;

    return craft()->templates->render('cloudflare/_widgets/quickaccess/body', [
      'settings' => $settings
    ]);
  }
}
