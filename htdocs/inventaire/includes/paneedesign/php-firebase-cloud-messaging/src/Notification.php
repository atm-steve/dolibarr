<?php

namespace PaneeDesign\PhpFirebaseCloudMessaging;

/**
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
 */
class Notification extends Message
{
    /**
     * Title of Notification
     *
     * @var string
     */
    private $title;

    /**
     * Body of Notification
     *
     * @var string
     */
    private $body;

    /**
     * Number of Notifications
     *
     * @var integer
     */
    private $badge;

    /**
     * Icon of Notification
     *
     * @var string
     */
    private $icon;

    /**
     * Hex code of color Notification
     *
     * @var string
     */
    private $color;

    /**
     * Sound filename of Notification
     *
     * @var string
     */
    private $sound;

    /**
     * Action after click on Notification
     *
     * @var string
     */
    private $clickAction;

    /**
     * Tag of notification
     *
     * @var string
     */
    private $tag;

    /**
     * Notification constructor.
     *
     * @param string $title
     * @param string $body
     */
    public function __construct($title = '', $body = '')
    {
        if ($title) {
            $this->title = $title;
        }

        if ($body) {
            $this->body = $body;
        }

        parent::__construct();
    }

    /**
     * Android only: notification title (also works for iOS watches)
     *
     * @param string $title
     * @return Notification
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Android/iOS: the body text is the main content of the notification
     *
     * @param string $body
     * @return Notification
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * iOS only: will add small red bubbles indicating the number of notifications to your apps icon
     *
     * @param integer $badge
     * @return Notification
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Get badge
     *
     * @return integer
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * Android only: set the name of your drawable resource as string
     *
     * @param string $icon the drawable name without .xml
     *
     * @return Notification
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Android only: background color of the notification icon when showing details on notifications
     *
     * @param string $color in #rrggbb format
     * @return Notification
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Android/iOS: can be default or a filename of a sound resource bundled in the app.
     * @see https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
     *
     * @param string $sound a sounds filename
     *
     * @return Notification
     */
    public function setSound($sound)
    {
        $this->sound = $sound;

        return $this;
    }

    /**
     * Get sound
     *
     * @return string
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * Android/iOS: what should happen upon notification click. when empty on Android the default activity
     * will be launched passing any payload to an intent.
     *
     * @param string $actionName on Android: intent name, on iOS: category in apns payload
     * @return Notification
     */
    public function setClickAction($actionName)
    {
        $this->clickAction = $actionName;

        return $this;
    }

    /**
     * Get click action
     *
     * @return string
     */
    public function getClickAction()
    {
        return $this->clickAction;
    }

    /**
     * Android only: when set notification will replace prior notifications from the same app with the same
     * tag.
     *
     * @param string $tag
     *
     * @return Notification
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Serialize object
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $jsonData = $this->getJsonData();

        if ($this->title) {
            $jsonData['title'] = $this->title;
        }

        if ($this->body) {
            $jsonData['body'] = $this->body;
        }

        if ($this->badge) {
            $jsonData['badge'] = $this->badge;
        }

        if ($this->icon) {
            $jsonData['icon'] = $this->icon;
        }

        if ($this->color) {
            $jsonData['color'] = $this->color;
        }

        if ($this->clickAction) {
            $jsonData['click_action'] = $this->clickAction;
        }

        if ($this->sound) {
            $jsonData['sound'] = $this->sound;
        }

        if ($this->tag) {
            $jsonData['tag'] = $this->tag;
        }

        return $jsonData;
    }
}
