<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Source;

use QrCommunication\RppsApi\Dto\{Activite, Identite, Profession, RppsSearchCriteria, RppsSearchResult};

final class PersonneActiviteSource extends TabularSource
{
    private const RESOURCE_ID = 'fffda7e9-0ea2-4c35-bba0-4496f3af935d';
    private const FILTER_COLUMN = 'Identifiant PP';

    public function fetchByRpps(string $rpps): array
    {
        $rows = $this->queryAll(self::RESOURCE_ID, self::FILTER_COLUMN, $rpps);
        $firstRow = $rows[0] ?? null;

        return [
            'identite' => $firstRow ? Identite::fromRow($firstRow) : new Identite(),
            'identificationNationale' => trim((string) ($firstRow['Identification nationale PP'] ?? '')),
            'profession' => $firstRow ? Profession::fromRow($firstRow) : new Profession(),
            'activites' => array_values(array_map(
                fn(array $row) => Activite::fromRow($row),
                array_filter($rows, fn(array $row) => trim((string) ($row['Identifiant technique de la structure'] ?? '')) !== ''),
            )),
        ];
    }

    public function search(RppsSearchCriteria $criteria, int $page = 1): array
    {
        $filters = [];

        if ($criteria->nom !== null && $criteria->nom !== '') {
            $filters["Nom d'exercice"] = ['value' => $criteria->nom, 'operator' => 'contains'];
        }

        if ($criteria->prenom !== null && $criteria->prenom !== '') {
            $filters["Prénom d'exercice"] = ['value' => $criteria->prenom, 'operator' => 'contains'];
        }

        if ($criteria->codePostal !== null && $criteria->codePostal !== '') {
            $operator = strlen($criteria->codePostal) === 5 ? 'exact' : 'contains';
            $filters['Code postal (coord. structure)'] = ['value' => $criteria->codePostal, 'operator' => $operator];
        }

        $result = $this->query(self::RESOURCE_ID, $filters, $page);
        $rows = $result['data'] ?? [];
        $total = $result['meta']['total'] ?? count($rows);

        $seen = [];
        $results = [];
        foreach ($rows as $row) {
            $rpps = trim((string) ($row['Identifiant PP'] ?? ''));
            if ($rpps === '' || isset($seen[$rpps])) continue;
            $seen[$rpps] = true;
            $results[] = RppsSearchResult::fromRow($row);
        }

        return ['results' => $results, 'total' => $total];
    }
}
