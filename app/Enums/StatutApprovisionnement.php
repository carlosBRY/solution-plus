<?php

namespace App\Enums;

enum StatutApprovisionnement: string
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case RECEPTIONNE = 'RECEPTIONNE';
    case ANNULE = 'ANNULE';
}
