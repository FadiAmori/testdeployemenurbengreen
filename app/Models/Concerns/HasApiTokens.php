<?php

namespace App\Models\Concerns;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Laravel\Sanctum\Contracts\HasAbilities;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Replacement for Laravel Sanctum's HasApiTokens trait with explicit nullable type hints.
 */
trait HasApiTokens
{
    /**
     * The currently authenticated access token instance.
     */
    protected ?HasAbilities $accessToken = null;

    /**
     * Get all of the API tokens for the user.
     */
    public function tokens(): MorphMany
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    /**
     * Create a new personal access token for the user.
     */
    public function createToken(string $name, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null): NewAccessToken
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(40)),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    /**
     * Determine if the current access token has the given ability.
     */
    public function tokenCan(string $ability): bool
    {
        return $this->accessToken?->can($ability) ?? false;
    }

    /**
     * Determine if the current access token does not have the given ability.
     */
    public function tokenCant(string $ability): bool
    {
        return $this->accessToken?->cant($ability) ?? true;
    }

    /**
     * Get the current access token being used for authentication.
     */
    public function currentAccessToken(): ?HasAbilities
    {
        return $this->accessToken;
    }

    /**
     * Set the current access token for the user.
     */
    public function withAccessToken(HasAbilities $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
