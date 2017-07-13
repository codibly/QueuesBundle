# QueuesBundle
Bundle with utilities of queues dealing based on BernardBundle

Installation

1. Add to your composer.json
``` json
"require": {
    (...)
    "codibly/queues-bundle": "dev-master"
}

"repositories": [
    (...)
    {
        "type": "vcs",
        "url": "https://github.com/codibly/QueuesBundle"
    }
]
```

2. Create main abstract Message entity extening Codibly\QueuesBundle\Message\MessageEntityAbstract, for example:

```php
<?php

namespace AppBundle\Entity\Message;

use Codibly\QueuesBundle\Message\MessageEntityAbstract;
use Doctrine\ORM\Mapping as ORM;


/**Interface
 * @ORM\Entity()
 * @ORM\Table(name="messages")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "deploy" = "DeployMessage",
 *     "deploy_result" = "DeployResultMessage",
 * })
 */
abstract class Message extends MessageEntityAbstract
{
}
```

3. Create another Messages entities based on main Message abstract. Notice, that every data received from message should 
be mapped using setters and getters methods. You should also list that properties in getRequiredProperties() method.

```php
<?php

namespace AppBundle\Entity\Message;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class DeployMessage extends Message
{

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Required client name")
     *
     * @var string
     */
    protected $clientName;
    
    /**
     * @return string
     */
    public function getClientName(): string
    {
        return $this->clientName;
    }

    /**
     * @param string $clientName
     * @return $this
     */
    public function setClientName(string $clientName)
    {
        $this->clientName = $clientName;

        return $this;
    }
        
    /**
     * @return array
     */
    protected function getRequiredProperties(): array
    {
        $parentRequired = parent::getRequiredProperties();
        $currentRequired = ['clientName'];

        return array_merge($parentRequired, $currentRequired);
    }
}
```

4. Add to your services.yml definition for MessageFactory service, completing calls section for add message binding:

```yaml
codibly_queues.message_factory:
    class: Codibly\QueuesBundle\MessageFactory
    arguments: ['@monolog.logger', '@codibly_queues.hash_generator.sha_hash']
    calls:
        - ['addInternalMessageBinding', ['deployQueue', 'ExampleBundle\Entity\Message\DeployMessage']]
        - ['addInternalMessageBinding', ['deployResultQueue', 'ExampleBundle\Entity\Message\DeployResultMessage']]
    public: true
```
5. ... and create your consumers based on GeneralConsumer. Example:
```yaml
example_consumer:
        class: AppBundle\Consumer\ExampleConsumer
        autowire: false
        autoconfigure: false
        parent: codibly_queues.general_consumer
        public: true
        tags:
            - { name: bernard.receiver, message: exampleQueue }
```

6. Add some events and subscribers, e.x.:

```php

<?php

namespace AppBundle\Event;

use AppBundle\Entity\Message\ExampleMessage;
use Symfony\Component\EventDispatcher\Event;

class ExampleEvent extends Event
{
    const NAME = 'example';

    /**
     * @var ExampleMessage
     */
    private $message;

    /**
     * ExampleEvent constructor.
     * @param ExampleMessage $message
     */
    public function __construct(ExampleMessage $message)
    {
        $this->message = $message;
    }

    /**
     * @return ExampleMessage
     */
    public function getMessage(): ExampleMessage
    {
        return $this->message;
    }
}


```

```php
<?php

namespace AppBundle\EventListener;

use AppBundle\Event\ExampleEvent;
use Codibly\QueuesBundle\Traits\ContextLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExampleSubscriber implements EventSubscriberInterface
{
    use ContextLogger {
        getContext as getParentContext;
    }

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $currentMessageId;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function getContext(): string
    {
        if (null === $this->currentMessageId) {
            return $this->getParentContext();
        }

        return 'Message ' . $this->currentMessageId;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExampleEvent::NAME => 'executeExample',
        ];
    }

    public function executeExample(ExampleEvent $event)
    {
        $message = $event->getMessage();
        $this->currentMessageId = $message->getMessageId();

        $this->logger->debug('Received message [' . $this->currentMessageId . ']: ' . var_export($message->toArray(), true));

        // ... another actions on some services ...
    }

}


```

7. Run 
```bash
php bin/console doctrine:schema:update --dump-sql --force
```

8. Configure your Bernad, example configuration:

```yaml
aws:
    version: '%aws_ver%'
    region: '%aws_region%'
    credentials:
        key: '%aws_key%'
        secret: '%aws_secret%'
    DynamoDb:
        region: '%aws_region%'
    S3:
        version: '%aws_s3_ver%'
    Sqs:
        credentials: '@aws_credentials'
        endpoint: '%aws_sqs_endpoint%'

bernard:
    driver: sqs
    options:
        sqs_service: aws.sqs
        sqs_queue_map: # optional for aliasing queue urls (default alias is the url section after the last "/")
            exampleQueue: '%example_queue_url%'
        prefetch: 1 # optional, but beware the default is >1 and you may run into invisibility timeout problems with that

```

services.yml
```yaml
aws_credentials:
        class: Aws\Credentials\Credentials
        arguments:
            - '%aws_key%'
            - '%aws_secret%'
```