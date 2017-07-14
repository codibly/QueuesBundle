<?php

namespace Codibly\QueuesBundle\Adapter;

use Bernard\Message\PlainMessage;
use Bernard\Producer;
use Codibly\QueuesBundle\Message\MessageEntityAbstract;

class BernardAdapter implements QueueAdapterInterface
{
    /**
     * @var Producer
     */
    private $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    public function publishMessage(string $queue, MessageEntityAbstract $message)
    {
        $this->producer->produce(
            new PlainMessage(
                $this->getCallbackName($queue),
                $message->toArray()
            ),
            $queue
        );
    }

    private function getCallbackName(string $queue)
    {
        return lcfirst(preg_replace('/[^a-zA-Z]/', '', $queue));
    }
}
