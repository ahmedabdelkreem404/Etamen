<?php

namespace App\Modules\Identity\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Patients\Infrastructure\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function register(array $data, Request $request): array
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Role::findOrCreate(UserRole::Patient->value);
        $user->assignRole(UserRole::Patient->value);

        PatientProfile::query()->create([
            'user_id' => $user->id,
        ]);

        $token = $user->createToken($request->userAgent() ?: 'api-token')->plainTextToken;

        $this->auditLogService->log('auth.register', $user, $user, request: $request);

        return [
            'user' => $user->refresh(),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function login(array $credentials, Request $request): ?array
    {
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        $tokenName = $credentials['device_name'] ?? $request->userAgent() ?: 'api-token';
        $token = $user->createToken($tokenName)->plainTextToken;

        $this->auditLogService->log('auth.login', $user, $user, request: $request);

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function logout(User $user, Request $request): void
    {
        $this->auditLogService->log('auth.logout', $user, $user, request: $request);

        $user->tokens()->delete();
    }
}
