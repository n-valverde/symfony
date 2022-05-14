<?php

namespace Symfony\Component\Messenger\Bridge\Mercure\Transport;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class Connection
{
    public function __construct(private HttpClientInterface $httpClient, private HubInterface $hub)
    {
    }

    public function get(array $topics = []): ResponseStreamInterface
    {
        $topics = [] === $topics ? '*' : implode(',', $topics);

        $response =  $this->httpClient->request('GET', 'http://127.0.0.1/.well-known/mercure', [
            'query' => [
                'topic' => $topics
            ],
//            'headers' => [
//                'last-event-id' => $this->lastId ?? 'urn:uuid:6663a619-ac28-410f-966f-1cecbc33ba80'
//            ]
        ]);

        return $this->httpClient->stream($response);
    }

    public function send(MercureStamp $stamp, string $data = '', string $type = null)
    {
        $this->hub->publish(new Update($stamp->getTopics(), $data, false, null, $type));
    }
}
