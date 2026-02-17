<?php

declare(strict_types=1);

namespace Th\Form;

final class AdminSetupForm
{
    private array $errors = [];

    private function __construct(
        private string $name,
        private string $email,
        private string $password,
        private string $passwordConfirmation
    ) {
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $form = new self(
            trim((string) ($input['name'] ?? '')),
            mb_strtolower(trim((string) ($input['email'] ?? ''))),
            (string) ($input['password'] ?? ''),
            (string) ($input['password_confirmation'] ?? '')
        );

        $form->validate();

        return $form;
    }

    public function name(): string
    {
        return $this->name;
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
     * @return array{name: string, email: string}
     */
    public function old(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    private function validate(): void
    {
        if (mb_strlen($this->name) < 2 || mb_strlen($this->name) > 120) {
            $this->errors['name'] = 'Name must be between 2 and 120 characters.';
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Enter a valid email address.';
        }

        if (!$this->isStrongPassword($this->password)) {
            $this->errors['password'] = 'Password must have 12+ chars, upper/lowercase, number and symbol.';
        }

        if ($this->password !== $this->passwordConfirmation) {
            $this->errors['password_confirmation'] = 'Password confirmation does not match.';
        }
    }

    private function isStrongPassword(string $password): bool
    {
        if (mb_strlen($password) < 12) {
            return false;
        }

        return preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/\d/', $password) === 1
            && preg_match('/[^a-zA-Z\d]/', $password) === 1;
    }
}
