<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;

class GoogleAccount extends Model
{
    protected $table = 'google_accounts';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function locations()
    {
        return $this->hasMany(GoogleLocation::class, 'google_account_id');
    }

    /**
     * Check whether the stored access token has expired.
     */
    public function isExpired(): bool
    {
        if (! $this->token_expiry) {
            return true;
        }

        return Carbon::parse($this->token_expiry)->isPast();
    }

    /**
     * Encrypt a token value for storage.
     */
    public function encryptToken(string $token): string
    {
        return encrypt($token);
    }

    /**
     * Decrypt a stored token value. Returns empty string on failure.
     */
    public function decryptToken(string $encrypted): string
    {
        try {
            return decrypt($encrypted);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * Retrieve the plain-text access token.
     */
    public function getPlainAccessToken(): string
    {
        if (empty($this->access_token)) {
            return '';
        }
        return $this->decryptToken($this->access_token);
    }

    /**
     * Retrieve the plain-text refresh token.
     */
    public function getPlainRefreshToken(): string
    {
        if (empty($this->refresh_token)) {
            return '';
        }
        return $this->decryptToken($this->refresh_token);
    }
}
