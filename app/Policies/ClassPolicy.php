<?php

namespace App\Policies;

use App\Domain\Accounts\Models\SchoolClass;
use App\Domain\Accounts\Models\Student;
use App\Models\User;

class ClassPolicy
{
    public function view(User $user, SchoolClass $schoolClass): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return (int) $user->school_id === (int) $schoolClass->school_id;
    }

    public function addStudent(User $user, SchoolClass $schoolClass, Student $student): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return (int) $user->school_id === (int) $schoolClass->school_id
            && (int) $schoolClass->school_id === (int) $student->school_id;
    }
}
