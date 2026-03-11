<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }
}
