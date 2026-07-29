<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ApoiarController extends Controller
{
    public function index(): View
    {
        return view('apoiar');
    }
}
