<?php

namespace Codibly\QueuesBundle;

use Codibly\QueuesBundle\Message\MessageEntityAbstract;

interface QueueInterface
{
    public function publishMessage(string $queue, MessageEntityAbstract $message);
}
