<?php

namespace App\Console\Commands;

use App\Services\AI\AiClientException;
use App\Services\AI\AiClientInterface;
use App\Services\AI\AiMessage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:test')]
#[Description('Send a simple test request to the configured AI provider')]
class AiTestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AiClientInterface $client): int
    {
        try {
            $response = $client->chat([
                AiMessage::user('Ответь одним словом: работает'),
            ]);
        } catch (AiClientException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("{$response->provider} / {$response->model}");
        $this->line($response->content);

        return self::SUCCESS;
    }
}
