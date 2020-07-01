<?php

namespace PaneeDesign\PhpFirebaseCloudMessaging;

use PaneeDesign\PhpFirebaseCloudMessaging\Recipient\Recipient;
use PaneeDesign\PhpFirebaseCloudMessaging\Recipient\Topic;
use PaneeDesign\PhpFirebaseCloudMessaging\Recipient\Device;

/**
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 */
class Message implements \JsonSerializable
{
    /**
     * Maximum topics and devices: https://firebase.google.com/docs/cloud-messaging/http-server-ref#send-downstream
     */
    const MAX_TOPICS  = 3;
    const MAX_DEVICES = 1000;

    /**
     * Priority of a message: https://firebase.google.com/docs/cloud-messaging/concept-options#setting-the-priority-of-a-message
     */
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH   = 'high';

    /**
     * @var Notification
     */
    private $notification;

    /**
     * @var string
     */
    private $collapseKey;

    /**
     * @var string
     */
    private $priority = self::PRIORITY_HIGH;

    /**
     * Is content Available?
     *
     * @var boolean
     */
    private $contentAvailable;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Recipient[]
     */
    private $recipients = [];

    /**
     * @var Topic|Device|Recipient
     */
    private $recipientType;

    /**
     * @var array
     */
    private $jsonData;

    /**
     * @var string
     */
    private $condition;

    /**
     * Message constructor.
     */
    public function __construct()
    {
        $this->jsonData = [];
    }

    /**
     * Where should the message go
     *
     * @param Recipient $recipient
     * @return Message
     */
    public function addRecipient(Recipient $recipient)
    {
        if (!isset($this->recipientType)) {
            $this->recipientType = get_class($recipient);
        }

        if ($this->recipientType !== get_class($recipient)) {
            throw new \InvalidArgumentException('Mixed recipient types are not supported by FCM');
        }

        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * @param Notification $notification
     * @return Message
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @see https://firebase.google.com/docs/cloud-messaging/concept-options#collapsible_and_non-collapsible_messages
     *
     * @param string $collapseKey
     * @return Message
     */
    public function setCollapseKey($collapseKey)
    {
        $this->collapseKey = $collapseKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCollapseKey()
    {
        return $this->collapseKey;
    }

    /**
     * @param string $priority
     * @return Message
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param boolean $contentAvailable
     * @return Message
     */
    public function setContentAvailable($contentAvailable)
    {
        $this->contentAvailable = $contentAvailable;

        return $this;
    }

    /**
     * Get content available
     *
     * @return string
     */
    public function getContentAvailable()
    {
        return $this->contentAvailable;
    }

    /**
     * @param array $data
     * @return Message
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Specify a condition pattern when sending to combinations of topics
     * https://firebase.google.com/docs/cloud-messaging/topic-messaging#sending_topic_messages_from_the_server
     *
     * Examples:
     * "%s && %s" > Send to devices subscribed to topic 1 and topic 2
     * "%s && (%s || %s)" > Send to devices subscribed to topic 1 and topic 2 or 3
     *
     * @param string $condition
     * @return Message
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set root message data via key
     *
     * @param string $key
     * @param mixed $value
     * @return Message
     */
    public function setJsonKey($key, $value)
    {
        $this->jsonData[$key] = $value;

        return $this;
    }

    /**
     * Unset root message data via key
     *
     * @param string $key
     * @return Message
     */
    public function unsetJsonKey($key)
    {
        unset($this->jsonData[$key]);

        return $this;
    }

    /**
     * Get root message data via key
     *
     * @param string $key
     * @return mixed
     */
    public function getJsonKey($key)
    {
        return $this->jsonData[$key];
    }

    /**
     * Set root message data
     *
     * @param array $array
     * @return Message
     */
    public function setJsonData($array)
    {
        $this->jsonData = $array;

        return $this;
    }

    /**
     * Get root message data
     *
     * @return array
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }

    /**
     * @param $value
     * @return Message
     */
    public function setDelayWhileIdle($value)
    {
        $this->setJsonKey('delay_while_idle', (bool)$value);

        return $this;
    }

    /**
     * @param $value
     * @return Message
     */
    public function setTimeToLive($value)
    {
        $this->setJsonKey('time_to_live', (int)$value);

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $jsonData = $this->jsonData;

        if (empty($this->recipients)) {
            throw new \UnexpectedValueException('Message must have at least one recipient');
        }

        if (count($this->recipients) == 1) {
            $jsonData['to'] = $this->createTarget();
        } elseif ($this->recipientType == Device::class) {
            $jsonData['registration_ids'] = $this->createTarget();
        } else {
            $jsonData['condition'] = $this->createTarget();
        }

        if ($this->collapseKey) {
            $jsonData['collapse_key'] = $this->collapseKey;
        }

        if ($this->data) {
            $jsonData['data'] = $this->data;
        }

        if ($this->priority) {
            $jsonData['priority'] = $this->priority;
        }

        if ($this->contentAvailable) {
            $jsonData['content_available'] = $this->contentAvailable;
        }

        if ($this->notification) {
            $jsonData['notification'] = $this->notification;
        }

        return $jsonData;
    }

    /**
     * @return array|null|string
     */
    private function createTarget()
    {
        $recipientCount = count($this->recipients);

        switch ($this->recipientType) {
            case Topic::class:
                if ($recipientCount == 1) {
                    return sprintf('/topics/%s', current($this->recipients)->getName());
                } else if ($recipientCount > self::MAX_TOPICS) {
                    throw new \OutOfRangeException(
                        sprintf(
                            'Message topic limit exceeded. Firebase supports a maximum of %u topics.',
                            self::MAX_TOPICS
                        )
                    );
                } else if (!$this->condition) {
                    throw new \InvalidArgumentException(
                        'Missing message condition. You must specify a condition pattern when sending to combinations of topics.'
                    );

                } else if ($recipientCount != substr_count($this->condition, '%s')) {
                    throw new \UnexpectedValueException(
                        'The number of message topics must match the number of occurrences of "%s" in the condition pattern.'
                    );
                } else {
                    $names = [];

                    /* @var Topic $recipient */
                    foreach ($this->recipients as $recipient) {
                        $names[] = vsprintf("'%s' in topics", [$recipient->getName()]);
                    }

                    return vsprintf($this->condition, $names);
                }

                break;
            case Device::class:
                if ($recipientCount == 1) {
                    return current($this->recipients)->getToken();
                } else if ($recipientCount > self::MAX_DEVICES) {
                    throw new \OutOfRangeException(
                        sprintf(
                            'Message device limit exceeded. Firebase supports a maximum of %u devices.',
                            self::MAX_DEVICES
                        )
                    );

                } else {
                    $ids = [];

                    /* @var Device $recipient */
                    foreach ($this->recipients as $recipient) {
                        $ids[] = $recipient->getToken();
                    }

                    return $ids;
                }


                break;
            default:
                break;
        }

        return null;
    }
}