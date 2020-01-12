<?php

namespace Closet\View;

use Latte\Engine;
use Latte\Macros\MacroSet;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * This class is a simple wrapper for Latte template engine which can be used with Slim PHP framework
 */
class LatteView
{
    private $latte;

    private $additionalParams = [];

    public function __construct(Engine $latte)
    {
        $this->latte = $latte;
    }

    /**
     * add template variables from assoc. array
     *
     * @param array $params
     */
    public function addParams(array $params)
    {
        $this->additionalParams = array_merge($this->additionalParams, $params);
    }

    /**
     * add template variable
     *
     * @param $name
     * @param $param
     */
    public function addParam($name, $param)
    {
        $this->additionalParams[$name] = $param;
    }

    /**
     * add Latte macro
     *
     * @param $name
     * @param callable $callback
     */
    public function addMacro($name, callable $callback)
    {
        $set = new MacroSet($this->latte->getCompiler());

        $set->addMacro($name, $callback);
    }

    /**
     * add Latte filter
     *
     * @param $title
     * @param callable $callback
     */
    public function addFilter($title, callable $callback)
    {
        $this->latte->addFilter($title, $callback);
    }

    /**
     * render the template
     *
     * @param $name
     * @param array $params
     * @return string
     */
    public function write($name, array $params = [])
    {
        $params = array_merge($this->additionalParams, $params);

        return $this->latte->renderToString($name, $params);
    }

    /**
     * render the template
     *
     * @param Response $response
     * @param $name
     * @param array $params
     * @return Response
     */
    public function render(Response $response, $name, array $params = [])
    {
        $response->getBody()->write(
            $this->write($name, $params)
        );

        return $response;
    }
}
