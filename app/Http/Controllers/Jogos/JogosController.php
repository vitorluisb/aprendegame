<?php

namespace App\Http\Controllers\Jogos;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class JogosController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Jogos/Index');
    }
}
