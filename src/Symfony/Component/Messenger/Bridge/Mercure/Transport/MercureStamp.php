<?php

namespace Symfony\Component\Messenger\Bridge\Mercure\Transport;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

class MercureStamp implements NonSendableStampInterface
{
    public function __construct(private string|array $topics)
    {
    }

    public function getTopics(): array|string
    {
        return is_string($this->topics) ? $this->topics : implode(',', $this->topics);
    }
}
