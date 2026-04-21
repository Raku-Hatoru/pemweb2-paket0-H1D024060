<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Member;
use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'role' => UserRole::Anggota,
            ]);

            Member::create([
                'user_id' => $user->getKey(),
                'member_code' => $this->nextMemberCode(),
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route($user->dashboardRouteName(), absolute: false));
    }

    private function nextMemberCode(): string
    {
        $sequence = (Member::query()->max('id') ?? 0) + 1;

        do {
            $memberCode = 'AGT-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (Member::query()->where('member_code', $memberCode)->exists());

        return $memberCode;
    }
}
