<?php

namespace Codibly\QueuesBundle\Consumer;

use AppBundle\Entity\Message\Message;
use Bernard\Message\PlainMessage;
use Codibly\QueuesBundle\MessageFactory;
use Codibly\QueuesBundle\Traits\ContextLogger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class GeneralConsumer
{
    use ContextLogger {
        getContext as getParentContext;
    }

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $currentMessageId;

    /**
     * DeployProcessorConsumer constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     * @param MessageFactory $messageFactoryBundle
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        MessageFactory $messageFactory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->logger = $logger;
        $this->em = $em;
        $this->messageFactory = $messageFactory;
        $this->dispatcher = $dispatcher;

        $this->repository = $em->getRepository($this->getMessageClass());
    }

    /**
     * @return string
     */
    abstract protected function getMessageClass(): string;

    protected function getContext(): string
    {
        if (null === $this->currentMessageId) {
            return $this->getParentContext();
        }

        return 'Message ' . $this->currentMessageId;
    }

    public function consumeMessage(PlainMessage $externalMessage)
    {
        try {
            $message = $this->messageFactory->createFromBernardBundle($externalMessage);

            $this->currentMessageId = $message->getMessageId();

            $results = $this->repository->findBy(['messageId' => $message->getMessageId()]);

            if (count($results) > 0) {
                $this->info(
                    sprintf(
                        'Message skipped because it was already received. Message details: %s',
                        (string)$message
                    )
                );

                return;
            }

            $this->em->persist($message);
            $this->em->flush();

            $this->info(
                sprintf(
                    'Message was added to the database with id: %s. Message details: %s',
                    $message->getId(),
                    (string)$message
                )
            );

            $this->dispatchInternalEvent($message);
        } catch (\InvalidArgumentException $e) {
            $this->error(
                sprintf(
                    'Unable to process message because of "%s". Message name: "%s", body: "%s", queue: "%s".',
                    $e->getMessage(),
                    $externalMessage->getName(),
                    json_encode($externalMessage->all()),
                    $externalMessage->getQueue()
                )
            );
        }
    }
    
    abstract protected function dispatchInternalEvent(Message $message);
}
