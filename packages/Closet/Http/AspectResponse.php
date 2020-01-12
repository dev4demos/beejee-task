<?php

namespace Closet\Http;

interface AspectResponse
{
    /**
     * Set a header on the Response.
     *
     * @param  string  $key
     * @param  string  $value
     * @param  bool    $replace
     * @return $this
     */
    public function header($key, $value, $replace = true);

    /**
     * Create a new response instance.
     *
     * @param  string       $content
     * @param  int          $status
     * @param  array        $headers
     * @return Response
     */
    public function make($content = '', $status = 200, array $headers = array());

    /**
     * Create a new JSON response instance.
     *
     * @param  string|array     $data
     * @param  int  $status
     * @param  array            $headers
     * @param  int              $options
     * @return JsonResponse
     */
    public function json($data = array(), $status = 200, array $headers = array(), $options = 0);

    /**
     * Create a new redirect response to the given path.
     *
     * @param  string           $path
     * @param  int              $status
     * @param  array            $headers
     * @return RedirectResponse
     */
    public function redirectTo($path, $status = 302, $headers = array());
}
