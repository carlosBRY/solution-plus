<?php

namespace App\Enums;

enum DeteriorationCause: string
{
    case CASSE = 'CASSE';
    case PEREMPTION = 'PEREMPTION';
    case DEFECTUEUX = 'DEFECTUEUX';
    case VOL = 'VOL';
    case DIVERSE = 'DIVERSE';
}
