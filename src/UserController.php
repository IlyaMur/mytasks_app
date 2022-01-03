<?php

declare(strict_types=1);

namespace TasksApp;

class UserController
{
    public array $errors = [];
    public string $apiKey = '';

    public function __construct(private UserGateway $gateway)
    {
    }

    public function processCreatingRequest(array $data): void
    {
        $this->username = $_POST['username'];
        $this->name = $_POST['name'];
        $this->password = $_POST['password'];

        $this->validateInput();

        if (empty($this->errors)) {
            $this->apiKey = $this->gateway->create($data);
        }
    }

    public function validateInput(): void
    {
        if (empty($this->name)) {
            $this->errors['name'] = 'Please input your name';
        }

        if (empty($this->username)) {
            $this->errors['username'] = 'Please input your username';
        }

        $user = $this->gateway->getUser($this->username);

        if ($user) {
            $this->errors['alreadyExist'] = 'User with this username already exists';
        }

        if (strlen($this->password) < 7) {
            $this->errors['password'] = 'Password must be at least 7 characters';
        }
    }
}
