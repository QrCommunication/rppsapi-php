<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Utils;
use QrCommunication\RppsApi\Dto\{
    FhirData,
    Identite,
    Profession,
    RppsFullProfile,
    RppsSearchCriteria,
    RppsSearchResponse,
    SourceStatus,
};
use QrCommunication\RppsApi\Enum\SourceName;
use QrCommunication\RppsApi\Exception\RppsException;
use QrCommunication\RppsApi\Source\{
    CarteCpsSource,
    DiplomesSource,
    FhirSource,
    MssanteSource,
    PersonneActiviteSource,
    SavoirFaireSource,
};

final class RppsClient
{
    private readonly ClientInterface $http;
    private readonly RppsClientOptions $options;

    public function __construct(?RppsClientOptions $options = null, ?ClientInterface $http = null)
    {
        $this->options = $options ?? new RppsClientOptions();
        $this->http = $http ?? new Client(['timeout' => $this->options->timeout]);
    }

    public static function validateRpps(string $rpps): string
    {
        $cleaned = trim($rpps);
        if (!preg_match('/^\d{11}$/', $cleaned)) {
            throw new RppsException(
                "Numéro RPPS invalide : \"{$rpps}\". Le RPPS doit contenir exactement 11 chiffres."
            );
        }
        return $cleaned;
    }

    private function isEnabled(SourceName $source): bool
    {
        return !in_array($source->value, $this->options->disabledSources, true);
    }

    private function tabularOpts(): array
    {
        return [
            'baseUrl' => $this->options->tabularBaseUrl,
            'pageSize' => $this->options->tabularPageSize,
        ];
    }

    /**
     * Récupère le profil complet d'un praticien par numéro RPPS.
     * Interroge toutes les sources en parallèle.
     */
    public function getByRpps(string $rpps): RppsFullProfile
    {
        $cleanRpps = self::validateRpps($rpps);
        $startTime = microtime(true);
        $sources = [];

        // Construire les sources
        $personneActivite = new PersonneActiviteSource($this->http, $this->options->tabularBaseUrl, $this->options->tabularPageSize);
        $diplomes = new DiplomesSource($this->http, $this->options->tabularBaseUrl, $this->options->tabularPageSize);
        $savoirFaire = new SavoirFaireSource($this->http, $this->options->tabularBaseUrl, $this->options->tabularPageSize);
        $cartesCps = new CarteCpsSource($this->http, $this->options->tabularBaseUrl, $this->options->tabularPageSize);
        $mssante = new MssanteSource($this->http, $this->options->tabularBaseUrl, $this->options->tabularPageSize);

        // Exécuter toutes les requêtes (séquentiellement car Guzzle sync, mais encapsulé)
        $paResult = $this->safeQuery(SourceName::PersonneActivite, fn() => $personneActivite->fetchByRpps($cleanRpps));
        $diplResult = $this->safeQuery(SourceName::Diplomes, fn() => $diplomes->fetchByRpps($cleanRpps));
        $sfResult = $this->safeQuery(SourceName::SavoirFaire, fn() => $savoirFaire->fetchByRpps($cleanRpps));
        $cpsResult = $this->safeQuery(SourceName::CarteCps, fn() => $cartesCps->fetchByRpps($cleanRpps));
        $mssResult = $this->safeQuery(SourceName::Mssante, fn() => $mssante->fetchByRpps($cleanRpps));

        // FHIR (seulement si clé API fournie)
        $fhirResult = ['data' => new FhirData(), 'status' => new SourceStatus(SourceName::Fhir, true, 0, 0, 'disabled: no API key')];
        if ($this->options->fhirApiKey && $this->isEnabled(SourceName::Fhir)) {
            $fhirSource = new FhirSource($this->http, $this->options->fhirApiKey, $this->options->fhirBaseUrl);
            $fhirResult = $this->safeQuery(SourceName::Fhir, fn() => $fhirSource->fetchByRpps($cleanRpps));
        }

        $sources[] = $fhirResult['status'];
        $sources[] = $paResult['status'];
        $sources[] = $diplResult['status'];
        $sources[] = $sfResult['status'];
        $sources[] = $cpsResult['status'];
        $sources[] = $mssResult['status'];

        $pa = $paResult['data'] ?? ['identite' => new Identite(), 'identificationNationale' => '', 'profession' => new Profession(), 'activites' => []];

        $totalDurationMs = round((microtime(true) - $startTime) * 1000, 1);

        return new RppsFullProfile(
            rpps: $cleanRpps,
            identificationNationale: $pa['identificationNationale'],
            identite: $pa['identite'],
            profession: $pa['profession'],
            activites: $pa['activites'],
            diplomesEtAutorisations: $diplResult['data'] ?? [],
            savoirFaire: $sfResult['data'] ?? [],
            cartesCps: $cpsResult['data'] ?? [],
            messageriesMssante: $mssResult['data'] ?? [],
            fhir: $fhirResult['data'] instanceof FhirData ? $fhirResult['data'] : new FhirData(),
            sources: $sources,
            queriedAt: date('c'),
            totalDurationMs: $totalDurationMs,
        );
    }

    /**
     * Recherche par nom, prénom et/ou code postal.
     * Au moins un critère obligatoire. Recherche partielle insensible à la casse.
     */
    public function search(RppsSearchCriteria $criteria, int $page = 1, ?int $pageSize = null): RppsSearchResponse
    {
        if (empty($criteria->nom) && empty($criteria->prenom) && empty($criteria->codePostal)) {
            throw new RppsException('Au moins un critère de recherche est obligatoire (nom, prenom ou codePostal).');
        }

        $pageSize ??= $this->options->tabularPageSize;
        $startTime = microtime(true);

        $source = new PersonneActiviteSource($this->http, $this->options->tabularBaseUrl, $pageSize);
        $result = $source->search($criteria, $page);

        $durationMs = round((microtime(true) - $startTime) * 1000, 1);

        return new RppsSearchResponse(
            results: $result['results'],
            total: $result['total'],
            page: $page,
            pageSize: $pageSize,
            durationMs: $durationMs,
            criteria: $criteria,
        );
    }

    private function safeQuery(SourceName $source, callable $fn): array
    {
        if (!$this->isEnabled($source)) {
            return [
                'data' => null,
                'status' => new SourceStatus($source, true, 0, 0, 'disabled'),
            ];
        }

        $start = microtime(true);
        try {
            $result = $fn();
            $durationMs = round((microtime(true) - $start) * 1000, 1);
            $rowCount = is_array($result) && !isset($result['identite']) ? count($result) : 1;
            return [
                'data' => $result,
                'status' => new SourceStatus($source, true, $rowCount, $durationMs),
            ];
        } catch (\Throwable $e) {
            $durationMs = round((microtime(true) - $start) * 1000, 1);
            return [
                'data' => null,
                'status' => new SourceStatus($source, false, 0, $durationMs, $e->getMessage()),
            ];
        }
    }
}
