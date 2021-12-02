<?php

namespace PE\Component\WAMP\Message;

trait Options
{
    /**
     * @var array
     */
    private array $options = [];

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setOption(string $name, $value)
    {
        $this->options[$name] = $value;
    }
}

