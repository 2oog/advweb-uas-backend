<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $token = auth()->user()->createToken('pos-app')->plainTextToken;
        return view('pos.index', compact('token'));
    }

    public function history()
    {
        $token = auth()->user()->createToken('pos-app')->plainTextToken;
        return view('pos.history', compact('token'));
    }
}
