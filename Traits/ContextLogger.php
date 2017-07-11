<?php

namespace Codibly\QueuesBundle\Traits;

use Psr\Log\LoggerInterface;

/**
 * @property LoggerInterface $logger
 */
trait ContextLogger
{
    protected function getContext(): string
    {
        return get_class($this);
    }

    protected function info(string $message): void
    {
        $this->logger->info('[' . $this->getContext() . '] ' . $message);
    }

    protected function error(string $message): void
    {
        $this->logger->error('[' . $this->getContext() . '] ' . $message);
    }

    protected function warning(string $message): void
    {
        $this->logger->warning('[' . $this->getContext() . '] ' . $message);
    }

    protected function notice(string $message): void
    {
        $this->logger->notice('[' . $this->getContext() . '] ' . $message);
    }

    protected function debug(string $message): void
    {
        $this->logger->debug('[' . $this->getContext() . '] ' . $message);
    }
}
