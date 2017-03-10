<?php

namespace TaskFlux;

class Environment
{
    private $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function get($key)
    {
        return $this->params[$key];
    }
}
