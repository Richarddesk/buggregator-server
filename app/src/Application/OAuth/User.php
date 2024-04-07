<?php

declare(strict_types=1);

namespace App\Application\OAuth;

final class User
{
    public function __construct(
        private readonly array $data,
    ) {
    }

    public function getUsername(): string
    {
        return $this->data['nickname'] ?? 'guest';
    }

    public function getAvatar(): string
    {
        return $this->data['picture'];
    }

    public function getEmail(): string
    {
        return $this->data['email'];
    }
}
