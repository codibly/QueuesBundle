# QueuesBundle
Bundle with utilities of queues dealing based on BernardBundle

Installation

1. Add to your composer.json

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

2. Add to your services.yml definition for MessageFactory service:

codibly_queues.message_factory:
    class: Codibly\QueuesBundle\MessageFactory
    arguments: ['@monolog.logger', '@codibly_queues.hash_generator.sha_hash']
    calls:
        - ['addInternalMessageBinding', ['deployQueue', 'AppBundle\Entity\Message\DeployMessage']]
        - ['addInternalMessageBinding', ['deployResultQueue', 'AppBundle\Entity\Message\DeployResultMessage']]
    public: true