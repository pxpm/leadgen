<?php

declare(strict_types=1);

namespace App\Enums;

enum FieldType: string
{
    case Text = 'text';
    case Select = 'select';
    case MultiSelect = 'multi_select';
    case Boolean = 'boolean';
    case Number = 'number';
}
