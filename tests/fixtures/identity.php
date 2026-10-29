<?php

/** Supplies isolated identities while real Yii RBAC evaluates their permissions. */
class RegressionIdentity implements yii\web\IdentityInterface
{
    /** @param int $id User ID in the isolated RBAC assignments. */
    public function __construct(private int $id = 1) {}

    /** @param int|string $id Fixture user ID. @return self Resolved fixture identity. */
    public static function findIdentity($id): self { return new self((int) $id); }

    /** @param mixed $token Unused token. @param mixed $type Unused token type. @return null Token login is disabled. */
    public static function findIdentityByAccessToken($token, $type = null): ?self { return null; }

    /** @return int Fixture user ID. */
    public function getId(): int { return $this->id; }

    /** @return string No persistent authentication cookie is used. */
    public function getAuthKey(): string { return ''; }

    /** @param string $authKey Untrusted cookie key. @return bool Persistent login is disabled. */
    public function validateAuthKey($authKey): bool { return false; }
}
