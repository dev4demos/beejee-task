<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class AbstractController
{
    protected $ioc;

    protected $request;

    protected $response;

    /**
     * The validator instance.
     *
     * @var Validator
     */
    protected $validator;

    protected $module;

    protected $viewExtension = 'latte';

    /**
     * That request input including uploaded files.
     *
     * @var array
     */
    protected $input = array();

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected $shared = array();

    public function __construct(ContainerInterface $ioc)
    {
        $this->ioc = $ioc;

        $this->request = $ioc['request'];

        $this->response = $ioc['response'];

        if ($lang = $this->ioc['session']->get('locale')) {
            $this->ioc['translator']->setLocale($lang);
        }

        $this->shareVars()
            ->share('controller', $this)
            ->share('_locale', $this->ioc['translator']->getLocale());
    }

    public function viewMake($name)
    {
        $data = func_get_args();

        $data = count($data) > 1 && is_array($data[1]) ? $data[1] : [];

        // dd(get_defined_vars(), $this->getShared());
        // dd($this->shared('id'), get_defined_vars());

        return $this->ioc['view']->render(
            $this->ioc['response'], $this->viewPath($name), $this->share($data)->getShared()
        );
    }

    public function viewPath($name)
    {
        $pieces = array_slice(func_get_args(), 1);

        $module = array_shift($pieces);

        $name = $name . '.' . ($this->viewExtension ?: 'latte');

        $module = $module ?: $this->module;

        if (!$module) {
            return $name;
        }

        $path = $module . '_' . $name;

        if (is_file($this->ioc['config']['view.path'] . '/' . $path)) {
            return $path;
        }

        $path = str_replace($module . '_', $module . '/', $path);

        return $path;
    }

    /**
     * Add a piece of shared data.
     *
     * @param  array|string  $key
     * @param  mixed|null    $value
     * @return self
     */
    public function share($key, $value = null)
    {
        foreach (is_array($key) ? $key : array($key => $value) as $key => $value) {
            empty($key) ?: $this->shared[$key] = $value;
        }

        return $this;
    }

    /**
     * Get an item from the shared data.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function shared($key, $default = null)
    {
        return array_key_exists($key, $this->shared) ? $this->shared[$key] : $default;
    }

    /**
     * Get all of the shared data for the environment.
     *
     * @return array
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * Add a piece of shared data.
     *
     * @param  array|string  $key
     * @param  mixed|null    $value
     * @return self
     */
    public function shareVars()
    {
        $request = $this->ioc['request'];

        $session = $this->ioc['session'];

        $translator = $this->ioc['translator'];

        $title = $request->segment(1) ?: 'Tasks';

        if ($request->segment(2)) {
            $title .= '-' . $request->segment(2);
        }

        $data = [
            '_title' => $translator->getFromJson($title),
            '_locale' => $translator->getLocale(),
            // '_errors' => $this->ioc['session']->get('errors', []),
            '_errors' => (array) $session->flash->get('errors', []),
            '_success' => (array) $session->flash->get('success', []),
            '_previousUrl' => $session->previousUrl(),
            // select distinct username from tasks
            '_usernameList' => [],
            '_task_statusList' => ['incomplete', 'reviewing', 'complete'],
        ];

        return $this->share($data);
    }

    public function customMessages()
    {
        $messages = [
            'required' => $this->ioc->trans('validation.required'),
            'email' => $this->ioc->trans('validation.email'),
        ];

        foreach ($messages as $rule => $message) {
            if (substr($message, 0, 11) == 'validation.') {
                unset($messages[$rule]);
            }
        }

        return $messages;
    }

    // ----------------------------------------------------------------------------------------
}
