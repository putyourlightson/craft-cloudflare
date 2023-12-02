<?php

/**
* Tests the functionality of the URL helper.
*/

use putyourlightson\cloudflare\Cloudflare;
use putyourlightson\cloudflare\helpers\UrlHelper;

beforeEach(function() {
    Cloudflare::$plugin->settings->zoneName = 'cloudflare-plugin.test';
});

test('Invalid URLs are removed', function() {
    $urls = UrlHelper::prepUrls([
        '/no-domain-name',
        'not-a-url-at-all'
    ]);

    expect($urls)
        ->toBe([]);
});

test('Leading and trailing spaces are trimmed and duplicates removed from URLs', function() {
    $urls = UrlHelper::prepUrls([
        'cloudflare-plugin.test ',
        ' https://cloudflare-plugin.test  ',
        ' https://cloudflare-plugin.test/foo ',
        '  cloudflare-plugin.test/foo'
    ]);

    expect($urls)
        ->toBe([
            'https://cloudflare-plugin.test',
            'https://cloudflare-plugin.test/foo'
        ]);
});

test('Relative URLs are not purgeable', function() {
    expect(UrlHelper::isPurgeableUrl('cloudflare-plugin.test/should-work', true))
        ->toBeFalse();
});

test('URLs within a zone are purgeable', function() {
    $urls = [
        'https://cloudflare-plugin.test/should-work',
        'http://cloudflare-plugin.test/should-work',
        'https://www.cloudflare-plugin.test/should-work',
        'https://subdomain.cloudflare-plugin.test/should/also/work',
    ];

    foreach ($urls as $url) {
        expect(UrlHelper::isPurgeableUrl($url, true))
            ->toBeTrue();
    }
});

test('URLs not within a zone are not purgeable', function() {
    $urls = [
        'https://cloudflare.com/about-overview',
        'https://cloudflare.com/about-overview',
        'https://www.cloudflare.com/about-overview',
        'cloudflare.com/about-overview',
    ];

    foreach ($urls as $url) {
        expect(UrlHelper::isPurgeableUrl($url, true))
            ->toBeFalse();
    }
});

test('The base domain is correctly returned from a URL', function()
{
    $urls = [
        'snipcart.com' => 'https://snipcart.com/foo/bar',
        'example.org.au' => 'https://www.example.org.au/path/to/something',
        'foo.bar' => 'https://subdomain.foo.bar',
    ];

    foreach ($urls as $domain => $url) {
        expect(UrlHelper::getBaseDomainFromUrl($url))
            ->toBe($domain);
    }
});

test('A base domain that is not real is not returned from a URL', function()
{
    expect(UrlHelper::getBaseDomainFromUrl('www.nota.realdomain/foo/bar'))
        ->toBeNull();
});
