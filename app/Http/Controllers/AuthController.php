<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
   /**
    * Show login form.
    */
   public function showLogin()
   {
      return view('auth.login');
   }

   /**
    * Show registration form.
    */
   public function showRegister()
   {
      return view('auth.register');
   }

   /**
    * Handle web login.
    */
   public function login(Request $request)
   {
      $credentials = $request->validate([
         'email' => 'required|email',
         'password' => 'required',
      ]);

      if (Auth::attempt($credentials, $request->boolean('remember'))) {
         $request->session()->regenerate();
         return redirect()->intended('/dashboard');
      }

      return back()->withErrors([
         'email' => 'The provided credentials do not match our records.',
      ])->onlyInput('email');
   }

   /**
    * Handle web registration.
    */
   public function register(Request $request)
   {
      $validated = $request->validate([
         'name' => 'required|string|max:255',
         'email' => 'required|string|email|max:255|unique:users',
         'password' => ['required', 'confirmed', Password::min(8)],
      ]);

      $user = User::create([
         'name' => $validated['name'],
         'email' => $validated['email'],
         'password' => $validated['password'],
      ]);

      Auth::login($user);

      return redirect('/dashboard');
   }

   /**
    * Handle web logout.
    */
   public function logout(Request $request)
   {
      Auth::logout();
      $request->session()->invalidate();
      $request->session()->regenerateToken();
      return redirect('/login');
   }

    // ─── API Authentication (Sanctum Token) ───────────────────

   /**
    * API register — returns token.
    */
   public function apiRegister(Request $request)
   {
      $validated = $request->validate([
         'name' => 'required|string|max:255',
         'email' => 'required|string|email|max:255|unique:users',
         'password' => ['required', 'confirmed', Password::min(8)],
      ]);

      $user = User::create([
         'name' => $validated['name'],
         'email' => $validated['email'],
         'password' => $validated['password'],
      ]);

      $token = $user->createToken('api-token')->plainTextToken;

      return response()->json([
         'user' => $user,
         'token' => $token,
      ], 201);
   }

   /**
    * API login — returns token.
    */
   public function apiLogin(Request $request)
   {
      $request->validate([
         'email' => 'required|email',
         'password' => 'required',
      ]);

      $user = User::where('email', $request->email)->first();

      if (!$user || !Hash::check($request->password, $user->password)) {
         return response()->json([
            'message' => 'Invalid credentials.',
         ], 401);
      }

      $token = $user->createToken('api-token')->plainTextToken;

      return response()->json([
         'user' => $user,
         'token' => $token,
      ]);
   }

   /**
    * API logout — revoke current token.
    */
   public function apiLogout(Request $request)
   {
      $request->user()->currentAccessToken()->delete();

      return response()->json(['message' => 'Logged out successfully.']);
   }
}
