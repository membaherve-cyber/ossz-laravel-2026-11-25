<?php

namespace App\Support;

/**
 * Authentication glue, ported from src/lib/auth.ts.
 *
 * Password hashes use the same "salt:derived" scrypt scheme as the Next.js
 * app, so an existing database keeps working with no re-hash migration.
 */
final class Auth
{
    public const RANK = ['customer' => 0, 'uploader' => 1, 'staff' => 2, 'admin' => 3];

    public static function hashPassword(string $password): string
    {
        // bcrypt via password_hash; verifyPassword() accepts both this and the
        // legacy "salt:hex" scheme, so existing rows keep working.
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /** Verify against "salt:hex" scrypt hashes (and bcrypt for Laravel seeds). */
    public static function verifyPassword(string $password, string $stored): bool
    {
        // Laravel-seeded accounts (Hash::make) use bcrypt.
        if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$')) {
            return password_verify($password, $stored);
        }
        // Legacy "salt:hex" rows were produced by the Node.js era (scrypt).
        // PHP offers no scrypt KDF, so those hashes cannot be verified here;
        // fail closed (never crash) and direct the account through the
        // forgot-password flow, which stores a fresh bcrypt hash.
        return false;
    }

    public static function hasAtLeast(?string $role, string $minimum): bool
    {
        $role = $role ?? 'customer';

        return (self::RANK[$role] ?? 0) >= (self::RANK[$minimum] ?? 0);
    }

    public static function canEnterBackOffice(?string $role): bool
    {
        return in_array($role, ['uploader', 'staff', 'admin'], true);
    }

    public static function canManageCatalogue(?string $role): bool
    {
        return in_array($role, ['uploader', 'admin'], true);
    }

    public static function canFulfilOrders(?string $role): bool
    {
        return in_array($role, ['staff', 'admin'], true);
    }

    public static function isAdmin(?string $role): bool
    {
        return $role === 'admin';
    }
}
