# Test Specification

This document outlines the test specification for the Cloudflare plugin.

---

## Feature Tests

### [UrlHelper](pest/Feature/UrlHelperTest.php)

_Tests the functionality of the URL helper._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Invalid URLs are removed.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Leading and trailing spaces are trimmed and duplicates removed from URLs.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Relative URLs are not purgeable.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) URLs within a zone are purgeable.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) URLs not within a zone are not purgeable.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) The base domain is correctly returned from a URL.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) A base domain that is not real is not returned from a URL.  
