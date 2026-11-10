<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use App\Notifications\VerifyEmailAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new marketplace user and return an API token.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6', 'max:72'],
            'location' => ['nullable', 'string', 'max:80'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed via model cast
            'location' => $data['location'] ?? null,
        ]);

        return response()->json([
            'token' => $user->createToken('declutter-ng')->plainTextToken,
            'user' => $this->userPayload($user),
        ], 201);
    }

    /**
     * Sign in with email + password and return an API token.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken('declutter-ng')->plainTextToken,
            'user' => $this->userPayload($user),
        ]);
    }

    /**
     * Sign out — revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Signed out.']);
    }

    /**
     * Return the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    /**
     * Update basic profile details (name, location).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'min:2', 'max:80'],
            'location' => ['nullable', 'string', 'max:80'],
        ]);

        $request->user()->update($data);

        return response()->json(['user' => $this->userPayload($request->user()->fresh())]);
    }

    /**
     * Send a verification email to the authenticated user.
     */
    public function sendVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.', 'verified' => true]);
        }

        // Invalidate previous unused tokens for this user.
        EmailVerification::where('user_id', $user->id)->whereNull('used_at')->delete();

        $token = Str::random(64);
        EmailVerification::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHour(),
        ]);

        $user->notify(new VerifyEmailAddress($token));

        return response()->json(['message' => 'Verification email sent.']);
    }

    /**
     * Verify an email address using the token from the email link.
     * Public endpoint — the token itself is the credential.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'size:64']]);

        $record = EmailVerification::where('token_hash', hash('sha256', $data['token']))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            return response()->json(['message' => 'Invalid or expired verification link.'], 422);
        }

        $record->update(['used_at' => now()]);
        $record->user->forceFill(['email_verified_at' => now()])->save();

        return response()->json(['message' => 'Email verified.', 'verified' => true]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'location' => $user->location,
            'joined' => $user->created_at?->year,
            'email_verified' => $user->email_verified_at !== null,
        ];
    }
}
