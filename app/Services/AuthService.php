<?php

namespace App\Services;

use Illuminate\Support\Str;

class AuthService
{
    /**
     * 確認URL用トークンを生成する
     * 
     * @param int $userId 会員ID
     * @return string
     */
    public static function generateTokenForVerificationUrl(int $userId): string
    {
        return dechex($userId) . '-' . Str::uuid();
    }
}
