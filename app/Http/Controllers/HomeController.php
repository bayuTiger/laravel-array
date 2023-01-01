<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\Home\StoreRequest;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function store(StoreRequest $request)
    {
        if (is_array($request->categories)) {
            User::find(Auth::user()->id)->categories()->sync($request->categories);
        }

        $categories = Auth::user()->categories;
        $status = 'カテゴリーを登録しました！';

        return redirect()->route('home')->with(compact('status', 'categories'));
    }
}
