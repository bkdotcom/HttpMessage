<?php

/**
 * This file is part of HttpMessage
 *
 * @package   bdk/http-message
 * @author    Brad Kent <bkfake-github@yahoo.com>
 * @license   http://opensource.org/licenses/MIT MIT
 * @copyright 2014-2022 Brad Kent
 * @version   v1.0
 */

namespace bdk\HttpMessage;

use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Assertions for Message, Request, ServerRequest, & Response
 */
trait AssertionTrait
{
    /**
     * Get the value's type
     *
     * @param mixed $value Value to inspect
     *
     * @return string
     */
    protected static function getTypeDebug($value)
    {
        return \is_object($value)
            ? \get_class($value)
            : \gettype($value);
    }

    /*
    	Message assertions
    */

    /**
     * Test valid header name
     *
     * @param string $name header name
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertHeaderName($name)
    {
        if (\is_string($name) === false && \is_numeric($name) === false) {
            throw new InvalidArgumentException(\sprintf(
                'Header name must be a string but %s provided.',
                self::getTypeDebug($name)
            ));
        }
        if ($name === '') {
            throw new InvalidArgumentException('Header name can not be empty.');
        }
        /*
            see https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.6
            alpha  => a-zA-Z
            digit  => 0-9
            others => !#$%&\'*+-.^_`|~
        */
        if (\preg_match('/^[a-zA-Z0-9!#$%&\'*+-.^_`|~]+$/', (string) $name) !== 1) {
            throw new InvalidArgumentException(\sprintf(
                '"%s" is not valid header name, it must be an RFC 7230 compatible string.',
                $name
            ));
        }
    }

    /**
     * Test valid header value
     *
     * @param array|string $value header value
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertHeaderValue($value)
    {
        if (\is_scalar($value) && !\is_bool($value)) {
            $value = array((string) $value);
        }
        if (\is_array($value) === false) {
            throw new InvalidArgumentException(\sprintf(
                'The header field value only accepts string and array, but %s provided.',
                self::getTypeDebug($value)
            ));
        }
        if (empty($value)) {
            throw new InvalidArgumentException(
                'Header value can not be empty array.'
            );
        }
        foreach ($value as $item) {
            $this->assertHeaderValueLine($item);
        }
    }

    /**
     * Validate header value
     *
     * @param mixed $value Header value to test
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function assertHeaderValueLine($value)
    {
        if ($value === '') {
            return;
        }
        if (\is_string($value) === false && \is_numeric($value) === false) {
            throw new InvalidArgumentException(\sprintf(
                'The header values only accept string and number, but %s provided.',
                self::getTypeDebug($value)
            ));
        }

        /*
            https://www.rfc-editor.org/rfc/rfc7230.txt (page.25)

            field-content = field-vchar [ 1*( SP / HTAB ) field-vchar ]
            field-vchar   = VCHAR / obs-text
            obs-text      = %x80-FF (character range outside ASCII.)
                             NOT ALLOWED
            SP            = space
            HTAB          = horizontal tab
            VCHAR         = any visible [USASCII] character. (x21-x7e)
        */
        if (\preg_match('/^[ \t\x21-\x7e]+$/', (string) $value) !== 1) {
            throw new InvalidArgumentException(\sprintf(
                '"%s" is not valid header value, it must contains visible ASCII characters only.',
                $value
            ));
        }
    }

    /**
     * Check out whether a protocol version number is supported.
     *
     * @param string $version HTTP protocol version.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function assertProtocolVersion($version)
    {
        if (\is_numeric($version) === false) {
            throw new InvalidArgumentException(\sprintf(
                'Unsupported HTTP protocol version number. %s provided.',
                self::getTypeDebug($version)
            ));
        }
        if (\in_array((string) $version, $this->validProtocolVers, true) === false) {
            throw new InvalidArgumentException(\sprintf(
                'Unsupported HTTP protocol version number. "%s" provided.',
                $version
            ));
        }
    }

    /*
		Request assertions
    */

    /**
     * Assert valid method
     *
     * @param string $method Http methods
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function assertMethod($method)
    {
        if (\is_string($method) === false) {
            throw new InvalidArgumentException(\sprintf(
                'HTTP method must be a string, but %s provided',
                self::getTypeDebug($method)
            ));
        }
        if ($method === '') {
            throw new InvalidArgumentException('Method must be a non-empty string.');
        }
        if (\preg_match('/^[a-z]+$/i', $method) !== 1) {
            throw new InvalidArgumentException('Method name must contain only ASCII alpha characters');
        }
    }

    /*
    	ServerRequest assertions
    */

    /**
     * Assert valid attribute name
     *
     * @param string $name  Attribute name
     * @param bool   $throw (false) Whether to throw InvalidArgumentException
     *
     * @return bool
     * @throws InvalidArgumentException if $throw === true
     */
    protected function assertAttributeName($name, $throw = true)
    {
        if (\is_string($name) || \is_numeric($name)) {
            return true;
        }
        if ($throw) {
            throw new InvalidArgumentException(\sprintf(
                'Attribute name must be a string, but %s provided.',
                self::getTypeDebug($name)
            ));
        }
        return false;
    }

    /**
     * Assert valid cookie parameters
     *
     * @param array $cookies Cookie parameters
     *
     * @return void
     * @throws InvalidArgumentException
     *
     * @see https://httpwg.org/http-extensions/draft-ietf-httpbis-rfc6265bis.html#name-syntax
     */
    protected function assertCookieParams($cookies)
    {
        $nameRegex = '/^[!#-+\--:<-[\]-~]+$/';
        \array_walk($cookies, function ($value, $name) use ($nameRegex) {
            if (\preg_match($nameRegex, $name) !== 1) {
                throw new InvalidArgumentException(\sprintf(
                    'Invalid cookie name specified: %s',
                    $name
                ));
            }
            if (\is_string($value) === false && \is_numeric($value) === false) {
                throw new InvalidArgumentException(\sprintf(
                    'Cookie value must be a string, but %s provided.',
                    self::getTypeDebug($value)
                ));
            }
        });
    }

    /**
     * Assert valid query parameters
     *
     * @param array $get Query parameters
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function assertQueryParams($get)
    {
        \array_walk_recursive($get, function ($value) {
            if (\is_string($value) === false) {
                throw new InvalidArgumentException(\sprintf(
                    'Query param value must be a string, but %s provided.',
                    self::getTypeDebug($value)
                ));
            }
        });
    }

    /**
     * Throw an exception if an unsupported argument type is provided.
     *
     * @param array|object|null $data The deserialized body data. This will
     *     typically be in an array or object.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function assertParsedBody($data)
    {
        if (
            $data === null ||
            \is_array($data) ||
            \is_object($data)
        ) {
            return;
        }
        throw new InvalidArgumentException(\sprintf(
            'ParsedBody must be array, object, or null, but %s provided.',
            self::getTypeDebug($data)
        ));
    }

    /**
     * Recursively validate the structure in an uploaded files array.
     *
     * @param array $uploadedFiles uploaded files tree
     *
     * @return void
     *
     * @throws InvalidArgumentException if any leaf is not an UploadedFileInterface instance.
     */
    protected function assertUploadedFiles($uploadedFiles)
    {
        \array_walk_recursive($uploadedFiles, function ($val) {
            if (!($val instanceof UploadedFileInterface)) {
                throw new InvalidArgumentException(\sprintf(
                    'Invalid file in uploaded files structure. Expected UploadedFileInterface, but %s provided',
                    self::getTypeDebug($val)
                ));
            }
        });
    }

    /*
    	Response assertions
    */

    /**
     * Validate reason phrase
     *
     * @param string $phrase Reason phrase to test
     *
     * @return void
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7230#section-3.1.2
     *
     * @throws InvalidArgumentException
     */
    protected function assertReasonPhrase($phrase)
    {
        if ($phrase === '') {
            return;
        }
        if (\is_string($phrase) === false) {
            throw new InvalidArgumentException(
                'Reason-phrase must be a string'
            );
        }
        // Don't allow control characters (incl \r & \n)
        if (\preg_match('#[^\P{C}\t]#u', $phrase, $matches, PREG_OFFSET_CAPTURE) === 1) {
            throw new InvalidArgumentException(\sprintf(
                'Reason phrase contains a prohibited character at position %s.',
                $matches[0][1]
            ));
        }
    }

    /**
     * Validate status code
     *
     * @param int $code Status Code
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function assertStatusCode($code)
    {
    	if (\is_string($code) && \preg_match('/^\d+$/', $code)) {
	        $code = (int) $code;
    	}
        if (\is_int($code) === false) {
            throw new InvalidArgumentException(\sprintf(
                'Status code must to be an integer, but %s provided',
				self::getTypeDebug($code)
            ));
        }
        if ($code < 100 || $code > 599) {
            throw new InvalidArgumentException(\sprintf(
                'Status code has to be an integer between 100 and 599. A status code of %d was given',
                $code
            ));
        }
    }
}
