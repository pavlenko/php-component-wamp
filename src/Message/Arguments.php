<?php

namespace PE\Component\WAMP\Message;

trait Arguments
{
    /**
     * @var array
     */
    private $arguments = [];

    /**
     * @var array
     */
    private $argumentsKw = [];

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array|null $arguments
     *
     * @return self
     */
    public function setArguments(array $arguments = null)
    {
        $this->arguments = $arguments ?: [];
        return $this;
    }

    /**
     * @return array
     */
    public function getArgumentsKw()
    {
        return $this->argumentsKw;
    }

    /**
     * @param array|null $argumentsKw
     *
     * @return self
     */
    public function setArgumentsKw(array $argumentsKw = null)
    {
        $this->argumentsKw = $argumentsKw ?: [];
        return $this;
    }

    /**
     * @return array
     */
    protected function getArgumentsParts()
    {
        $parts = [];

        if (count($this->arguments)) {
            $parts[] = $this->arguments;

            if (count($this->argumentsKw)) {
                $parts[] = $this->argumentsKw;
            }
        } else if (count($this->argumentsKw)) {
            $parts = [[], $this->argumentsKw];
        }

        return $parts;
    }
}