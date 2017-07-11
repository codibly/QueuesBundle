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

``` php
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

``` php
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
     * @param string $messageId
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
```

4. Add to your services.yml definition for MessageFactory service, completing calls section for add message binding:

``` yml
codibly_queues.message_factory:
    class: Codibly\QueuesBundle\MessageFactory
    arguments: ['@monolog.logger', '@codibly_queues.hash_generator.sha_hash']
    calls:
        - ['addInternalMessageBinding', ['deployQueue', 'ExampleBundle\Entity\Message\DeployMessage']]
        - ['addInternalMessageBinding', ['deployResultQueue', 'ExampleBundle\Entity\Message\DeployResultMessage']]
    public: true
```
5. ... and create your consumers based on GeneralConsumer. Example:
```yml
deploy_consumer:
        autowire: false
        autoconfigure: false
        parent: codibly_queues.general_consumer
        public: true
        tags:
            - { name: bernard.receiver, message: deployQueue }
```