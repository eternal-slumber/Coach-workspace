<?php

namespace App\Services\AI;

interface AiClientInterface
{
    /**
     * @param  list<AiMessage>  $messages
     * @param  array<string, mixed>  $options
     */
    public function chat(array $messages, array $options = []): AiResponse;
}
