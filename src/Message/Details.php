<?php

namespace PE\Component\WAMP\Message;

/**
 * @codeCoverageIgnore
 */
trait Details
{
    /**
     * @var array
     */
    private array $details = [];

    /**
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param array $details
     *
     * @return $this
     */
    public function setDetails(array $details): self
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
    public function getDetail(string $name, $default = null)
    {
        return array_key_exists($name, $this->details) ? $this->details[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setDetail(string $name, $value): self
    {
        $this->details[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param array  $features
     *
     * @return $this
     * @deprecated
     */
    public function addFeatures(string $name, array $features): self
    {
        if (!isset($this->details['roles'])) {
            $this->details['roles'] = [];
        }

        $this->details['roles'][$name] = ['features' => $features];
        return $this;
    }

    /**
     * Add feature to role
     *
     * @param string $role
     * @param string $feature
     * @param bool $enabled
     *
     * @return $this
     */
    public function setFeature(string $role, string $feature, bool $enabled = true): self
    {
        $this->details['roles'][$role]['features'][$feature] = $enabled;
        return $this;
    }
}
