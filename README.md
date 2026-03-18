# @qrcommunication/rppsapi (PHP)

SDK PHP pour interroger l'ensemble des sources de donnees RPPS (Repertoire Partage des Professionnels de Sante) en une seule requete.

## Installation

```bash
composer require qrcommunication/rppsapi
```

## Quick Start

```php
use QrCommunication\RppsApi\RppsClient;
use QrCommunication\RppsApi\RppsClientOptions;
use QrCommunication\RppsApi\Dto\RppsSearchCriteria;

$client = new RppsClient(new RppsClientOptions(
    fhirApiKey: $_ENV['FHIR_API_KEY'] ?? null,
));

// Profil complet par RPPS
$profil = $client->getByRpps('10005173140');

echo $profil->identite->civiliteExercice->libelle . ' ';
echo $profil->identite->prenomExercice . ' ' . $profil->identite->nomExercice . "\n";
echo $profil->profession->libelle . "\n";

foreach ($profil->activites as $activite) {
    echo "  - " . $activite->structure->raisonSociale . "\n";
}

foreach ($profil->diplomesEtAutorisations as $da) {
    if ($da->diplome) {
        echo "  - [Diplome] " . $da->diplome->libelle . "\n";
    }
}

// Recherche par nom + code postal
$results = $client->search(new RppsSearchCriteria(
    nom: 'DUPONT',
    codePostal: '75',
));

echo "Total: {$results->total}\n";
foreach ($results->results as $r) {
    echo "  {$r->civilite} {$r->prenomExercice} {$r->nomExercice} — {$r->profession->libelle}\n";
}
```

## API Reference

### `RppsClient`

```php
$client = new RppsClient(new RppsClientOptions(
    fhirBaseUrl: 'https://gateway.api.esante.gouv.fr/fhir/v2',  // defaut
    fhirApiKey: 'votre-cle-gravitee',                            // optionnel
    tabularBaseUrl: 'https://tabular-api.data.gouv.fr/api/resources',  // defaut
    timeout: 30.0,                                                // secondes
    tabularPageSize: 100,                                         // defaut
    disabledSources: [],                                          // ex: ['fhir', 'mssante']
));
```

### `getByRpps(string $rpps): RppsFullProfile`

Retourne le profil complet avec identite, profession, activites, diplomes, savoir-faire, cartes CPS, messageries MSSante et donnees FHIR.

### `search(RppsSearchCriteria $criteria, int $page = 1, ?int $pageSize = null): RppsSearchResponse`

Recherche partielle insensible a la casse. Au moins un critere obligatoire.

### `validateRpps(string $rpps): string`

Valide un numero RPPS (11 chiffres). Lance `RppsException` si invalide.

## Sources de donnees

| Source | API | Donnees |
|--------|-----|---------|
| personne-activite | Tabular data.gouv.fr | Identite, profession, structures |
| diplomes | Tabular data.gouv.fr | Diplomes, autorisations |
| savoir-faire | Tabular data.gouv.fr | Specialites, competences |
| carte-cps | Tabular data.gouv.fr | Cartes professionnelles |
| mssante | Tabular data.gouv.fr | Messageries securisees |
| fhir | API FHIR ANS | Donnees FHIR normalisees (cle requise) |

## Configuration FHIR

Obtenir une cle gratuite sur https://portal.api.esante.gouv.fr :
1. Creer un compte
2. Creer une application
3. S'abonner a "API Annuaire Sante en libre acces"
4. Recuperer la cle ESANTE-API-KEY

## Licence

PolyForm Noncommercial 1.0.0 — Voir [LICENSE](./LICENSE)
