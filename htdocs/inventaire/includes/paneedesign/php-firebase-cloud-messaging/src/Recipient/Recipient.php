<?php

namespace PaneeDesign\PhpFirebaseCloudMessaging\Recipient;

/**
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 */
class Recipient
{
    /**
     * @var Device|Topic
     */
    private $to;

    /**
     * @param Device|Topic $to
     * @return Recipient
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return Device|Topic
     */
    public function toJson()
    {
        return $this->to;
    }
}