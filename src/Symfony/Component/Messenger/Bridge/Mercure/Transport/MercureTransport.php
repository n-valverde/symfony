<?php

namespace Symfony\Component\Messenger\Bridge\Mercure\Transport;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Messenger\Bridge\Mercure\Serializer\MercureSerializer;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\QueueReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MercureTransport implements QueueReceiverInterface, TransportInterface
{
    private SerializerInterface $serializer;

    public function __construct(private Connection $connection, ?SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer ?? Serializer::create();
    }

    public function get(): iterable
    {
        foreach ($this->connection->get() as $chunk) {
            $body = trim($chunk->getContent());

            if (empty($body) || str_starts_with($body, ':')) {
                continue;
            }

            $lines = explode(PHP_EOL, $body);

            foreach ($lines as $line) {
                if (str_starts_with($line, 'event:')) {
                    $type = trim(explode(':', $line)[1]);
                } else {
                    $bodyArray[] = $line;
                }
            }

            yield $this->serializer->decode(['body' => json_encode($bodyArray), 'headers' => ['type' => $type]]);
        }
    }

    public function getFromQueues(array $queueNames): iterable
    {
        foreach ($this->connection->get($queueNames) as $chunk) {
            yield $this->serializer->decode(['body' => $chunk->getContent()]);
        }
    }

    public function ack(Envelope $envelope): void
    {
        // TODO: Implement ack() method.
    }

    public function reject(Envelope $envelope): void
    {
        // TODO: Implement reject() method.
    }

    public function send(Envelope $envelope): Envelope
    {
//        var_dump($envelope);die();
        $encoded = $this->serializer->encode($envelope);
        $stamp = $envelope->last(MercureStamp::class);

        if (!$stamp) {
            $stamp = new MercureStamp($encoded['headers']['routing_key'] ?? 'messenger_default');
        }
//        var_dump($encoded);die();

        $this->connection->send($stamp, $encoded['body'], $encoded['headers']['type']);

//        $this->serializer->encode($envelope);



        return $envelope;
    }
}
