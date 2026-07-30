<?php

namespace App\Enums;

enum ModePaiement: string
{
    case ESPECES = 'ESPECES';
    case CREDIT = 'CREDIT';
    case ORANGE_MONEY = 'ORANGE_MONEY';
    case MOOV_MONEY = 'MOOV_MONEY';
    case CARTE = 'CARTE';
    case VIREMENT = 'VIREMENT';
}
