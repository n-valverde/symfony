<?php

namespace Symfony\Component\Messenger\Bridge\Mercure\Transport;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mercure\Hub;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class MercureTransportFactory implements TransportFactoryInterface
{
    public function __construct(private HubInterface $hub)
    {
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $client = HttpClient::createForBaseUri('http://127.0.0.1/.well-known/mercure');

        $connection = new Connection($client, $this->hub);

        return new MercureTransport($connection);
    }

    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'mercure://');
    }
}
