<?php

namespace App\Enums;

enum PageStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
}
