<?php

namespace App\Http\Controllers;


    use Illuminate\Http\Request;
    use App\Models\User;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Validation\ValidationException;

    class AuthController extends Controller
    {
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

        public function login(Request $request)
        {
            $validate = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $validate['email'])->first();
            if (!$user || ! Hash::check($validate['password'], $user->password)) {
                return response()->json(['message' => 'Invalid login details'], 401);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'user' => $user, 
                'token' => $token
            ], 200);
        }
    }

