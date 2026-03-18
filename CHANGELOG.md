# Changelog

Toutes les modifications notables de ce projet sont documentees dans ce fichier.

Format base sur [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/).

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
