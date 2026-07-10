<?php

namespace App\Services\AI;

class LmStudioClient extends OpenAiCompatibleClient
{
    public function provider(): string
    {
        return 'lmstudio';
    }
}
