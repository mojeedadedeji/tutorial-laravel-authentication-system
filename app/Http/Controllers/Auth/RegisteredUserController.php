<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private SmsService $sms) {}

    /**
     * Show the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Validate details, send an OTP to the phone, then show the verify step.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('phone')) {
            $request->merge(['phone' => $this->sms->normalize($request->phone)]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'min:10', 'max:20', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $validated['phone'] = $this->sms->normalize($validated['phone']);

        $this->issueOtp($validated['phone']);

        $request->session()->put('registration.pending', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password_hash' => Hash::make($validated['password']),
        ]);

        return redirect()->route('register.verify')
            ->with('status', 'We sent a 6-digit code to your phone.');
    }

    /**
     * Show the OTP verification step.
     */
    public function verifyForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('registration.pending')) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp');
    }

    /**
     * Verify the OTP and create the account.
     */
    public function verify(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('registration.pending');

        if (! $pending) {
            return redirect()->route('register');
        }

        $request->validate(['otp' => ['required', 'digits:6']]);

        $key = 'otp:' . $pending['phone'];
        $stored = Cache::get($key);

        if (! $stored || ! Hash::check($request->otp, $stored)) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        Cache::forget($key);
        $request->session()->forget('registration.pending');

        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'phone' => $pending['phone'],
            'password' => $pending['password_hash'],
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Resend the OTP to the pending phone number.
     */
    public function resend(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('registration.pending');

        if (! $pending) {
            return redirect()->route('register');
        }

        $this->issueOtp($pending['phone']);

        return back()->with('status', 'A new code has been sent to your phone.');
    }

    /**
     * Generate, store (hashed) and send a 6-digit OTP.
     */
    private function issueOtp(string $phone): void
    {
        $code = (string) random_int(100000, 999999);

        Cache::put('otp:' . $phone, Hash::make($code), now()->addMinutes(10));

        $this->sms->send($phone, "Your 5050 Logistics verification code is {$code}. It expires in 10 minutes.");
    }
}
