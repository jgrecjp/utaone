<?php
namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class TopController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
 //       $this->middleware('auth');
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $songs = Song::inRandomOrder()->take(5)->get();
        return view('welcome', compact('songs'));
    }
}
