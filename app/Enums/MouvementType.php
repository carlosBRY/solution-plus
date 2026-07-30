<?php

namespace App\Enums;

enum MouvementType: string
{
    case ENTREE = 'ENTREE';
    case SORTIE = 'SORTIE';
    case AJUSTEMENT = 'AJUSTEMENT';
    case RETOUR = 'RETOUR';
    case STOCK_INITIAL = 'STOCK_INITIAL';
    case DETERIORATION = 'DETERIORATION';
}
