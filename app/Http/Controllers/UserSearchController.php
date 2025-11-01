<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('user');
        $users = collect(); // Initialize as an empty collection

        if ($query) {
            $users = User::where('name', 'like', '%' . $query . '%')
                         ->paginate(10);
        }

        return view('user.search-results', [
            'users' => $users,
            'searchQuery' => $query,
        ]);
    }
}
