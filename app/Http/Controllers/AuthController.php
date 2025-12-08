<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Login; // Necessary for Login History (Successful)
use Illuminate\Auth\Events\Failed; // Necessary for Login History (Failed)

class AuthController extends Controller
{
    /**
     * Handles user registration and API token creation.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user, 
            'token' => $token
        ], 201);
    }

    /**
     * Handles user login for API tokens, ensuring Login History is recorded.
     */
    public function login(Request $request)
    {
        // 1. Validate request and assign to $credentials
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // 2. Find the user and verify the password manually
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            
            // --- FAILURE BLOCK (Log History) ---
            // CHECKLIST: Dispatch Failed Event for Audit/Login History
            event(new Failed('api', $credentials, $request)); 
            
            // Return 401 JSON error expected by Vue
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        // --- SUCCESS BLOCK (Log History & API Token Generation) ---
        
        // Log the user in to set up any session/guard state (optional but safer)
        Auth::login($user); 

        // CHECKLIST: Dispatch Login Event for Audit/Login History
        // Using 'web' here ensures the session guard logic is handled if needed.
        event(new Login('web', $user, $request->boolean('remember'))); 

        // Generate token and return JSON response expected by the frontend
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user, 
            'token' => $token
        ], 200);
    }

    /**
     * Retrieves the authenticated user's profile information.
     */
    public function me()
    {
        $user = Auth::user(); 

        // Convert stored path to usable public URL for the frontend
        if ($user && $user->profile_picture_path) {
            $user->profile_picture_path = Storage::url($user->profile_picture_path);
        }
        
        return response()->json([
            'user' => $user
        ], 200);
    }
}