<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function scores()
    {
        $scores = Auth::user()->scores()->with('song')->orderBy('created_at', 'desc')->get();
        return view('user.scores', compact('scores'));
    }
}

