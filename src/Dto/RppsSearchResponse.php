<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

final readonly class RppsSearchResponse
{
    public function __construct(
        /** @var RppsSearchResult[] */
        public array $results = [],
        public int $total = 0,
        public int $page = 1,
        public int $pageSize = 100,
        public float $durationMs = 0,
        public RppsSearchCriteria $criteria = new RppsSearchCriteria(),
    ) {}
}
