<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'organization_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        
        
        // Create org + admin user in a transaction
        $org = null;
        
        
        
        \DB::beginTransaction();
        try {
            $org = Organization::create([
                'name' => $data['organization_name'],
                'schema_name' => Str::slug($data['organization_name'])
            ]);

            $user = User::create([
                'organization_id' => $org->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'admin'
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            \DB::commit();

            return response()->json([
                'token' => $token,
                'user' => $user,
                'organization' => $org,
            ], 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($data)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->load('organization');
            return response()->json(['token' => $token, 'user' => $user, 'organization' => $user->organization]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete();
        }
        return response()->json(['message' => 'Logged out']);
    }
}
