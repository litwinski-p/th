<?php

declare(strict_types=1);

namespace Th\Form;

use DateTimeImmutable;

final class MovementCreateForm
{
    private array $errors = [];

    private function __construct(
        private string $movementType,
        private string $amountInput,
        private string $description,
        private string $movedAt
    ) {
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $form = new self(
            trim((string) ($input['movement_type'] ?? '')),
            trim((string) ($input['amount'] ?? '')),
            trim((string) ($input['description'] ?? '')),
            trim((string) ($input['moved_at'] ?? ''))
        );

        $form->validate();

        return $form;
    }

    public function movementType(): string
    {
        return $this->movementType;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function movedAt(): string
    {
        return $this->movedAt;
    }

    public function normalizedAmount(): string
    {
        return number_format((float) $this->amountInput, 2, '.', '');
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
     * @return array{movement_type: string, amount: string, description: string, moved_at: string}
     */
    public function old(): array
    {
        return [
            'movement_type' => $this->movementType,
            'amount' => $this->amountInput,
            'description' => $this->description,
            'moved_at' => $this->movedAt,
        ];
    }

    private function validate(): void
    {
        if (!in_array($this->movementType, ['earning', 'expense'], true)) {
            $this->errors['movement_type'] = 'Choose a valid movement type.';
        }

        if (!is_numeric($this->amountInput) || (float) $this->amountInput <= 0) {
            $this->errors['amount'] = 'Amount must be a number greater than zero.';
        }

        if (mb_strlen($this->description) < 3 || mb_strlen($this->description) > 255) {
            $this->errors['description'] = 'Description must be between 3 and 255 characters.';
        }

        if (!$this->isValidDate($this->movedAt)) {
            $this->errors['moved_at'] = 'Date must be in YYYY-MM-DD format.';
        }
    }

    private function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
