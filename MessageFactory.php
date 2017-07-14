<?php

namespace Codibly\QueuesBundle;

use AppBundle\Entity\Message\Message;
use Codibly\QueuesBundle\HashGenerator\HashGeneratorInterface;
use Bernard\Message\PlainMessage;
use Psr\Log\LoggerInterface;

class MessageFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $internalMessagesBinding = [];

    /**
     * @var HashGeneratorInterface
     */
    private $hashGenerator;

    /**
     * MessageFactory constructor.
     * @param LoggerInterface $logger
     * @param HashGeneratorInterface $hashGenerator
     */
    public function __construct(LoggerInterface $logger, HashGeneratorInterface $hashGenerator)
    {
        $this->logger = $logger;
        $this->hashGenerator = $hashGenerator;
    }

    public function addInternalMessageBinding(string $name, string $internalMessageClass)
    {
        if (!class_exists($internalMessageClass)) {
            throw new \InvalidArgumentException('Internal message class must be full-namespace version.');
        }

        if (array_key_exists($name, $this->internalMessagesBinding)) {
            return;
        }

        $this->internalMessagesBinding[$name] = $internalMessageClass;
    }

    /**
     * @param PlainMessage $message
     * @return Message
     */
    public function createFromBernardBundle(PlainMessage $message): Message
    {
        $name = $message->getName();

        if (!$this->binded($name)) {
            throw new \InvalidArgumentException('Message binding don\'t exist');
        }

        return $this->createFromArray($name, $message->all());
    }

    public function createNew(string $name, array $data): Message
    {
        if (!$this->binded($name)) {
            throw new \InvalidArgumentException('Message binding don\'t exist');
        }

        $hash = $this->hashGenerator->generateHash(json_encode($data));

        return $this->createFromArray(
            $name,
            array_merge(
                [
                    'messageId' => $hash,
                ],
                $data
            )
        );
    }

    public function createFromArray(string $name, array $data): Message
    {
        try {
            $className = $this->internalMessagesBinding[$name];

            $internalMessage = new $className($data);

            return $internalMessage;
        } catch (\InvalidArgumentException $e) {
            $this->logger->error(
                sprintf(
                    'Internal message %s cannot be created from data consumed from queue: %s because of: %s',
                    $name,
                    json_encode($data),
                    $e->getMessage()
                )
            );

            throw new \InvalidArgumentException('Unable to create internal message from consumed data', 0, $e);
        }
    }

    private function binded(string $name): bool
    {
        if (!array_key_exists($name, $this->internalMessagesBinding)) {
            $this->logger->error(
                sprintf(
                    'Message consumed from queue with name: %s don\'t have internal binding. Binded messages: %s',
                    $name,
                    json_encode(array_keys($this->internalMessagesBinding))
                )
            );

            return false;
        }

        return true;
    }
}
