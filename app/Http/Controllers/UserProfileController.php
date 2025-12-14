<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    public function updateProfile(Request $request) 
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255', 
            'email' => 'required|email|unique:users,email,' . $user->id, 
            'profile_picture_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
        ];

        $validatedData = $request->validate($rules);
        
        if ($request->hasFile('profile_picture_path')) {
            if ($user->profile_picture_path) {
                if (Storage::disk('public')->exists($user->profile_picture_path)) {
                    Storage::disk('public')->delete($user->profile_picture_path);
                }
            }
            
            $path = $request->file('profile_picture_path')->store('profiles', 'public');
            $user->profile_picture_path = $path;
        }

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        
        $user->save();
        
        $savedPath = $user->profile_picture_path;
        $profileUrl = null;

        if ($savedPath) {
            $profileUrl = Storage::url($savedPath);
        }

        $user->profile_picture_path = $profileUrl;

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => $user, 
        ], 200);
    }
}
