<?php

declare(strict_types=1);

namespace QrCommunication\RppsApi\Dto;

final readonly class CodeLabel
{
    public function __construct(
        public string $code = '',
        public string $libelle = '',
    ) {}

    public static function fromRow(array $row, string $codeKey, string $labelKey): self
    {
        return new self(
            code: trim((string) ($row[$codeKey] ?? '')),
            libelle: trim((string) ($row[$labelKey] ?? '')),
        );
    }

    public function toArray(): array
    {
        return ['code' => $this->code, 'libelle' => $this->libelle];
    }
}
