<?php

namespace bdk\Test\HttpMessage;

use stdClass;

trait DataProviderTrait
{
    /**
     * Generate a random string
     *
     * @param int $length (70) length between 1 and 70 inclusive
     *
     * @return string
     */
    public function randomBytes($length = 70)
    {
        $length = \min($length, 70);
        $length = \max($length, 1);
        return \sha1(
            \rand(0, 32000) . \microtime(true) .  \uniqid('', true),
            true // binary
        );
    }

    public function invalidProtocolVersions()
    {
        return [
            'nonNumeric' => ['a'],
            'null' => [null],
            'alpha 1' => ['1.a'],
            'alpha 2' => ['1.1 enhanced'],
            'alpha 3' => ['x1.5'],
            [1.],
            ['2.'],
            'nullChar' => ["\0"],
            'bool' => [false],
            'object' => [new stdClass()],
            'closure' => [static function () {}],
            'array' => [['2.0']],
        ];
    }

    public function validProtocolVersions()
    {
        return [
            ['0.9'],
            ['1.0'],
            ['1.1'],
            ['2.0'],
            ['2'],
            ['3'],
        ];
    }

    public function hostHeaderVariations()
    {
        return [
            'lowercase'         => ['host'],
            'mixed-1-2-3'       => ['HOSt'],
            'mixed-1-2-4'       => ['HOsT'],
            'mixed-1-2'         => ['HOst'],
            'mixed-1-3-4'       => ['HoST'],
            'mixed-1-3'         => ['HoSt'],
            'mixed-1-4'         => ['HosT'],
            'mixed-2-3'         => ['hOSt'],
            'mixed-2-4'         => ['hOsT'],
            'mixed-2'           => ['hOst'],
            'mixed-3-4'         => ['hoST'],
            'mixed-3'           => ['hoSt'],
            'mixed-4'           => ['hosT'],
            'reverse-titlecase' => ['hOST'],
            'titlecase'         => ['Host'],
            'uppercase'         => ['HOST'],
        ];
    }

    public function validHeaderNames()
    {
        return [
            'int' => [123],
            'numreic' => ['123'],
            ['Access-Control-Allow-Credentials'],
            ['Access-Control-Allow-Headers'],
            ['Access-Control-Allow-Methods'],
            ['Access-Control-Allow-Origin'],
            ['Access-Control-Expose-Headers'],
            ['Access-Control-Max-Age'],
            ['Accept-Ranges'],
            ['Age'],
            ['Allow'],
            ['Alternate-Protocol'],
            ['Cache-Control'],
            ['Client-Date'],
            ['Client-Peer'],
            ['Client-Response-Num'],
            ['Connection'],
            ['Content-Disposition'],
            ['Content-Encoding'],
            ['Content-Language'],
            ['Content-Length'],
            ['Content-Location'],
            ['Content-MD5'],
            ['Content-Range'],
            ['Content-Security-Policy'],
            ['Content-Security-Policy-Report-Only'],
            ['Content-Type'],
            ['Date'],
            ['ETag'],
            ['Expires'],
            ['HTTP'],
            ['Keep-Alive'],
            ['Last-Modified'],
            ['Link'],
            ['Location'],
            ['P3P'],
            ['Pragma'],
            ['Proxy-Authenticate'],
            ['Proxy-Connection'],
            ['Refresh'],
            ['Retry-After'],
            ['Server'],
            ['Set-Cookie'],
            ['Status'],
            ['Strict-Transport-Security'],
            ['Timing-Allow-Origin'],
            ['Trailer'],
            ['Transfer-Encoding'],
            ['Upgrade'],
            ['Vary'],
            ['Via'],
            ['Warning'],
            ['WWW-Authenticate'],
            ['X-Content-Type-Options'],
            ['X-Frame-Options'],
            ['X-Permitted-Cross-Domain-Policies'],
            ['X-Pingback'],
            ['X-Powered-By'],
            ['X-Robots-Tag'],
            ['X-UA-Compatible'],
            ['X-XSS-Protection'],
        ];
    }

