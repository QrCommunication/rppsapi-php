<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

use QrCommunication\RppsApi\Enum\SourceName;

final readonly class SourceStatus
{
    public function __construct(
        public SourceName $source,
        public bool $success,
        public int $rowCount = 0,
        public float $durationMs = 0,
        public ?string $error = null,
    ) {}
}
