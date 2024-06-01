<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
 
class LoginController
{
    public function redirectToProvider()
    {
        return Socialite::driver('authentik')->redirect();
    }

    public function handleProviderCallback(): RedirectResponse
    {
        $user = Socialite::driver('authentik')->user();

        // dd($user->getEmail());

        $user = User::updateOrCreate([
            'email' => $user->getEmail(),
        ], [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }
}