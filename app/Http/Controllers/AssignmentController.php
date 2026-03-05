<?php

namespace App\Http\Controllers;

use App\Domain\Accounts\Models\Assignment;
use App\Http\Requests\StoreAssignmentRequest;
use Illuminate\Http\RedirectResponse;

class AssignmentController extends Controller
{
    public function store(StoreAssignmentRequest $request): RedirectResponse
    {
        Assignment::create([
            ...$request->validated(),
            'teacher_id' => auth()->id(),
        ]);

        return back();
    }
}
