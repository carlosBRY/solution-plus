<?php

namespace App\Enums;

enum ModePaiement: string
{
    case ESPECES = 'ESPECES';
    case CREDIT = 'CREDIT';
    case WAVE = 'WAVE';
    case ORANGE_MONEY = 'ORANGE_MONEY';
    case MTN_MONEY = 'MTN_MONEY';
    case MOOV_MONEY = 'MOOV_MONEY';
    case CARTE = 'CARTE';
    case VIREMENT = 'VIREMENT';
    case AUTRE = 'AUTRE';
}
