<?php

namespace PaneeDesign\PhpFirebaseCloudMessaging\Recipient;

/**
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 */
class Topic extends Recipient
{
    /**
     * @var string
     */
    private $name;

    /**
     * Topic constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}