    public function invalidHeaderNames()
    {
        $tests = [
            // 'int' => [233],
            // 'numeric' => ['123'],
            'array' => [['Header-Name']],
            'carriageReturn' => ["va\rlue"],
            'closure' => [static function () {}],
            'colon' => ['Location:'],
            'emptyString' => [''],
            'false' => [false],
            'linefeed' => ["va\nlue"],
            'non-ascii' => ['This-is-a-cyrillic-о'],
            'null' => [null],
            'object' => [new stdClass()],
            'space' => ['hey dude'],
            'true' => [true],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'true',
            ]));
        }
        return $tests;
    }

    public function validHeaderValues()
    {
        return [
            [1234],
            ['text/plain'],
            ['PHP 9.1'],
            ['text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8'],
            ['gzip, deflate, br'],
            ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36'],
        ];
    }

    public function invalidHeaderValues()
    {
        return [
            'closure' => [static function () {}],
            'cr' => ["value\rinjection"],
            'emptyArray' => [[]],
            'false' => [false],
            'lf' => ["value\ninjection"],
            'null' => [null],
            'object' => [new stdClass()],
            'true' => [true],
        ];
    }

    public function validRequestTargets()
    {
        return [
            'asterisk-form'         => ['*'],
            'authority-form'        => ['api.example.com'],
            'absolute-form'         => ['https://api.example.com/users'],
            'absolute-form-query'   => ['https://api.example.com/users?foo=bar'],
            'origin-form-path-only' => ['/users'],
            'origin-form'           => ['/users?id=foo'],
        ];
    }

    public function invalidRequestTargets()
    {
        return [
            'with-space'   => ['foo bar baz'],
            'invalid-type' => [12],
            'null'         => [null],
            'object'       => [new stdClass()],
            'newline'      => ["request\ntarget"],
            'tab'          => ["request\ttarget"],
        ];
    }

    public function urisWithRequestTargets()
    {
        return [
            ['http://foo.com/baz?bar=bam', '/baz?bar=bam'],
            ['http://example.com', '/'],
            ['http://example.com#proceed', '/'],
        ];
    }

    public function validRequestMethods()
    {
        return [
            'HEAD'      => ['HEAD'],
            'GET'       => ['GET'],
            'POST'      => ['POST'],
            'PUT'       => ['PUT'],
            'DELETE'    => ['DELETE'],
            'PATCH'     => ['PATCH'],
            'CONNECT'   => ['CONNECT'],
            'OPTIONS'   => ['OPTIONS'],
            'TRACE'     => ['TRACE'],
            'PROPFIND'  => ['PROPFIND'],
            'PROPPATCH' => ['PROPPATCH'],
            'MKCOL'     => ['MKCOL'],
            'COPY'      => ['COPY'],
            'MOVE'      => ['MOVE'],
            'LOCK'      => ['LOCK'],
            'UNLOCK'    => ['UNLOCK'],
        ];
    }

    public function invalidRequestMethods()
    {
        return [
            'emptyString'                => [''],
            'number'                     => [123],
            'numeric'                    => ['123'],
            'contains-space'             => ['hey dude'],
            'contains-special-character' => ['POST!'],
            'contains-numbers'           => ['GET1'],
            'null'                       => [null],
            'bool'                       => [true],
        ];
    }

    public function validUris()
    {
        return [
            ['urn:path-rootless'],
            ['urn:path:with:colon'],
            ['urn:/path-absolute'],
            ['urn:/'],
            ['urn:'],
            ['/'],
            ['relative/'],
            ['0'],
            [''],
            ['//example.org'],
            ['//example.org/'],
            ['//example.org?q#h'],
            ['?q'],
            ['?q=abc&foo=bar'],
            ['#fragment'],
            ['./foo/../bar'],
        ];
    }

    public function invalidUris()
    {
        return [
            'array' => [[]],
            'bogusScheme' => ['0scheme://host/path?query#fragment'],
            'closure' => [static function () {}],
            'false' => [false],
            'float' => [1.1],
            'host with colon' => ['urn://host:with:colon'],
            'int' => [1],
            'invalid port' => ['//example.com:10000000'],
            'object' => [new stdClass()],
            'scheme only' => ['http://'],
            // ['//example.com:0'], // @todo for whatever reason php is flaky about this
            // [null],
        ];
    }

    public function validUriSchemes()
    {
        $schemes = [
            '', // same as removing scheme
            'aaa',
            'aaas',
            'about',
            'acap',
            'acct',
            'acr',
            'adiumxtra',
            'afp',
            'afs',
            'aim',
            'appdata',
            'apt',
            'attachment',
            'aw',
            'barion',
            'beshare',
            'bitcoin',
            'bitcoincash',
            'blob',
            'bolo',
            'browserext',
            'callto',
            'cap',
            'chrome',
            'chrome-extension',
            'cid',
            'coap',
            'coap+tcp',
            'coap+ws',
            'coaps',
            'coaps+tcp',
            'coaps+ws',
            'com-eventbrite-attendee',
            'content',
            'conti',
            'crid',
            'cvs',
            'data',
            'dav',
            'diaspora',
            'dict',
            'did',
            'dis',
            'dlna-playcontainer',
            'dlna-playsingle',
            'dns',
            'dntp',
            'dtn',
            'dvb',
            'ed2k',
            'elsi',
            'example',
            'facetime',
            'fax',
            'feed',
            'feedready',
            'file',
            'filesystem',
            'finger',
            'fish',
            'ftp',
            'geo',
            'gg',
            'git',
            'gizmoproject',
            'go',
            'gopher',
            'graph',
            'gtalk',
            'h323',
            'ham',
            'hcap',
            'hcp',
            'http',
            'https',
            'hxxp',
            'hxxps',
            'hydrazone',
            'iax',
            'icap',
            'icon',
            'im',
            'imap',
            'info',
            'iotdisco',
            'ipn',
            'ipp',
            'ipps',
            'irc',
            'irc6',
            'ircs',
            'iris',
            'iris.beep',
            'iris.lwz',
            'iris.xpc',
            'iris.xpcs',
            'isostore',
            'itms',
            'jabber',
            'jar',
            'jms',
            'keyparc',
            'lastfm',
            'ldap',
            'ldaps',
            'lvlt',
            'magnet',
            'mailserver',
            'mailto',
            'maps',
            'market',
            'message',
            'microsoft.windows.camera',
            'microsoft.windows.camera.multipicker',
            'microsoft.windows.camera.picker',
            'mid',
            'mms',
            'modem',
            'mongodb',
            'moz',
            'ms-access',
            'ms-browser-extension',
            'ms-drive-to',
            'ms-enrollment',
            'ms-excel',
            'ms-eyecontrolspeech',
            'ms-gamebarservices',
            'ms-gamingoverlay',
            'ms-getoffice',
            'ms-help',
            'ms-infopath',
            'ms-inputapp',
            'ms-lockscreencomponent-config',
            'ms-media-stream-id',
            'ms-mixedrealitycapture',
            'ms-officeapp',
            'ms-people',
            'ms-project',
            'ms-powerpoint',
            'ms-publisher',
            'ms-restoretabcompanion',
            'ms-screenclip',
            'ms-screensketch',
            'ms-search',
            'ms-search-repair',
            'ms-secondary-screen-controller',
            'ms-secondary-screen-setup',
            'ms-settings',
            'ms-settings-airplanemode',
            'ms-settings-bluetooth',
            'ms-settings-camera',
            'ms-settings-cellular',
            'ms-settings-cloudstorage',
            'ms-settings-connectabledevices',
            'ms-settings-displays-topology',
            'ms-settings-emailandaccounts',
            'ms-settings-language',
            'ms-settings-location',
            'ms-settings-lock',
            'ms-settings-nfctransactions',
            'ms-settings-notifications',
            'ms-settings-power',
            'ms-settings-privacy',
            'ms-settings-proximity',
            'ms-settings-screenrotation',
            'ms-settings-wifi',
            'ms-settings-workplace',
            'ms-spd',
            'ms-sttoverlay',
            'ms-transit-to',
            'ms-useractivityset',
            'ms-virtualtouchpad',
            'ms-visio',
            'ms-walk-to',
            'ms-whiteboard',
            'ms-whiteboard-cmd',
            'ms-word',
            'msnim',
            'msrp',
            'msrps',
            'mtqp',
            'mumble',
            'mupdate',
            'mvn',
            'news',
            'nfs',
            'ni',
            'nih',
            'nntp',
            'notes',
            'ocf',
            'oid',
            'onenote',
            'onenote-cmd',
            'opaquelocktoken',
            'openpgp4fpr',
            'pack',
            'palm',
            'paparazzi',
            'pkcs11',
            'platform',
            'pop',
            'pres',
            'prospero',
            'proxy',
            'pwid',
            'psyc',
            'qb',
            'query',
            'redis',
            'rediss',
            'reload',
            'res',
            'resource',
            'rmi',
            'rsync',
            'rtmfp',
            'rtmp',
            'rtsp',
            'rtsps',
            'rtspu',
            'secondlife',
            'service',
            'session',
            'sftp',
            'sgn',
            'shttp',
            'sieve',
            'simpleledger',
            'sip',
            'sips',
            'skype',
            'smb',
            'sms',
            'smtp',
            'snews',
            'snmp',
            'soap.beep',
            'soap.beeps',
            'soldat',
            'spiffe',
            'spotify',
            'ssh',
            'steam',
            'stun',
            'stuns',
            'submit',
            'svn',
            'tag',
            'teamspeak',
            'tel',
            'teliaeid',
            'telnet',
            'tftp',
            'things',
            'thismessage',
            'tip',
            'tn3270',
            'tool',
            'turn',
            'turns',
            'tv',
            'udp',
            'unreal',
            'urn',
            'ut2004',
            'v-event',
            'vemmi',
            'ventrilo',
            'videotex',
            'vnc',
            'view-source',
            'wais',
            'webcal',
            'wpid',
            'ws',
            'wss',
            'wtai',
            'wyciwyg',
            'xcon',
            'xcon-userid',
            'xfire',
            'xmlrpc.beep',
            'xmlrpc.beeps',
            'xmpp',
            'xri',
            'ymsgr',
            'z39.50',
            'z39.50r',
            'z39.50s',
        ];
        return \array_map(function ($value) {
            return [$value];
        }, \array_combine($schemes, $schemes));
    }

    public function invalidUriSchemes()
    {
        return [
            'zero' => [0],
            'null' => [null],
            'float' => [7.4],
            'bool' => [true],
            'object' => [new stdClass()],
            'closure' => [static function () {}],
            'port' => [':80'],
            'string' => ['80 but not always'],
        ];
    }

    public function invalidUriUserInfos()
    {
        $tests = [
            'password closure' => ['user', new stdClass()],
            'password true' => ['user', true],
            'user int' => [0, null],
            'user null' => [null, null],
            'user object' => [new stdClass(), null],
            'user true' => [true, null],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'password true',
                'user int', // string typeHint coerces to "0"
                'user true',
            ]));
        }
        return $tests;
    }

    public function invalidUriHosts()
    {
        $tests = [
            'array' => [['example.com']],
            'closure' => [static function () {}],
            'float' => [7.4],
            'null' => [null],
            'object' => [new stdClass()],
            'true' => [true],
            'underscore' => ['example_test.com'],
            'zero' => [0],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'float',
                'true',
                'zero',
            ]));
        }
        return $tests;
    }

    public function invalidUriPorts()
    {
        $tests = [
            'false' => [false],
            'float' => [7.4],
            'max' => [PHP_INT_MAX],
            'min' => [PHP_INT_MIN],
            'negative' => [-2],
            'object' => [new stdClass()],
            'outOfRange 1' => [0xffff + 1],
            'outOfRange 2' => [\rand(0xffff + 1, 0xfffff)],
            'string' => ['80 but not always'],
            'true' => [true],
            'withColon' => [':80'],
            'zero' => [0],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'float',
                'true',
            ]));
        }
        return $tests;
    }

    public function invalidUriPaths()
    {
        $tests = [
            'false' => [true],
            'float' => [7.4],
            'null' => [null],
            'object' => [new stdClass()],
            'true' => [true],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'false',
                'float',
                'true',
            ]));
        }
        return $tests;
    }

    public function invalidUriQueries()
    {
        $tests = [
            'false' => [false],
            'null' => [null],
            'true' => [true],
            'object' => [new stdClass()],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'false',
                'true',
            ]));
        }
        return $tests;
    }

    public function invalidUriFragments()
    {
        $tests = [
            'array closure' => [[static function () {}]],
            'array empty' => [[]],
            'array obj' => [[new stdClass()]],
            'array path' => [['/path']],
            'closure' => [static function () {}],
            'false' => [false],
            'null' => [null],
            'object' => [new stdClass()],
            'true' => [true],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'false',
                'true',
            ]));
        }
        return $tests;
    }

    public function uriComponents()
    {
        $unreserved = 'a-zA-Z0-9.-_~!$&\'()*+,;=:@';
        return [
            // Percent encode spaces
            ['/pa th?q=va lue#frag ment', '/pa%20th', 'q=va%20lue', 'frag%20ment', '/pa%20th?q=va%20lue#frag%20ment'],
            // Percent encode multibyte
            ['/€?€#€', '/%E2%82%AC', '%E2%82%AC', '%E2%82%AC', '/%E2%82%AC?%E2%82%AC#%E2%82%AC'],
            // Don't encode something that's already encoded
            ['/pa%20th?q=va%20lue#frag%20ment', '/pa%20th', 'q=va%20lue', 'frag%20ment', '/pa%20th?q=va%20lue#frag%20ment'],
            // Percent encode invalid percent encodings
            ['/pa%2-th?q=va%2-lue#frag%2-ment', '/pa%252-th', 'q=va%252-lue', 'frag%252-ment', '/pa%252-th?q=va%252-lue#frag%252-ment'],
            // Don't encode path segments
            ['/pa/th//two?q=va/lue#frag/ment', '/pa/th//two', 'q=va/lue', 'frag/ment', '/pa/th//two?q=va/lue#frag/ment'],
            // Don't encode unreserved chars or sub-delimiters
            [
                '/' . $unreserved . '?' . $unreserved . '#' . $unreserved,
                '/' . $unreserved,
                $unreserved,
                $unreserved,
                '/' . $unreserved . '?' . $unreserved . '#' . $unreserved],
            // Encoded unreserved chars are not decoded
            ['/p%61th?q=v%61lue#fr%61gment', '/p%61th', 'q=v%61lue', 'fr%61gment', '/p%61th?q=v%61lue#fr%61gment'],
        ];
    }

    public function validQueryParams()
    {
        return [
            ['0='],
            ['&&&&a=example'],
            ['x=&y[]=2&y[xxx]=null&0=false'],
            ['x=&y[]=2&y[xxx]=null&0=false&[1]=23'],
            ['x=&y[][]=2&y[][1]=null&y[][][]=0&false=-1'],
        ];
    }

    public function validCookieParams()
    {
        return [
            [[]],
            [['a' => '1']],
            [['a' => 'value']],
        ];
    }

    public function validUploadedFiles()
    {
        return [
            [[]],
        ];
    }

    public function validParsedBodies()
    {
        return [
            [null],
            [[]],
            [new stdClass()],
        ];
    }

    public function validAttributeNamesAndValues()
    {
        return [
            'null' => ['name', null],
            'int' => ['name', 42],
            'array' => ['name', [1, 2, 3]],
            'false' => ['name', false],
            'true' => ['name', true],
            'float' => ['name', 3.14],
            'string' => ['name', 'string'],
            'object' => ['name', new stdClass()],
            'closure' => ['another name !', static function () {}],
        ];
    }

    public function invalidQueryParams()
    {
        return [
            'not array' => [new stdClass()],
            'null' => [['a' => null]],
            'int' => [['a' => 1]],
            'float' => [['a' => 1.1]],
            'bool' => [['a' => false]],
            'object' => [['a' => new stdClass()]],
            'closure' => [['x' => static function () {}]],
        ];
    }

    public function invalidCookieParams()
    {
        return [
            'not array' => [new stdClass()],
            'name empty' => [['' => 'value']],
            'name quote' => [['"a"' => 'value']],
            'value null' => [['a' => null]],
            // [['a' => 1]],
            // [['a' => 1.1]],
            // [['value']],
            'value bool' => [['a' => false]],
            'value object' => [['obj' => new stdClass()]],
            'value closure' => [['x' => static function () {}]],
        ];
    }

    public function invalidUploadedFiles()
    {
        return [
            'null' => [[null]],
            'string' => [['file']],
            'int' => [[1]],
            'float' => [[1.1]],
            'bool' => [[false]],
            'obj' => [[new stdClass()]],
            'closure' => [[static function () {}]],
        ];
    }

    public function invalidParsedBodies()
    {
        return [
            [1],
            [1.1],
            [false],
            ['value'],
        ];
    }

    public function invalidAttributeNamesAndValues()
    {
        $tests = [
            'null' => [null, 1],
            'false' => [false, null],
            'object' => [new stdClass(), 1],
            'closure' => [static function () {}, 'value'],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'false',
            ]));
        }
        return $tests;
    }

    public function invalidResources()
    {
        $name = tempnam(sys_get_temp_dir(), 'psr-7');
        return [
            // 'null'                => [ null ],
            'false'               => [ false ],
            'true'                => [ true ],
            'int'                 => [ 1 ],
            'float'               => [ 1.1 ],
            // 'string-non-resource' => [ 'foo-bar-baz' ],
            'array'               => [ [ \fopen($name, 'r+') ] ],
            'object'              => [ (object) [ 'resource' => fopen($name, 'r+') ] ],
        ];
    }

    public function allModes()
    {
        return [
            // mode readable writable
            ['a',   false,  true],
            ['a+',   true,  true],
            ['a+b',  true,  true],
            ['ab',  false,  true],
            ['c',   false,  true],
            ['c+',   true,  true],
            ['c+b',  true,  true],
            ['c+t',  true,  true],
            ['cb',  false,  true],
            ['r',    true, false],
            ['r+',   true,  true],
            ['r+b',  true,  true],
            ['r+t',  true,  true],
            ['rb',   true, false],
            ['rt',   true, false],
            ['rw',   true,  true],
            ['w',   false,  true],
            ['w+',   true,  true],
            ['w+b',  true,  true],
            ['w+t',  true,  true],
            ['wb',  false,  true],
            ['x',   false,  true],
            ['x+',   true,  true],
            ['x+b',  true,  true],
            ['x+t',  true,  true],
            ['xb',  false,  true],
        ];
    }

    public function nonReadableModes()
    {
        $nonReadable = array();
        foreach ($this->allModes() as $info) {
            if ($info[1] === false) {
                $nonReadable[] = [$info[0]];
            }
        }
        return $nonReadable;
    }

    public function nonWritableModes()
    {
        $nonWritable = array();
        foreach ($this->allModes() as $info) {
            if ($info[2] === false) {
                $nonWritable[] = [$info[0]];
            }
        }
        return $nonWritable;
    }

    public function statusPhrases()
    {
        return [
            ['500', '', 'Internal Server Error'],
            [103, '', ''],
            [200, "tab\ttab", "tab\ttab"],
        ];
    }

    public function invalidStatusCodes()
    {
        $tests = [
            'true'     => [true],
            'false'    => [false],
            'array'    => [[200]],
            'object'   => [(object) ['statusCode' => 200]],
            'too-low'  => [99],
            'float'    => [400.5],
            'too-high' => [600],
            'null'     => [null],
            'string'   => ['foo'],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'float',
            ]));
        }
        return $tests;
    }

    public function invalidReasonPhrases()
    {
        $tests = [
            'array'   => [[200]],
            'closure' => [static function () {}],
            'false'   => [false],
            'float'   => [400.5],
            'integer' => [99],
            'newline' => ["Custom reason phrase\n\rThe next line"],
            'object'  => [(object) ['reasonPhrase' => 'Ok']],
            'true'    => [true],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'false',
                'float',
                'integer',
                'true',
            ]));
        }
        return $tests;
    }

    /*
    public function headersWithInjectionVectors()
    {
        return [
            'name-with-cr'           => ["X-Foo\r-Bar", 'value'],
            'name-with-lf'           => ["X-Foo\n-Bar", 'value'],
            'name-with-crlf'         => ["X-Foo\r\n-Bar", 'value'],
            'name-with-2crlf'        => ["X-Foo\r\n\r\n-Bar", 'value'],
            'value-with-cr'          => ['X-Foo-Bar', "value\rinjection"],
            'value-with-lf'          => ['X-Foo-Bar', "value\ninjection"],
            'value-with-crlf'        => ['X-Foo-Bar', "value\r\ninjection"],
            'value-with-2crlf'       => ['X-Foo-Bar', "value\r\n\r\ninjection"],
            'array-value-with-cr'    => ['X-Foo-Bar', ["value\rinjection"]],
            'array-value-with-lf'    => ['X-Foo-Bar', ["value\ninjection"]],
            'array-value-with-crlf'  => ['X-Foo-Bar', ["value\r\ninjection"]],
            'array-value-with-2crlf' => ['X-Foo-Bar', ["value\r\n\r\ninjection"]],
        ];
    }
    */

    public function invalidStreams()
    {
        return [
            'array'  => [['filename']],
            'closure' => [static function () {}],
            'false'  => [false],
            'float'  => [1.1],
            'int'    => [1],
            'null'   => [null],
            'object' => [(object) ['filename']],
            'true'   => [true],
        ];
    }

    public function invalidTargetPaths()
    {
        $tests = [
            'array'  => [['filename']],
            'closure' => [static function () {}],
            'empty'  => [''],
            'false'  => [false],
            'float'  => [1.1],
            'int'    => [1],
            'null'   => [null],
            'object' => [(object) ['filename']],
            'true'   => [true],
        ];
        if (self::hasParamTypes()) {
            $tests = \array_diff_key($tests, \array_flip([
                'float',
                'int',
                'true',
            ]));
        }
        return $tests;
    }

    public function invalidFileSizes()
    {
        return [
            'negative' => [-1],
        ];
    }

    public function invalidFileNames()
    {
        return [
            'directory-separator' => ['this/is/not/valid'],
            '0-char'              => ["this is \0 not good either"],
        ];
    }

    public function validMediaTypes()
    {
        return [
            ['application/epub+zip'],
            ['application/java-archive'],
            ['application/octet-stream'],
            ['application/octet-stream'],
            ['application/vnd.amazon.ebook'],
            ['application/vnd.apple.installer+xml'],
            ['application/vnd.ms-excel'],
            ['application/vnd.ms-fontobject'],
            ['application/vnd.ms-powerpoint'],
            ['application/vnd.oasis.opendocument.presentation'],
            ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['application/vnd.visio'],
            ['application/x-abiword'],
            ['application/x-rar-compressed'],
            ['application/x-sh'],
            ['application/x-shockwave-flash'],
            ['application/xhtml+xml'],
            ['audio/aac'],
            ['audio/midi'],
            ['audio/x-midi'],
            ['font/woff'],
            ['font/woff2'],
            ['image/svg+xml'],
            ['text/html; charset=UTF-8'],
            ['text/plain'],
            ['video/mpeg'],
            ['video/x-msvideo'],
        ];
    }

    public function invalidMediaTypes()
    {
        return [
            'array'  => [['filename']],
            'backslash' => ['test\\test'],
            'closure' => [static function () {}],
            'false'  => [false],
            'float'  => [1.1],
            'int'    => [1],
            'invalidParam1' => ['text/html charset=UTF-8'],
            'invalidParam2' => ['text/html; char-set=UTF-8'],
            'invalidSubType' => ['text/bogus/subtype'],
            'invalidType' => ['some+monster+media+type/here'],
            'moreThanOneSuffix' => ['text/html+foo+bar'],
            'object' => [(object) ['filename']],
            'true'   => [true],
        ];
    }

    public function invalidFileUploadErrorStatuses()
    {
        return [
            [-1],
            [74],
            [10000],
            [PHP_INT_MIN],
            [PHP_INT_MAX],
        ];
    }

    public function fileUploadErrorCodes()
    {
        return [
            [UPLOAD_ERR_INI_SIZE],
            [UPLOAD_ERR_FORM_SIZE],
            [UPLOAD_ERR_PARTIAL],
            [UPLOAD_ERR_NO_FILE],
            [UPLOAD_ERR_NO_TMP_DIR],
            [UPLOAD_ERR_CANT_WRITE],
            [UPLOAD_ERR_EXTENSION],
        ];
    }
}
