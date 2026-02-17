<?php

declare(strict_types=1);

namespace Th\Form;

final class AdminLoginForm
{
    private array $errors = [];

    private function __construct(
        private string $email,
        private string $password
    ) {
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $form = new self(
            mb_strtolower(trim((string) ($input['email'] ?? ''))),
            (string) ($input['password'] ?? '')
        );

        $form->validate();

        return $form;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return array{email: string}
     */
    public function old(): array
    {
        return ['email' => $this->email];
    }

    private function validate(): void
    {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Enter a valid email address.';
        }

        if ($this->password === '') {
            $this->errors['password'] = 'Password is required.';
        }
    }
}
