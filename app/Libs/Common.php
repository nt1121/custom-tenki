<?php

namespace App\Libs;

use Illuminate\Support\Str;

class Common
{
    /**
     * 確認URL用のトークンを生成する
     * 
     * @param  int $userId 会員ID
     * @return string
     */
    public static function generateConfirmationUrlToken(int $userId): string
    {
        return dechex($userId) . '-' . Str::uuid();
    }
}

