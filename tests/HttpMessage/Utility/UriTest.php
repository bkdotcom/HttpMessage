<?php

namespace bdk\Test\HttpMessage\Utility;

use bdk\HttpMessage\Uri;
use bdk\HttpMessage\Utility\Uri as UriUtils;
use PHPUnit\Framework\TestCase;

/**
 * @covers bdk\HttpMessage\Uri
 * @covers bdk\HttpMessage\Utility\Uri
 *
 * @phpcs:disable SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder
 */
class UriTest extends TestCase
{
    const RFC3986_BASE = 'http://a/b/c/d;p?q';

    /**
     * @dataProvider providerFromGlobals
     */
    public function testFromGlobals(array $serverVars, array $getVars, $expectUriString)
    {
        $serverBackup = $_SERVER;
        $getBackup = $_GET;

        $_SERVER = $serverVars;
        $_GET = $getVars;
        self::assertSame($expectUriString, (string) Uri::fromGlobals());

        $_SERVER = $serverBackup;
        $_GET = $getBackup;
    }

    /**
     * @dataProvider providerFromParsed
     */
    public function testFromParsed(array $parsed, $expectUriString)
    {
        $uri = UriUtils::fromParsed($parsed);
        self::assertSame($expectUriString, (string) $uri);
    }

    /**
     * @dataProvider providerIsCrossOrigin
     */
    public function testIsCrossOrigin($uri1, $uri2, $expect)
    {
        self::assertSame($expect, UriUtils::isCrossOrigin(new Uri($uri1), new Uri($uri2)));
    }

    /**
     * @dataProvider providerParseUrl
     */
    public function testParseUrl($url, $expect)
    {
        $previousLcType = \setlocale(LC_CTYPE, '0');
        \setlocale(LC_CTYPE, 'en_GB');

        $parts = UriUtils::parseUrl($url);

        \setlocale(LC_CTYPE, $previousLcType);

        self::assertSame($expect, $parts);
    }

    /**
     * @dataProvider providerResolve
     */
    public function testResolveUri($base, $rel, $expect)
    {
        $base = new Uri($base);
        $rel = new Uri($rel);
        $targetUri = UriUtils::resolve($base, $rel);

        self::assertInstanceOf('Psr\\Http\\Message\\UriInterface', $targetUri);
        self::assertSame($expect, (string) $targetUri);
        // This ensures there are no test cases that only work in the resolve() direction but not the
        // opposite via relativize(). This can happen when both base and rel URI are relative-path
        // references resulting in another relative-path URI.
        self::assertSame($expect, (string) UriUtils::resolve($base, $targetUri));
    }

    public static function providerFromGlobals()
    {
        $serverCommon = array(
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_HOST' => 'www.test.com:8080',
            'PHP_AUTH_PW' => '1234',
            'PHP_AUTH_USER' => 'billybob',
            'QUERY_STRING' => 'used=only_if_no_REQUEST_URI',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_TIME_FLOAT' => $_SERVER['REQUEST_TIME_FLOAT'],
            'REQUEST_URI' => '/path?ding=dong',
            'SCRIPT_NAME' => isset($_SERVER['SCRIPT_NAME'])
                ? $_SERVER['SCRIPT_NAME']
                : null,
        );

        return array(
            array(
                $serverCommon,
                array(
                    'used' => 'only after REQUEST_URI and QUERY_STRING',
                ),
                'http://www.test.com:8080/path?ding=dong',
            ),
            array(
                \array_merge($serverCommon, array(
                    'HTTPS' => 'on',
                )),
                array(),
                'https://www.test.com:8080/path?ding=dong',
            ),
            array(
                array(
                    'REQUEST_METHOD' => 'GET',
                    'SERVER_NAME' => 'somedomain',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => 'ding=dong',
                ),
                array(),
                'http://somedomain:8080/?ding=dong',
            ),
            array(
                array(
                    'REQUEST_METHOD' => 'GET',
                    'SERVER_ADDR' => '192.168.100.42',
                    'SERVER_PORT' => '8080',
                ),
                array(
                    'foo' => 'bar',
                ),
                'http://192.168.100.42:8080/?foo=bar',
            ),
        );
    }

