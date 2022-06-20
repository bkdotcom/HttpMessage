# HttpMessage
PSR-7 (HttpMessage) Implementation

## Notable features

* Preserves "." and space in query params and "parsedBody" (POST)
* `UploadedFile::getClientFullPath()`` PHP 8.1 added a new file upload property (not included in PSR-7)
* Supported and tested on PHP 5.4 - 8.1

## Tests / Quality

![Supported PHP versions](https://img.shields.io/static/v1?label=PHP&message=5.4%20-%208.1&color=blue)
![Build Status](https://img.shields.io/github/workflow/status/bkdotcom/PHPDebugConsole/PHPUnit.svg?logo=github)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/bkdotcom/HttpMessage.svg?logo=codeclimate)](https://codeclimate.com/github/bkdotcom/HttpMessage)
[![Coverage](https://img.shields.io/codeclimate/coverage/bkdotcom/HttpMessage.svg?logo=codeclimate)](https://codeclimate.com/github/bkdotcom/HttpMessage)
