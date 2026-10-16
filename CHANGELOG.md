# Changelog

Toutes les modifications notables de ce projet sont documentees dans ce fichier.

Format base sur [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/).

## [1.1.2] - 2026-10-16

### Modifie

- Dependances : `guzzlehttp/guzzle` elargi a `^7.0 || ^8.0`, `guzzlehttp/promises`
  a `^2.0 || ^3.0` et `phpunit/phpunit` a `^10.0 || ^11.0 || ^12.0 || ^13.0`.
  Les anciennes majeures restent autorisees ; la resolution locale recente utilise
  Guzzle 8.2.0, Promises 3.0.2 et PHPUnit 13.4.3.
- Workflows GitHub Actions actualises avec des actions epinglees par SHA et Composer 2.10.3.
  La matrice configuree couvre PHP 8.2, 8.3, 8.4 et 8.5 avec les dependances recentes,
  ainsi qu'une resolution basse sur PHP 8.2, sans contournement des controles de securite.
- Release preparee avec validation stricte du tag stable, du commit et de sa presence dans
  l'historique de `main`, reutilisation de la matrice de tests et notes de version obligatoires.

### Corrige

- Minimum PHP declare corrige de `>=8.1` a `>=8.2` pour refleter les classes `readonly`
  deja presentes dans le SDK et ses DTOs. Leur syntaxe exigeait deja PHP 8.2 ; aucune
  reecriture de l'API n'est introduite et la compatibilite PHP 8.1 n'est plus annoncee.
  Cette correction de metadonnee resserre la contrainte visible par Composer.

### Ajoute

- Neuf tests PHPUnit hors reseau utilisant le vrai client Guzzle, un transport simule strict
  et des donnees synthetiques : mapping des reponses, pagination, deduplication, query,
  en-tetes, erreurs HTTP/connexion et validation avant transport. Aucun appel RPPS/ANS/FHIR.
- Commande `composer test` avec bootstrap de l'autoloader et echec sur suite vide,
  test risque ou avertissement, compatible avec les interfaces CLI PHPUnit 10 a 13.

### Verification locale

- Sous PHP 8.4.26 et Composer 2.10.3 : neuf tests et 58 assertions passes, aucun saut,
  avertissement ou echec ; 35 paquets installes sur 35 a leur derniere version stable,
  audit Composer sans vulnerabilite connue et verification de plateforme reussie.
- La matrice CI distante doit reussir sur le commit livre avant la publication.
  Les preuves locales ne remplacent pas cette verification.

## [1.0.0] - 2026-03-18

### Ajoute

- Client `RppsClient` avec methode `getByRpps()` pour recuperer le profil complet d'un praticien
- Methode `search()` pour rechercher par nom, prenom et/ou code postal (recherche partielle insensible a la casse)
- 6 sources de donnees interrogees :
  - API FHIR Annuaire Sante (ANS) — necessite cle Gravitee
  - Fichier personne-activite (data.gouv.fr Tabular API)
  - Fichier diplomes et autorisations (data.gouv.fr Tabular API)
  - Fichier savoir-faire (data.gouv.fr Tabular API)
  - Fichier cartes CPS/CPF (data.gouv.fr Tabular API)
  - Fichier messageries MSSante (data.gouv.fr Tabular API)
- DTOs readonly PHP 8.1+ pour toutes les structures de donnees
- Gestion d'erreurs avec `safeQuery()` — une source en erreur ne bloque pas les autres
- Validation du numero RPPS (11 chiffres)
- Auto-desactivation de la source FHIR sans cle API
- Pagination automatique des resultats Tabular API
- Deduplications par RPPS dans les resultats de recherche
