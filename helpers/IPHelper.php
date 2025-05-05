<?php
/**
 * IPv4 / IPv6 utility helper
 * Handles validation, conversion, masking and CIDR-range splitting
 *
 * @copyright Copyright (C) 2025 Neuron-Grid
 * @license   MIT
 * @author    AIZAWA Hina <hina@fetus.jp>
 */

declare(strict_types=1);

namespace app\helpers;

use Brick\Math\BigInteger;
use InvalidArgumentException;

use function filter_var;
use function inet_ntop;
use function inet_pton;
use function pack;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strpos;

/**
 * Utility methods for both IPv4 and IPv6.
 *
 * Public API:
 *  - isValid(string $ip): bool
 *  - toBinary(string $ip): string          4 or 16‑byte packed binary
 *  - fromBinary(string $packed): string
 *  - toInteger(string $ip): BigInteger
 *  - maskBits(int $bits, int $version): string  packed binary netmask
 *  - splitBlock(string $startIp, int|BigInteger $count): array<string>  CIDR list
 */
final class IPHelper
{
    private const MIN_SPLIT_V4 = 8;  // Do not create /9…/32 fragments
    private const IPV4_BITS    = 32;
    private const IPV6_BITS    = 128;

    /* ---------- Validation & basic conversion ---------- */

    public static function isValid(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    public static function toBinary(string $ip): string
    {
        $packed = inet_pton($ip);
        if ($packed === false) {
            throw new InvalidArgumentException("Invalid IP address: {$ip}");
        }
        return $packed;           // 4 or 16 bytes
    }

    public static function fromBinary(string $packed): string
    {
        return inet_ntop($packed);
    }

    public static function toInteger(string $ip): BigInteger
    {
        return BigInteger::of(bin2hex(self::toBinary($ip)), 16);
    }

    /* ---------- Netmask helpers ---------- */

    /**
     * Return packed binary netmask for given bit‑length.
     *
     * @throws InvalidArgumentException
     */
    public static function maskBits(int $bits, int $version): string
    {
        $max = $version === 4 ? self::IPV4_BITS : self::IPV6_BITS;
        if ($bits < 0 || $bits > $max) {
            throw new InvalidArgumentException('Invalid netmask length');
        }

        // IPv4 uses 32‑bit integer packed as big‑endian
        if ($version === 4) {
            $mask = $bits === 0 ? 0 : ((0xFFFFFFFF << (32 - $bits)) & 0xFFFFFFFF);
            return pack('N', $mask);
        }

        // IPv6 – build binary string of 128 bits
        if ($bits === 0) {
            return str_repeat("\0", 16);
        }
        $ones  = str_repeat('1', $bits);
        $zeros = str_repeat('0', self::IPV6_BITS - $bits);
        /** @noinspection BinaryStringFunctionsInspection */
        return hex2bin(sprintf('%032s', dechex(bindec($ones . $zeros))));
    }

    /* ---------- CIDR splitting ---------- */

    /**
     * Split a continuous block (start + count) into minimum CIDR blocks.
     *
     * @param int|BigInteger $count  number of addresses (>=1)
     * @return string[]              e.g. ["192.0.2.0/24", "2001:db8::/32"]
     */
    public static function splitBlock(string $startIp, $count): array
    {
        $result  = [];
        $version = strpos($startIp, ':') === false ? 4 : 6;
        $maxBits = $version === 4 ? self::IPV4_BITS : self::IPV6_BITS;

        $start = self::toInteger($startIp);
        $end   = $start->plus(BigInteger::of($count)->minus(1));

        while ($start->compareTo($end) <= 0) {
            $lsb         = $start->getLowestSetBit();          // trailing zero bits
            $largestMask = $maxBits - $lsb;

            $remaining   = $end->minus($start)->plus(1)->bitLength();
            $remainMask  = $maxBits - $remaining + 1;

            $cidrLen = min($largestMask, $remainMask);

            $result[] = sprintf('%s/%d', self::fromBinary(
                hex2bin(str_pad($start->toBase(16), $version === 4 ? 8 : 32, '0', STR_PAD_LEFT))
            ), $cidrLen);

            $start = $start->plus(BigInteger::one()->shiftedLeft($maxBits - $cidrLen));
        }

        return $result;
    }
}
