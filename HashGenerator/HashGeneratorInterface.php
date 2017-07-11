<?php

namespace Codibly\QueuesBundle\HashGenerator;

interface HashGeneratorInterface
{
    public function generateHash(string $append = null): string;
}
