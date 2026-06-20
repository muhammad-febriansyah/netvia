<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterCustomerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Paket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register', [
            'pakets' => Paket::query()->where('is_active', true)->orderBy('nama')->get(),
        ]);
    }

    public function register(RegisterRequest $request, RegisterCustomerAction $action): RedirectResponse
    {
        $user = $action->execute($request->registrationData());

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('portal.dashboard')
            ->with('success', 'Pendaftaran berhasil. Akun Anda menunggu aktivasi admin.');
    }
}
