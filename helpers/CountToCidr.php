<?php

/**
 * @copyright Copyright (C) 2021-2025 AIZAWA Hina
 * @license https://github.com/fetus-hina/ipv4.fetus.jp/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@fetus.jp>
 */

declare(strict_types=1);

namespace app\helpers;

use Brick\Math\BigInteger;
use function filter_var;
use const FILTER_VALIDATE_IP;

class CountToCidr
{
    /**
     * @return string[]
     */
    public static function convert(string $startAddress, int $count): ?array
    {
        // 必ず 1 以上
        if ($count < 1) {
            return null;
        }

        // IP 文字列が無効なら処理しない
        if (!filter_var($startAddress, FILTER_VALIDATE_IP)) {
            return null;
        }

        // IPv4 は int、IPv6 は BigInteger で扱う
        $countBI = BigInteger::of($count);

        return IPHelper::splitBlock($startAddress, $countBI);
    }
}
