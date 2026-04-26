<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FcmToken extends Model
{
    protected $table = 's_fcm_tokens';

    protected $fillable = ['user_id', 'token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Register a token for a user.
     * If the token already belongs to another user (device switch), it is reassigned.
     * If the user already has this token, the timestamp is refreshed.
     */
    public static function register(string $userId, string $token): void
    {
        // Remove from any other user who has this token (device switched accounts)
        static::where('token', $token)
            ->where('user_id', '!=', $userId)
            ->delete();

        // Upsert: update timestamp if exists, create if not
        static::updateOrCreate(
            ['user_id' => $userId, 'token' => $token],
            ['updated_at' => now()]
        );
    }

    /**
     * Remove a specific token entirely.
     */
    public static function revoke(string $token): void
    {
        static::where('token', $token)->delete();
    }
}
