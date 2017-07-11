<?php

namespace Codibly\QueuesBundle\HashGenerator;

class ShaHashGenerator implements HashGeneratorInterface
{
    public function generateHash(string $append = null): string
    {
        $hash = (new \DateTimeImmutable())->getTimestamp() . rand(1000, 9999);

        if (null !== $append) {
            $hash .= $append;
        }

        return hash('sha1', $hash);
    }
}
