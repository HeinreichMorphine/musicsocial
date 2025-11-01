<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowerController extends Controller
{
    public function followers(User $user)
    {
        $followers = $user->followers()->paginate(10);
        return view('profile.followers', ['user' => $user, 'followers' => $followers]);
    }

    public function following(User $user)
    {
        $following = $user->following()->paginate(10);
        return view('profile.following', ['user' => $user, 'following' => $following]);
    }
}
