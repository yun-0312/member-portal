<?php

namespace App\Enums;

enum UserStatus: int
{
    case Pending = 0;   // 承認待ち
    case Active = 1;    // 承認済み
    case Rejected = 2;  // 却下
    case Retired = 3;   // 退職済み
}
