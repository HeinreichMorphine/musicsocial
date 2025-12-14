<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Share;
use App\Models\Comment;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AdminController extends Controller
{
    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $userCount = User::count();
        $shareCount = Share::count();
        $commentCount = Comment::count();
        
        return view('admin.dashboard', compact('userCount', 'shareCount', 'commentCount'));
    }

    public function users()
    {
        $users = User::paginate(10);
        return view('admin.users', compact('users'));
    }

    public function banUser($id)
    {
        $user = User::findOrFail($id);
        $user->is_banned = !$user->is_banned;
        $user->save();

        $status = $user->is_banned ? 'banned' : 'unbanned';
        return redirect()->back()->with('success', "User has been {$status}.");
    }

    public function deleteUser($id)
    {
        User::destroy($id);
        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function moderation()
    {
        $shares = Share::with('user')->latest()->paginate(10);
        $comments = Comment::with('user')->latest()->paginate(10);
        return view('admin.moderation', compact('shares', 'comments'));
    }

    public function deleteShare($id)
    {
        Share::destroy($id);
        return redirect()->back()->with('success', 'Share deleted successfully.');
    }

    public function deleteComment($id)
    {
        Comment::destroy($id);
        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }



    public function retrainPage(Request $request)
    {
        $recommendations = null;
        $users = User::select('id', 'name')->get(); // Fetch all users for the dropdown

        if ($request->has('user_id')) {
            $userId = $request->input('user_id');
            // Call Flask API to get recommendations
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', "http://127.0.0.1:5000/recommendations/{$userId}");
                $data = json_decode($response->getBody(), true);
                if (isset($data['recommendations'])) {
                    $rawRecommendations = $data['recommendations'];
                    $songIds = collect($rawRecommendations)->pluck('song_id')->all();
                    $songs = \App\Models\Song::whereIn('id', $songIds)->get()->keyBy('id');

                    $recommendations = [];
                    foreach ($rawRecommendations as $rec) {
                        $song = $songs->get($rec['song_id']);
                        $rec['song_name'] = $song ? $song->track_name : 'Unknown Song';
                        $recommendations[] = $rec;
                    }
                }
            } catch (\Exception $e) {
                // Fallback or error handling
                // For now, just return null or empty array
            }
        }
        return view('admin.retrain', compact('recommendations', 'users'));
    }


    public function profile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;

        if ($request->filled('password')) {
            $admin->password = bcrypt($request->password);
        }

        $admin->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
