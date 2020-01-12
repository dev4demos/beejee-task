<?php

namespace Closet\Http;

interface AspectRequest
{
    /**
     * Creates a new request with values from PHP's super globals. OK
     *
     * $_GET
     * $_POST
     * $_COOKIE
     * $_FILES
     * $_SERVER
     *
     * @return ServerRequestInterface
     */
    public static function createFromGlobals();

    /**
     * Get the request method. OK
     *
     * @return string
     */
    public function method();

    /**
     * Checks if the request method is of specified type. OK
     *
     * @param string $method Uppercase request method (GET, POST etc)
     *
     * @return bool
     */
    public function isMethod($method);

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function scheme();

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function userInfo();

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function host();

    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Port" header must contain the client port.
     *
     * If your reverse proxy uses a different header name than "X-Forwarded-Port",
     * configure it via via the $trustedHeaderSet argument of the
     * Request::setTrustedProxies() method instead.
     *
     * @return int|string can be a string if fetched from the server bag
     */
    public function port();

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    // public function authority();

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function path();

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function queryString();

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function fullUrl();

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url();

    /**
     * Generates a normalized URI (URL) for the Request.
     *
     * @return string A normalized URI (URL) for the Request
     */
    public function uri();

    /**
     * Returns the root URL from which this request is executed.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getBasePath(), except that it also includes the
     * script filename (e.g. index.php) if one exists.
     *
     * @return string The raw URL (i.e. not urldecoded)
     */
    public function getBaseUrl();

    /**
     * Get the root URL for the application.
     *
     * @return string
     */
    public function root();

    /**
     * Get all of the segments for the request path. OK
     *
     * @return array
     */
    public function segments();

    /**
     * Get a segment from the URI (1 based index). OK
     *
     * @param  int  $index
     * @param  string|null  $default
     * @return string|null
     */
    public function segment($index, $default = null);

    /**
     * Determine if the current request URI matches a pattern. OK
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function is();

    /**
     * Determine if the current request URL and query string matches a pattern. OK
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function fullUrlIs();

    /**
     * Determine if the request is the result of an AJAX call. OK
     *
     * @return bool
     */
    public function ajax();

    /**
     * Determine if the request is the result of an PJAX call.  OK
     *
     * @return bool
     */
    public function pjax();

    /**
     * Determine if the request contains a given input item key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function exists($key);

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($key);

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all();

    /**
     * Retrieve an input item from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string|array
     */
    public function input($key = null, $default = null);

    /**
     * Get a subset of the items from the input data.
     *
     * @param  array  $keys
     * @return array
     */
    public function only($keys);

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param  array  $keys
     * @return array
     */
    public function except($keys);

    /**
     * Retrieve a query string item from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string|array
     */
    public function query($key = null, $default = null);

    /**
     * Retrieve a server variable from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string|array
     */
    public function server($key = null, $default = null);

    /**
     * Retrieve a cookie from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string|array
     */
    public function cookie($key = null, $default = null);

    /**
     * Determine if a cookie is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasCookie($key);

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function files();

    /**
     * Retrieve a file from the request.
     * @param  string                                          $key
     * @param  mixed                                           $default
     * @return \Psr\Http\Message\UploadedFileInterface|array
     */
    public function file($key = null, $default = null);

    /**
     * Determine if the uploaded data contains a file.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasFile($key);

    /**
     * Retrieve a header from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string|array
     */
    public function header($key = null, $default = null);

    /**
     * Retrieve a header from the request.
     *
     * @param  string         $key
     * @param  mixed          $default
     * @return string
     */
    public function headerLine($key, $default = null);

    /**
     * Get the client IP address. OK
     *
     * @return string|null
     */
    public function ip();

    /**
     * Determine if the request is over HTTPS. OK
     *
     * @return bool
     */
    public function secure();

    /**
     * Get the client user agent. OK
     *
     * @return string
     */
    public function userAgent();

    /**
     * Get the current encoded path info for the request.
     * @return string
     */
    public function decodedPath();
}