    public static function providerFromParsed()
    {
        return array(
            'usernamePassword' => array(
                array(
                    'scheme' => 'http',
                    'host' => 'example.com',
                    'username' => 'user',
                    'password' => 'pass',
                ),
                'http://user:pass@example.com',
            ),
            'userInfoArray' => array(
                array(
                    'scheme' => 'http',
                    'host' => 'example.com',
                    'user' => 'ignoredUser',
                    'pass' => 'ignoredPass',
                    'userInfo' => ['user', 'pass'],
                ),
                'http://user:pass@example.com',
            ),
            'userInfoString' => array(
                array(
                    'scheme' => 'http',
                    'host' => 'example.com',
                    'username' => 'ignoredUser',
                    'password' => 'ignoredPass',
                    'userInfo' => 'user:pass',
                ),
                'http://user:pass@example.com',
            ),
            'zeros' => array(
                array(
                    'fragment' => '0',
                    'host' => 'example.com',
                    'pass' => '0',
                    'path' => '0',
                    'port' => 1234,
                    'query' => 0,
                    'scheme' => 'http',
                    'user' => 0,
                ),
                'http://0:0@example.com:1234/0?0#0'
            ),
        );
    }

    public static function providerIsCrossOrigin()
    {
        return [
            ['http://example.com/123', 'http://example.com/', false],
            ['http://example.com/123', 'http://example.com:80/', false],
            ['http://example.com:80/123', 'http://example.com/', false],
            ['http://example.com:80/123', 'http://example.com:80/', false],
            ['http://example.com/123', 'https://example.com/', true],
            ['http://example.com/123', 'http://www.example.com/', true],
            ['http://example.com/123', 'http://example.com:81/', true],
            ['http://example.com:80/123', 'http://example.com:81/', true],
            ['https://example.com/123', 'https://example.com/', false],
            ['https://example.com/123', 'https://example.com:443/', false],
            ['https://example.com:443/123', 'https://example.com/', false],
            ['https://example.com:443/123', 'https://example.com:443/', false],
            ['https://example.com/123', 'http://example.com/', true],
            ['https://example.com/123', 'https://www.example.com/', true],
            ['https://example.com/123', 'https://example.com:444/', true],
            ['https://example.com:443/123', 'https://example.com:444/', true],
        ];
    }

    public static function providerParseUrl()
    {
        // chars from parseUrl with %" \
        $chars = '!*\'();:@&=$,/?#[]%" \\';
        return array(
            'basic' => array('https://user:pass@example.com:80/path/È/💩/page.html?foo=bar&zip=zap#fragment', array(
                'fragment' => 'fragment',
                'host' => 'example.com',
                'pass' => 'pass',
                'path' => '/path/È/💩/page.html',
                'port' => 80,
                'query' => 'foo=bar&zip=zap',
                'scheme' => 'https',
                'user' => 'user',
            )),
            // ensure we don't filter out '0' values
            'zeros' => array('https://0:0@example.com:80/0?0#0', array(
                'fragment' => '0',
                'host' => 'example.com',
                'pass' => '0',
                'path' => '/0',
                'port' => 80,
                'query' => '0',
                'scheme' => 'https',
                'user' => '0',
            )),
            'encodedChars' => array('http://mydomain.com/' . \rawurlencode($chars), array(
                'host' => 'mydomain.com',
                'path' => '/' . \rawurlencode($chars),
                'scheme' => 'http',
            )),
            'invalid' => array('http:///example.com', false),
            'mixed' => array('/%È21%25È3*%(', array(
                'path' => '/%È21%25È3*%(',
            )),
            'specialChars' => array('//example.com/' . $chars, array(
                'fragment' => '[]%" \\',
                'host' => 'example.com',
                'path' => '/' . \substr($chars, 0, \strpos($chars, '?')),
                'query' => '',
            )),
            'uriInterface' => array(
                new Uri('//example.com/'),
                array(
                    'host' => 'example.com',
                    'path' => '/',
                ),
            ),
            'uriInterface2' => array(
                new Uri('http://foo:bar@example.com:8080/path?zip=zap#frag'),
                array(
                    'fragment' => 'frag',
                    'host' => 'example.com',
                    'pass' => 'bar',
                    'path' => '/path',
                    'port' => 8080,
                    'query' => 'zip=zap',
                    'scheme' => 'http',
                    'user' => 'foo',
                ),
            ),
        );
    }

