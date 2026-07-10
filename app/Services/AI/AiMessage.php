<?php

namespace App\Services\AI;

use InvalidArgumentException;

readonly class AiMessage
{
    public const array ROLES = ['system', 'user', 'assistant'];

    public function __construct(
        public string $role,
        public string $content,
    ) {
        if (! in_array($role, self::ROLES, true)) {
            throw new InvalidArgumentException("Unsupported AI message role [{$role}].");
        }

        if (trim($content) === '') {
            throw new InvalidArgumentException('AI message content cannot be empty.');
        }
    }

    public static function system(string $content): self
    {
        return new self('system', $content);
    }

    public static function user(string $content): self
    {
        return new self('user', $content);
    }

    public static function assistant(string $content): self
    {
        return new self('assistant', $content);
    }

    /**
     * @return array{role: string, content: string}
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}
