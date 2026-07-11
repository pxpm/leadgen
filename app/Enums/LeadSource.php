<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSource: string
{
    case Widget = 'widget';
    case MissedCall = 'missed_call';
    case DirectLink = 'direct_link';
    case Manual = 'manual';
}
