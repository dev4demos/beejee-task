<?php

namespace Closet\Http;

use GuzzleHttp\Psr7\ServerRequest;

class Request extends ServerRequest implements AspectRequest
{
    public static function createFromGlobals()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $headers = function_exists('getallheaders') ? getallheaders() : array();
        $uri = self::getUriFromGlobals();

        $body = new \GuzzleHttp\Psr7\LazyOpenStream('php://input', 'r+');
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';

        // The issue is solved here
        // We want an Instance of Closet\Http\Aspects\RequestAspect
        $serverRequest = new static($method, $uri, $headers, $body, $protocol, $_SERVER);

        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(self::normalizeFiles($_FILES));
    }

    /**
     * Get an input element from the request.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $input = $this->all())) {
            return $input[$key];
        }
        //
        elseif (array_key_exists($key, $input = $this->server())) {
            return $input[$key];
        }

        return call_user_func(array($this, $key));
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function method()
    {
        return $this->getMethod();
    }

    /**
     * Checks if the request method is of specified type.
     *
     * @param string $method Uppercase request method (GET, POST etc)
     *
     * @return bool
     */
    public function isMethod($method)
    {
        return strtoupper($this->getMethod()) === strtoupper($method);
    }

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
    public function scheme()
    {
        return $this->getUri()->getScheme();
    }

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
    public function userInfo()
    {
        return $this->getUri()->getUserInfo();
    }

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
    public function host()
    {
        return $this->getUri()->getHost();
    }

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
    public function port()
    {
        return $this->getUri()->getPort();
    }

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
    public function authority()
    {
        // userInfo+host+port
        return $this->getUri()->getAuthority();
    }

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
    public function path()
    {
        $pattern = trim($this->getUri()->getPath(), '/');

        return $pattern == '' ? '/' : $pattern;

        // return '/' . ltrim($this->getUri()->getPath(), '/');
    }

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
    public function queryString()
    {
        if (null !== $qs = $this->getUri()->getQuery()) {
            $qs = '?' . $qs;
        }

        return $qs;
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function fragmentString()
    {
        $qs = $this->getUri()->getFragment();

        return null === $qs ? '#' . $qs : '';
    }

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function fullUrl()
    {
        // Fragments never reach the server thus fullUrl = uri
        return (string) $this->getUri();

        // $path = '/' . ltrim($this->path(), '/');
        // return $this->scheme() . '://' . $this->authority() . $path . $this->queryString();
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->fullUrl()), '/');
    }

    /**
     * Generates a normalized URI (URL) for the Request.
     *
     * @return string A normalized URI (URL) for the Request
     *
     * @see getQueryString()
     */
    public function uri()
    {
        return (string) $this->getUri();
    }

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
    public function getBaseUrl()
    {
        return $this->segment(1);
    }

    /**
     * Get the root URL for the application.
     *
     * @return string
     */
    public function root()
    {
        return rtrim($this->getSchemeAndHttpHost() . $this->getBaseUrl(), '/');
    }

    /**
     * Get all of the segments for the request path.
     *
     * @return array
     */
    public function segments()
    {
        return array_values(array_filter(explode('/', $this->path()), function ($v) {return $v != '';}));
    }

    /**
     * Get a segment from the URI (1 based index).
     *
     * @param  int  $index
     * @param  string|null  $default
     * @return string|null
     */
    public function segment($index, $default = null)
    {
        return static::arrayGet($this->segments(), $index - 1, $default);
    }

    public function is()
    {
        $patterns = current(func_get_args());

        $decodedPath = urldecode($this->path());

        foreach (is_array($patterns) ? $patterns : func_get_args() as $pattern) {
            if (static::strIs($pattern, $decodedPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the current request URL and query string matches a pattern.
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function fullUrlIs()
    {
        $patterns = current(func_get_args());

        $decodedPath = urldecode($this->fullUrl());

        foreach (is_array($patterns) ? $patterns : func_get_args() as $pattern) {
            if (static::strIs($pattern, $decodedPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the request is the result of an AJAX call. OK
     *
     * @return bool
     */
    public function ajax()
    {
        return 'XMLHttpRequest' == current((array) $this->getHeader('X-Requested-With'));
    }

    /**
     * Determine if the request is the result of an PJAX call.
     *
     * @return bool
     */
    public function pjax()
    {
        return $this->hasHeader('X-PJAX') ? true : false;
    }

    /**
     * Determine if the request contains a given input item key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function exists($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        $input = $this->all();

        foreach ($keys as $value) {
            if (!array_key_exists($value, $input)) {
                return false;
            }

        }

        return true;
    }

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }

        }

        return true;
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all()
    {
        return array_replace_recursive($this->input(), $this->files());
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string|array
     */
    public function input($key = null, $default = null)
    {
        $input = (array) $this->getParsedBody() + $this->getQueryParams();

        if (is_null($key)) {
            return $input;
        }

        return isset($input[$key]) ? $input[$key] : $default;
    }

    /**
     * Get a subset of the items from the input data.
     *
     * @param  array  $keys
     * @return array
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = array();

        $input = $this->all();

        foreach ($keys as $key) {
            static::arraySet($results, $key, static::arrayGet($input, $key));
        }

        return $results;
    }

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param  array  $keys
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->all();

        static::arrayForget($results, $keys);

        return $results;
    }

    /**
     * Retrieve a query string item from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string|array
     */
    public function query($key = null, $default = null)
    {
        $input = $this->getQueryParams();

        return is_null($key) ? $input : (isset($input[$key]) ? $input[$key] : $default);
    }

    /**
     * Retrieve a server variable from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string|array
     */
    public function server($key = null, $default = null)
    {
        $input = $this->getServerParams();

        return is_null($key) ? $input : (isset($input[$key]) ? $input[$key] : $default);
    }

    /**
     * Retrieve a cookie from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string|array
     */
    public function cookie($key = null, $default = null)
    {
        if ($this->hasCookie($key)) {

            $params = $this->getCookieParams();

            return $params[$key];
        }

        return $default;
    }

    /**
     * Determine if a cookie is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasCookie($key)
    {
        return array_key_exists($key, $this->getCookieParams());
    }

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
    public function files()
    {
        return $this->getUploadedFiles();
    }

    /**
     * Retrieve a file from the request.
     * @param  string                                          $key
     * @param  mixed                                           $default
     * @return \Psr\Http\Message\UploadedFileInterface|array
     */
    public function file($key = null, $default = null)
    {
        $input = $this->files();

        return is_null($key) ? $input : (isset($input[$key]) ? $input[$key] : $default);
    }

    /**
     * Determine if the uploaded data contains a file.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasFile($key)
    {
        if (!is_array($files = $this->file($key))) {
            $files = array($files);
        }

        foreach ($files as $file) {
            if ($file instanceof \SplFileInfo && $file->getPath() != '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve a header from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string|array
     */
    public function header($key = null, $default = null)
    {
        return $this->getHeader($key) ?: $default;
    }

    /**
     * Retrieve a header from the request.
     *
     * @param  string         $key
     * @param  mixed          $default
     * @return string
     */
    public function headerLine($key, $default = null)
    {
        return $this->getHeaderLine($key) ?: $default;
    }

    /**
     * Get the client IP address.
     *
     * @return string|null
     */
    public function ip()
    {
        $keys = array('X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR');

        foreach ($keys as $key) {
            $params = $this->getServerParams();
            if (isset($params[$key])) {
                return $params[$key];
            }
        }

        return '127.0.0.1';
    }

    /**
     * Determine if the request is over HTTPS.
     *
     * @return bool
     */
    public function secure()
    {
        return strtolower($this->getUri()->getScheme()) === 'https' ? true : false;
    }

    /**
     * Get the client user agent.
     *
     * @return string
     */
    public function userAgent()
    {
        return $this->getHeaderLine('User-Agent');
    }

    /**
     * Get the current encoded path info for the request.
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    // ----------------------------------------------------------------------------------

    /**
     * Gets the scheme and HTTP host.
     *
     * If the URL was called with basic authentication, the user
     * and the password are not added to the generated string.
     *
     * @return string The scheme and HTTP host
     */
    public function getSchemeAndHttpHost()
    {
        return $this->scheme() . '://' . $this->host();
    }

    /**
     * Determine if the given input key is an empty string for "has".
     *
     * @param  string  $key
     * @return bool
     */
    protected function isEmptyString($key)
    {
        $boolOrArray = is_bool($this->input($key)) || is_array($this->input($key));

        return !$boolOrArray && trim((string) $this->input($key)) === '';
    }

    /**
     * Get the input source for the request.
     *
     * @return array
     */
    protected function getInputSource()
    {
        return $this->isMethod('GET') ? $this->getQueryParams() : (array) $this->getParsedBody();
    }

    // ----------------------------------------------------------------------------------
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function arrayGet(array $array, $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function arraySet(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Remove an array item from a given array using "dot" notation.
     *
     * @param  array   $array
     * @param  array  $keys
     * @return void
     */
    public static function arrayForget(&$array, $keys)
    {
        $original = &$array;

        foreach ((array) $keys as $key) {
            $parts = explode('.', $key);

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array = &$original;
        }
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string  $pattern
     * @param  string  $value
     * @return bool
     */
    public static function strIs($pattern, $value)
    {
        if ($pattern == $value) {
            return true;
        }

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern) . '\z';

        return (bool) preg_match('#^' . $pattern . '#', $value);
    }
}
