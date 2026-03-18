<?php

declare(strict_types=1);

namespace QrCommunication\RppsApi\Enum;

enum SourceName: string
{
    case Fhir = 'fhir';
    case PersonneActivite = 'personne-activite';
    case Diplomes = 'diplomes';
    case SavoirFaire = 'savoir-faire';
    case CarteCps = 'carte-cps';
    case Mssante = 'mssante';
}
