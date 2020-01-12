<?php

namespace Closet\Session;

use Rakit\Session\SessionManager;

class Store extends SessionManager
{
    /**
     * Checks if a key exists.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->has($key);
    }

    // public function get($key, $default = null)
    // {
    //     $value = parent::get($key, $default);

    //     if ($value === $default && ($flash = $this->flash)) {
    //         $value = $flash->get($key, $default);
    //         dd($this->flash);
    //     }

    //     return $value;
    // }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param  string|array  $key
     * @param  mixed       $value
     * @return void
     */
    public function put($key, $value = null)
    {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $arrayKey => $arrayValue) {
            $this->set($arrayKey, $arrayValue);
        }
    }

    /**
     * Remove all of the items from the session.
     *
     * @return void
     */
    public function clear()
    {
        return $this->remove();
    }

    /**
     * Remove one or many items from the session.
     *
     * @param  string|array  $keys
     * @return void
     */
    public function forget($keys)
    {
        foreach ((array) $keys as $key) {
            $this->remove($key);
        }
    }

    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    public function token()
    {
        return $this->get('_token');
    }

    /**
     * Get the previous URL from the session.
     *
     * @return string|null
     */
    public function previousUrl()
    {
        return $this->get('_previous.url');
    }

    /**
     * Set the "previous" URL in the session.
     *
     * @param  string  $url
     * @return void
     */
    public function setPreviousUrl($url)
    {
        return $this->put('_previous.url', $url);
    }

    // ------------------------------------------------------------------------------
    // compatable
    // ------------------------------------------------------------------------------

    /**
     * Remove all of the items from the session.
     *
     * @return void
     */
    public function flush()
    {
        $this->clear();
    }

    /**
     * Get the value of a given key and then forget it.
     *
     * @param  string  $key
     * @param  string  $default
     * @return mixed
     */
    public function pull($key)
    {
        return $this->remove($key);
    }

    /**
     * Push a value onto a session array.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key, array());

        $array[] = $value;

        $this->put($key, $array);
    }

    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token();
    }

    // ------------------------------------------------------------------------------
    // flash
    // ------------------------------------------------------------------------------

    /**
     * Flash a key / value pair to the session.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function flash($key, $value = true)
    {
        call_user_func_array([$this->flash, 'set'], func_get_args());

        return;

        // $this->put($key, $value);

        // $this->push('_flash.new', $key);

        // $this->removeFromOldFlashData(array($key));
    }

    /**
     * Reflash a subset of the current flash data.
     *
     * @param  array|mixed  $keys
     * @return void
     */
    public function keep($keys = null)
    {
        $this->mergeNewFlashes($keys = is_array($keys) ? $keys : func_get_args());

        $this->removeFromOldFlashData($keys);
    }

    /**
     * Merge new flash keys into the new flash array.
     *
     * @param  array  $keys
     * @return void
     */
    protected function mergeNewFlashes(array $keys)
    {
        $values = array_unique(array_merge($this->get('_flash.new', array()), $keys));

        $this->put('_flash.new', $values);
    }

    /**
     * Remove the given keys from the old flash data.
     *
     * @param  array  $keys
     * @return void
     */
    protected function removeFromOldFlashData(array $keys)
    {
        $this->put('_flash.old', array_diff($this->get('_flash.old', array()), $keys));
    }

    /**
     * Age the flash data for the session.
     *
     * @return void
     */
    public function ageFlashData()
    {
        $this->forget($this->get('_flash.old', array()));

        $this->put('_flash.old', $this->get('_flash.new', array()));

        $this->put('_flash.new', array());
    }

    /**
     * Flash an input array to the session.
     *
     * @param  array  $value
     * @return void
     */
    public function flashInput(array $value)
    {
        $this->flash('_old_input', $value);
    }

    /**
     * Get the requested item from the flashed input array.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function getOldInput($key = null, $default = null)
    {
        // $input = $this->get('_old_input', array());

        $input = $this->flash->get('_old_input', array());

        if (isset($input[$key])) {
            return $input[$key];
        } elseif (!array_key_exists($key, $input)) {
            return $default;
        }

        return $input;
    }

    /**
     * Determine if the session contains old input.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasOldInput($key = null)
    {
        $old = $this->getOldInput($key);

        return is_null($key) ? count($old) > 0 : !is_null($old);
    }

    // ------------------------------------------------------------------------------
    // helpers
    // ------------------------------------------------------------------------------

    /**
     * Regenerate the CSRF token value.
     *
     * @return self
     */
    public function regenerateToken()
    {
        $this->set('_token', $this->random(40));

        return $this;
    }

    /**
     * @param $key
     * @return string
     */
    protected function generateKeyUnique($key)
    {
        return $this->name . $key;
    }

    protected function random($length = 40)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length * 2);

            if ($bytes === false) {
                throw new \RuntimeException('Unable to generate random string.');
            }

            return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
        }

        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
}