    public static function providerResolve()
    {
        return [
            [self::RFC3986_BASE, 'g:h',           'g:h'],
            [self::RFC3986_BASE, 'g',             'http://a/b/c/g'],
            [self::RFC3986_BASE, './g',           'http://a/b/c/g'],
            [self::RFC3986_BASE, 'g/',            'http://a/b/c/g/'],
            [self::RFC3986_BASE, '/g',            'http://a/g'],
            [self::RFC3986_BASE, '//g',           'http://g'],
            [self::RFC3986_BASE, '?y',            'http://a/b/c/d;p?y'],
            [self::RFC3986_BASE, 'g?y',           'http://a/b/c/g?y'],
            [self::RFC3986_BASE, '#s',            'http://a/b/c/d;p?q#s'],
            [self::RFC3986_BASE, 'g#s',           'http://a/b/c/g#s'],
            [self::RFC3986_BASE, 'g?y#s',         'http://a/b/c/g?y#s'],
            [self::RFC3986_BASE, ';x',            'http://a/b/c/;x'],
            [self::RFC3986_BASE, 'g;x',           'http://a/b/c/g;x'],
            [self::RFC3986_BASE, 'g;x?y#s',       'http://a/b/c/g;x?y#s'],
            [self::RFC3986_BASE, '',              self::RFC3986_BASE],
            [self::RFC3986_BASE, '.',             'http://a/b/c/'],
            [self::RFC3986_BASE, './',            'http://a/b/c/'],
            [self::RFC3986_BASE, '..',            'http://a/b/'],
            [self::RFC3986_BASE, '../',           'http://a/b/'],
            [self::RFC3986_BASE, '../g',          'http://a/b/g'],
            [self::RFC3986_BASE, '../..',         'http://a/'],
            [self::RFC3986_BASE, '../../',        'http://a/'],
            [self::RFC3986_BASE, '../../g',       'http://a/g'],
            [self::RFC3986_BASE, '../../../g',    'http://a/g'],
            [self::RFC3986_BASE, '../../../../g', 'http://a/g'],
            [self::RFC3986_BASE, '/./g',          'http://a/g'],
            [self::RFC3986_BASE, '/../g',         'http://a/g'],
            [self::RFC3986_BASE, 'g.',            'http://a/b/c/g.'],
            [self::RFC3986_BASE, '.g',            'http://a/b/c/.g'],
            [self::RFC3986_BASE, 'g..',           'http://a/b/c/g..'],
            [self::RFC3986_BASE, '..g',           'http://a/b/c/..g'],
            [self::RFC3986_BASE, './../g',        'http://a/b/g'],
            [self::RFC3986_BASE, 'foo////g',      'http://a/b/c/foo////g'],
            [self::RFC3986_BASE, './g/.',         'http://a/b/c/g/'],
            [self::RFC3986_BASE, 'g/./h',         'http://a/b/c/g/h'],
            [self::RFC3986_BASE, 'g/../h',        'http://a/b/c/h'],
            [self::RFC3986_BASE, 'g;x=1/./y',     'http://a/b/c/g;x=1/y'],
            [self::RFC3986_BASE, 'g;x=1/../y',    'http://a/b/c/y'],
            // dot-segments in the query or fragment
            [self::RFC3986_BASE, 'g?y/./x',       'http://a/b/c/g?y/./x'],
            [self::RFC3986_BASE, 'g?y/../x',      'http://a/b/c/g?y/../x'],
            [self::RFC3986_BASE, 'g#s/./x',       'http://a/b/c/g#s/./x'],
            [self::RFC3986_BASE, 'g#s/../x',      'http://a/b/c/g#s/../x'],
            [self::RFC3986_BASE, 'g#s/../x',      'http://a/b/c/g#s/../x'],
            [self::RFC3986_BASE, '?y#s',          'http://a/b/c/d;p?y#s'],
            // base with fragment
            ['http://a/b/c?q#s', '?y',            'http://a/b/c?y'],
            // base with user info
            ['http://u@a/b/c/d;p?q', '.',         'http://u@a/b/c/'],
            ['http://u:p@a/b/c/d;p?q', '.',       'http://u:p@a/b/c/'],
            // path ending with slash or no slash at all
            ['http://a/b/c/d/',  'e',             'http://a/b/c/d/e'],
            ['urn:no-slash',     'e',             'urn:e'],
            // path ending without slash and multi-segment relative part
            ['http://a/b/c',     'd/e',           'http://a/b/d/e'],
            // falsey relative parts
            [self::RFC3986_BASE, '//0',           'http://0'],
            [self::RFC3986_BASE, '0',             'http://a/b/c/0'],
            [self::RFC3986_BASE, '?0',            'http://a/b/c/d;p?0'],
            [self::RFC3986_BASE, '#0',            'http://a/b/c/d;p?q#0'],
            // absolute path base URI
            ['/a/b/',            '',              '/a/b/'],
            ['/a/b',             '',              '/a/b'],
            ['/',                'a',             '/a'],
            ['/',                'a/b',           '/a/b'],
            ['/a/b',             'g',             '/a/g'],
            ['/a/b/c',           './',            '/a/b/'],
            ['/a/b/',            '../',           '/a/'],
            ['/a/b/c',           '../',           '/a/'],
            ['/a/b/',            '../../x/y/z/',  '/x/y/z/'],
            ['/a/b/c/d/e',       '../../../c/d',  '/a/c/d'],
            ['/a/b/c//',         '../',           '/a/b/c/'],
            ['/a/b/c/',          './/',           '/a/b/c//'],
            ['/a/b/c',           '../../../../a', '/a'],
            ['/a/b/c',           '../../../..',   '/'],
            // not actually a dot-segment
            ['/a/b/c',           '..a/b..',           '/a/b/..a/b..'],
            // '' cannot be used as relative reference as it would inherit the base query component
            ['/a/b?q',           'b',             '/a/b'],
            ['/a/b/?q',          './',            '/a/b/'],
            // path with colon: "with:colon" would be the wrong relative reference
            ['/a/',              './with:colon',  '/a/with:colon'],
            ['/a/',              'b/with:colon',  '/a/b/with:colon'],
            ['/a/',              './:b/',         '/a/:b/'],
            // relative path references
            ['a',               'a/b',            'a/b'],
            ['',                 '',              ''],
            ['',                 '..',            ''],
            ['/',                '..',            '/'],
            ['urn:a/b',          '..//a/b',       'urn:/a/b'],
            // network path references
            // empty base path and relative-path reference
            ['//example.com',    'a',             '//example.com/a'],
            // path starting with two slashes
            ['//example.com//two-slashes', './',  '//example.com//'],
            ['//example.com',    './/',           '//example.com//'],
            ['//example.com/',   './/',           '//example.com//'],
            // base URI has less components than relative URI
            ['/',                '//a/b/c/../?q#h',     '//a/b/?q#h'],
            ['/',                'urn:/',         'urn:/'],
        ];
    }
}
