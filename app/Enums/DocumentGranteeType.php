<?php

namespace App\Enums;

enum DocumentGranteeType: string
{
    case Role = 'role';
    case OrgUnit = 'org_unit';
    case User = 'user';
}
