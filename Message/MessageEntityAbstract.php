<?php

namespace Codibly\QueuesBundle\Message;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\Timestampable;
use Symfony\Component\Validator\Constraints as Assert;

abstract class MessageEntityAbstract
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Processor require name")
     *
     * @var string
     */
    protected $messageId;

    public function __construct(array $data)
    {
        if (null === $data) {
            throw new \InvalidArgumentException('Message body should contain valid json');
        }

        $data['messageId'] = mt_rand(1,10000); // todo !!!!!!!!

        foreach ($this->getRequiredProperties() as $property) {
            if (!array_key_exists($property, $data)) {
                throw new \InvalidArgumentException(
                    'Message body should contain json with ' . $property . ' key'
                );
            }

            $this->{'set' . ucfirst($property)}($data[$property]);
        }
    }

    protected function getRequiredProperties(): array
    {
        return ['messageId'];
    }

    public function __toString()
    {
        $json = $this->toArray();

        return json_encode($json);
    }

    public function toArray()
    {
        $reflect = new \ReflectionClass($this);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED);
        $array = [];

        foreach ($props as $prop) {
            $array[$prop->getName()] = $this->{'get' . ucfirst($prop->getName())}();
        }

        return $array;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @param string $messageId
     * @return $this
     */
    public function setMessageId(string $messageId)
    {
        $this->messageId = $messageId;

        return $this;
    }
}
