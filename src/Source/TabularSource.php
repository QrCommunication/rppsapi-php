<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Source;

use GuzzleHttp\ClientInterface;

abstract class TabularSource
{
    private const BASE_URL = 'https://tabular-api.data.gouv.fr/api/resources';

    public function __construct(
        protected readonly ClientInterface $http,
        protected readonly string $baseUrl = self::BASE_URL,
        protected readonly int $pageSize = 100,
    ) {}

    protected function query(string $resourceId, array $filters, int $page = 1): array
    {
        $params = ['page_size' => $this->pageSize, 'page' => $page];
        foreach ($filters as $column => $filter) {
            $operator = $filter['operator'] ?? 'exact';
            $params["{$column}__{$operator}"] = $filter['value'];
        }

        $url = "{$this->baseUrl}/{$resourceId}/data/";
        $response = $this->http->request('GET', $url, [
            'query' => $params,
            'headers' => ['Accept' => 'application/json'],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function queryAll(string $resourceId, string $filterColumn, string $filterValue): array
    {
        $result = $this->query($resourceId, [
            $filterColumn => ['value' => $filterValue, 'operator' => 'exact'],
        ]);

        $allData = $result['data'] ?? [];
        while (!empty($result['links']['next'])) {
            $response = $this->http->request('GET', $result['links']['next'], [
                'headers' => ['Accept' => 'application/json'],
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
            $allData = array_merge($allData, $result['data'] ?? []);
        }

        return $allData;
    }
}
