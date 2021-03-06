<?php

namespace PE\Component\WAMP\Message;

trait Details
{
    /**
     * @var array
     */
    private $details = [];

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param array $details
     *
     * @return $this
     */
    public function setDetails(array $details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getDetail($name, $default = null)
    {
        return array_key_exists($name, $this->details) ? $this->details[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setDetail($name, $value)
    {
        $this->details[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param array  $features
     *
     * @return $this
     */
    public function addFeatures($name, array $features)
    {
        if (!isset($this->details['roles'])) {
            $this->details['roles'] = [];
        }

        $this->details['roles'][$name] = ['features' => $features];
        return $this;
    }
}