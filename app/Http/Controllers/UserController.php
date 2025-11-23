<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Ensure the User model is accessible

class UserController extends Controller
{
    /**
     * Get the details of the currently authenticated user.
     * This is used by the /api/user and /api/me routes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function current(Request $request): JsonResponse
    {
        // The 'auth:sanctum' middleware ensures $request->user() is not null.
        return response()->json($request->user());
    }
    
    /**
     * Update the profile information and optional profile picture of the authenticated user.
     * This method handles the file upload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        // 1. Get the authenticated user
        $user = $request->user();
        
        try {
            // 2. Validate incoming data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                // Allow changing email only if it's unique (excluding the current user's email)
                'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
                // The image field is optional, accepts file uploads, and must be an image
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
                'password' => 'nullable|string|min:8|confirmed', // Optional password change
            ]);

            $updateData = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
            ];

            // 3. Handle File Upload (Profile Picture)
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if it exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                
                // Store the new image in the 'public/profiles' directory
                // The 'public' disk maps to storage/app/public, which must be symlinked
                $path = $request->file('profile_picture')->store('profiles', 'public');
                $updateData['profile_picture'] = $path;
            }

            // 4. Handle Password Change
            if (!empty($validatedData['password'])) {
                $updateData['password'] = Hash::make($validatedData['password']);
            }
            
            // 5. Update the user model
            $user->update($updateData);

            // 6. Return the updated user data
            return response()->json([
                'message' => 'Profile updated successfully.',
                'user' => $user
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Profile update failed for user ID ' . $user->id . ': ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred during profile update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // --- Unused default resource methods below (kept for completeness) ---

    public function index() { /* ... */ }
    public function create() { /* ... */ }
    public function store(Request $request) { /* ... */ }
    public function show(string $id) { /* ... */ }
    public function edit(string $id) { /* ... */ }
    public function update(Request $request, string $id) { /* ... */ }
    public function destroy(string $id) { /* ... */ }
}