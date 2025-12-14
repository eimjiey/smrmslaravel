<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function current(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
    
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            $updateData = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
            ];

            if ($request->hasFile('profile_picture')) {
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                
                $path = $request->file('profile_picture')->store('profiles', 'public');
                $updateData['profile_picture'] = $path;
            }

            if (!empty($validatedData['password'])) {
                $updateData['password'] = Hash::make($validatedData['password']);
            }
            
            $user->update($updateData);

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

    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
