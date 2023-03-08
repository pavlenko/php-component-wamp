<?php

namespace PE\Component\WAMP\Message;

/**
 * @codeCoverageIgnore
 */
trait Arguments
{
    /**
     * @var array
     */
    private array $arguments = [];

    /**
     * @var array
     */
    private array $argumentsKw = [];

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array|null $arguments
     *
     * @return self
     */
    public function setArguments(array $arguments = null): self
    {
        $this->arguments = $arguments ?: [];
        return $this;
    }

    /**
     * @return array
     */
    public function getArgumentsKw(): array
    {
        return $this->argumentsKw;
    }

    /**
     * @param array|null $argumentsKw
     *
     * @return self
     */
    public function setArgumentsKw(array $argumentsKw = null): self
    {
        $this->argumentsKw = $argumentsKw ?: [];
        return $this;
    }

    /**
     * @return array
     */
    protected function getArgumentsParts(): array
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