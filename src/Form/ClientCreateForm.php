<?php

declare(strict_types=1);

namespace Th\Form;

final class ClientCreateForm
{
    private array $errors = [];

    private function __construct(
        private string $fullName,
        private ?string $email,
        private ?string $phone,
        private ?string $note
    ) {
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $form = new self(
            trim((string) ($input['full_name'] ?? '')),
            self::optionalText($input['email'] ?? null),
            self::optionalText($input['phone'] ?? null),
            self::optionalText($input['note'] ?? null)
        );

        $form->validate();

        return $form;
    }

    public function fullName(): string
    {
        return $this->fullName;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function note(): ?string
    {
        return $this->note;
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
     * @return array{full_name: string, email: string, phone: string, note: string}
     */
    public function old(): array
    {
        return [
            'full_name' => $this->fullName,
            'email' => $this->email ?? '',
            'phone' => $this->phone ?? '',
            'note' => $this->note ?? '',
        ];
    }

    private function validate(): void
    {
        if (mb_strlen($this->fullName) < 2 || mb_strlen($this->fullName) > 150) {
            $this->errors['full_name'] = 'Full name must be between 2 and 150 characters.';
        }

        if ($this->email !== null && filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors['email'] = 'Email is invalid.';
        }

        if ($this->phone !== null && mb_strlen($this->phone) > 50) {
            $this->errors['phone'] = 'Phone cannot be longer than 50 characters.';
        }

        if ($this->note !== null && mb_strlen($this->note) > 2000) {
            $this->errors['note'] = 'Note cannot be longer than 2000 characters.';
        }
    }

    private static function optionalText(mixed $value): ?string
    {
        if (!is_scalar($value) && $value !== null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
