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

        // 1. Validation for ALL fields
        $rules = [
            'name' => 'required|string|max:255', 
            'email' => 'required|email|unique:users,email,' . $user->id, 
            'profile_picture_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
        ];

        $validatedData = $request->validate($rules);
        
        // --- 2. Handle File Upload (Optional) ---
        if ($request->hasFile('profile_picture_path')) {
            // Delete the old file if it exists
            if ($user->profile_picture_path) {
                // Use the existing path to check for existence
                if (Storage::disk('public')->exists($user->profile_picture_path)) {
                    Storage::disk('public')->delete($user->profile_picture_path);
                }
            }
            
            // 🎯 FIXED: Changed 'profile_images' to 'profiles' to match your directory structure
            $path = $request->file('profile_picture_path')->store('profiles', 'public');
            
            // Save the internal path to the database
            $user->profile_picture_path = $path;
        }

        // --- 3. Update Text Fields ---
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        
        // --- 4. Final Save ---
        $user->save();
        
        // --- 5. Prepare Response Data ---
        
        $savedPath = $user->profile_picture_path;
        $profileUrl = null;

        if ($savedPath) {
            // 🎯 FIX: Generate the full URL, which is now possible with the correct path
            $profileUrl = Storage::url($savedPath);
        }

        // Manually override the path property on the user object before sending it to Flutter.
        $user->profile_picture_path = $profileUrl;

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => $user, 
        ], 200);
    }
}