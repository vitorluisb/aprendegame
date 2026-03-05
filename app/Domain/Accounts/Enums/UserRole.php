<?php

namespace App\Domain\Accounts\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case SchoolAdmin = 'school_admin';
    case Teacher = 'teacher';
    case Guardian = 'guardian';
    case Student = 'student';
}
