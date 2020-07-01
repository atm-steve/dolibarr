<?php

namespace PaneeDesign\PhpFirebaseCloudMessaging\Tests;

use PaneeDesign\PhpFirebaseCloudMessaging\Recipient\Recipient;
use PaneeDesign\PhpFirebaseCloudMessaging\Message;
use PaneeDesign\PhpFirebaseCloudMessaging\Recipient\Topic;
use PaneeDesign\PhpFirebaseCloudMessaging\Notification;
use PaneeDesign\PhpFirebaseCloudMessaging\Recipient\Device;

/**
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 */
class MessageTest extends PhpFirebaseCloudMessagingTestCase
{
    /**
     * @var Message
     */
    private $fixture;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Message();
    }

    public function testThrowsExceptionWhenDifferentRecipientTypesAreRegistered()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->fixture->addRecipient(new Topic('breaking-news'))
            ->addRecipient(new Recipient());
    }

    public function testThrowsExceptionWhenNoRecipientWasAdded()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->fixture->jsonSerialize();
    }

    public function testThrowsExceptionWhenMultipleTopicsWereGiven()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->fixture->addRecipient(new Topic('breaking-news'))
            ->addRecipient(new Topic('another topic'));

        $this->fixture->jsonSerialize();
    }

    public function testJsonEncodeWorksOnTopicRecipients()
    {
        $body = '{"to":"\/topics\/breaking-news","notification":{"title":"test","body":"a nice testing notification"}}';

        $notification = new Notification('test', 'a nice testing notification');
        $message = new Message();
        $message->setNotification($notification);

        $message->addRecipient(new Topic('breaking-news'));
        $this->assertSame(
            $body,
            json_encode($message)
        );
    }

    public function testJsonEncodeWorksOnDeviceRecipients()
    {
        $body = '{"to":"deviceId","notification":{"title":"test","body":"a nice testing notification"}}';

        $notification = new Notification('test', 'a nice testing notification');
        $message = new Message();
        $message->setNotification($notification);

        $message->addRecipient(new Device('deviceId'));
        $this->assertSame(
            $body,
            json_encode($message)
        );
    }
}