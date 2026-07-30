<?php

namespace App\Enums;

enum StatutVente: string
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case PAYEE = 'PAYEE';
    case ANNULEE = 'ANNULEE';
}
