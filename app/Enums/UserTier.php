<?php

namespace App\Enums;

enum UserTier: string
{
    case BASIC = 'basic';
    case PLUS = 'plus';
    case PRO = 'pro';
}