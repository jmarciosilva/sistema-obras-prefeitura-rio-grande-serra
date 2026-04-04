<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ManualController extends Controller
{
    public function index(): View
    {
        return view('manual.index', [
            'perfil' => auth()->user()->perfil,
            'nome'   => auth()->user()->name,
        ]);
    }
}
