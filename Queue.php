<?php

namespace Codibly\QueuesBundle;

use Codibly\QueuesBundle\Adapter\QueueAdapterInterface;
use Codibly\QueuesBundle\Message\MessageEntityAbstract;

class Queue implements QueueInterface
{
    /**
     * @var QueueAdapterInterface
     */
    private $adapter;

    /**
     * Queue constructor.
     * @param QueueAdapterInterface $adapter
     */
    public function __construct(QueueAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function publishMessage(string $queue, MessageEntityAbstract $message)
    {
        $this->adapter->publishMessage($queue, $message);
    }
}
