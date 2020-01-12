<?php

namespace Closet\Http;

class Response extends \GuzzleHttp\Psr7\Response implements AspectResponse
{
    /**
     * Convert response to string.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    public function __toString()
    {
        if (is_callable('parent::__toString')) {
            return parent::__toString();
        }

        $output = sprintf(
            'HTTP/%s %s %s', $this->getProtocolVersion(), $this->getStatusCode(), $this->getReasonPhrase()
        );
        $output .= "\r\n";
        foreach ($this->getHeaders() as $name => $values) {
            $output .= sprintf('%s: %s', $name, $this->getHeaderLine($name)) . "\r\n";
        }
        $output .= "\r\n";
        $output .= (string) $this->getBody();

        return $output;
    }

    /**
     * Set a header on the Response.
     *
     * @param  string  $key
     * @param  string  $value
     * @param  bool    $replace
     * @return $this
     */
    public function header($key, $value, $replace = true)
    {
        if ($replace) {
            return $this->withoutHeader($key)->withHeader($key, $value);
        }

        return $this->withHeader($key, $value);
    }

    /**
     * Create a new response instance.
     *
     * @param  string   $content
     * @param  int      $status
     * @param  array    $headers
     * @return $this
     */
    public function make($content = '', $status = 200, array $headers = array())
    {
        $message = new static;

        foreach ($headers as $key => $value) {
            $message = $message->withHeader($key, $value);
        }

        $message->getBody()->write($content);

        return $message->withStatus($status);
    }

    /**
     * Create a new JSON response instance.
     *
     * @param  mixed    $data
     * @param  int      $status
     * @param  array    $headers
     * @param  int      $options
     * @return $this
     */
    public function json($data = array(), $status = 200, array $headers = array(), $options = 0)
    {
        $key = 'Content-Type';

        echo $this->make(!$data ? '' : json_encode($data, $options), $status, $headers)
            ->withoutHeader($key)->withHeader($key, 'application/json');

        exit();
    }

    /**
     * Create a new redirect response to the given path.
     *
     * @param  string   $path
     * @param  int      $status
     * @param  array    $headers
     * @return $this
     */
    public function redirectTo($path, $status = 302, $headers = array())
    {
        if (empty($path)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $headers['Location'] = $path;

        $content = '<!DOCTYPE html><html><head><meta charset="UTF-8" /><meta http-equiv="refresh" content="0;url=%1$s" /><title>Redirecting to %1$s</title></head><body>Redirecting to <a href="%1$s">%1$s</a>.</body></html>';

        echo $this->make(sprintf($content, htmlspecialchars($path, ENT_QUOTES, 'UTF-8')), $status, $headers);

        exit;
    }

    /**
     * Updates the content and headers according to the JSON data and callback.
     *
     * @return $this
     */
    protected function update()
    {
        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $message = $this->withHeader('Content-Type', 'text/javascript');
        }

        // return $this->withHeader(sprintf('/**/%s(%s);', $this->callback, $this->data));
        return $this->withHeader(sprintf('/**/%s;', $this->callback));
    }

    /**
     * Sets the JSONP callback.
     *
     * @param string|null $callback The JSONP callback or null to use none
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When the callback name is not valid
     */
    public function setCallback($callback = null)
    {
        if (null !== $callback) {
            // partially taken from http://www.geekality.net/2011/08/03/valid-javascript-identifier/
            // partially taken from https://github.com/willdurand/JsonpCallbackValidator
            //      JsonpCallbackValidator is released under the MIT License. See https://github.com/willdurand/JsonpCallbackValidator/blob/v1.1.0/LICENSE for details.
            //      (c) William Durand <william.durand1@gmail.com>
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';
            $reserved = array(
                'break', 'do', 'instanceof', 'typeof', 'case', 'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 'for', 'switch', 'while',
                'debugger', 'function', 'this', 'with', 'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 'extends', 'super', 'const', 'export',
                'import', 'implements', 'let', 'private', 'public', 'yield', 'interface', 'package', 'protected', 'static', 'null', 'true', 'false',
            );

            $parts = explode('.', $callback);

            foreach ($parts as $part) {
                if (!preg_match($pattern, $part) || \in_array($part, $reserved, true)) {
                    throw new \InvalidArgumentException('The callback name is not valid.');
                }
            }
        }

        $this->callback = $callback;

        return $this->update();
    }
}
