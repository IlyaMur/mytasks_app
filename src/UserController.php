<?php

declare(strict_types=1);

namespace TasksApp;

class UserController
{
    public function __construct(private UserGateway $gateway)
    {
    }

    public function processCreatingRequest(array $userData): array
    {
        $userData = $this->validateInput($userData);

        if (empty($userData['errors'])) {
            $userData['apiKey'] = $this->gateway->create($userData);
        }

        return $userData;
    }

    public function validateInput($userData): array
    {
        if (empty($userData['name'])) {
            $userData['errors']['name'] = 'Please input your name';
        }

        if (empty($userData['username'])) {
            $userData['errors']['username'] = 'Please input your username';
        }

        $user = $this->gateway->getUser($userData['username']);

        if ($user) {
            $userData['errors']['alreadyExists'] = 'User with this username already exists';
        }

        if (strlen($userData['password']) < 7) {
            $userData['errors']['password'] = 'Password must be at least 7 characters';
        }

        return $userData;
    }
}
