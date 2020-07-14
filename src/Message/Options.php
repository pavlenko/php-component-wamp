<?php

namespace PE\Component\WAMP\Message;

trait Options
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return self
     */
    public function setOptions(array $options)
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
    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }
}
