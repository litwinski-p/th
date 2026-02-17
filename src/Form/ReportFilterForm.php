<?php

declare(strict_types=1);

namespace Th\Form;

use DateTimeImmutable;

final class ReportFilterForm
{
    private array $errors = [];

    private ?int $clientId = null;

    private ?string $normalizedFrom = null;

    private ?string $normalizedTo = null;

    private function __construct(
        private string $clientIdInput,
        private string $from,
        private string $to
    ) {
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $form = new self(
            trim((string) ($input['client_id'] ?? '')),
            trim((string) ($input['from'] ?? '')),
            trim((string) ($input['to'] ?? ''))
        );

        $form->validate();

        return $form;
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

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function clientId(): ?int
    {
        return $this->clientId;
    }

    public function fromDate(): ?string
    {
        return $this->normalizedFrom;
    }

    public function toDate(): ?string
    {
        return $this->normalizedTo;
    }

    /**
     * @return array{client_id: string, from: string, to: string}
     */
    public function filters(): array
    {
        return [
            'client_id' => $this->clientIdInput,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }

    private function validate(): void
    {
        if ($this->clientIdInput !== '') {
            if (!ctype_digit($this->clientIdInput)) {
                $this->errors['client_id'] = 'Client selection is invalid.';
            } else {
                $this->clientId = (int) $this->clientIdInput;
            }
        }

        if ($this->from !== '') {
            if ($this->isValidDate($this->from)) {
                $this->normalizedFrom = $this->from;
            } else {
                $this->errors['from'] = 'From date is invalid.';
            }
        }

        if ($this->to !== '') {
            if ($this->isValidDate($this->to)) {
                $this->normalizedTo = $this->to;
            } else {
                $this->errors['to'] = 'To date is invalid.';
            }
        }

        if ($this->normalizedFrom !== null && $this->normalizedTo !== null && $this->normalizedFrom > $this->normalizedTo) {
            $this->errors['range'] = 'From date cannot be greater than to date.';
        }
    }

    private function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
