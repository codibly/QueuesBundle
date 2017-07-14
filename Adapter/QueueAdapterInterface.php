<?php

namespace Codibly\QueuesBundle\Adapter;

use Codibly\QueuesBundle\Message\MessageEntityAbstract;

interface QueueAdapterInterface
{
    public function publishMessage(string $queue, MessageEntityAbstract $message);
}
