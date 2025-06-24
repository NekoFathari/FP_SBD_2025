<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Anggota;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:30|unique:anggota,username',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'telp' => 'required|string|max:20|unique:anggota,no_hp',
            'alamat' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'name' => $request->name,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Anggota::create([
            'username' => $request->username,
            'alamat' => $request->alamat,
            'no_hp' => $request->telp,
            'tanggal_join' => now(),
        ]);
        Auth::login($user);

        return to_route('dashboard');
    }
}
