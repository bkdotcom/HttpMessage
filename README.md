# HttpMessage
PSR-7 (HttpMessage) Implementation

## Notable features

* Supported and tested on PHP 5.4 - 8.3
* Preserves "." and space in query params (GET) and "parsedBody" (POST) keys.
* `UploadedFile::getClientFullPath()`.  PHP 8.1 added a new file upload property (not included in PSR-7)

### Utilties
* HttpFoundationBridge: create ServerRequeset and Response from HttpFoundation request and response
* ContentType: common mime-type constants
* ParseStr: parse URL-encoded string (query / post-body) without converting "." and " " (spaces) in keys
* Response: `emit`; `codePhrase()` - get standard code phrase for given HTTP status code
* ServerRequest: `fromGlobals()`
* Uri: `fromGlobals()`; `parseUrl()`; `isCrossOrigin()`; `resolve()`

## Tests / Quality

![Supported PHP versions](https://img.shields.io/static/v1?label=PHP&message=5.4%20-%208.3&color=blue)
![Build Status](https://img.shields.io/github/actions/workflow/status/bkdotcom/HttpMessage/phpunit.yml.svg?logo=github)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/bkdotcom/HttpMessage.svg?logo=codeclimate)](https://codeclimate.com/github/bkdotcom/HttpMessage)
[![Coverage](https://img.shields.io/codeclimate/coverage/bkdotcom/HttpMessage.svg?logo=codeclimate)](https://codeclimate.com/github/bkdotcom/HttpMessage)
