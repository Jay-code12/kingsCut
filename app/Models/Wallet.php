<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Wallet
{
    public static function getOrCreateForCustomer(int $customerId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM wallets WHERE customer_id = ? LIMIT 1');
        $stmt->execute([$customerId]);
        $wallet = $stmt->fetch();
        if ($wallet) {
            return $wallet;
        }
        $stmt = $db->prepare('INSERT INTO wallets (customer_id, balance) VALUES (?, 0)');
        $stmt->execute([$customerId]);
        return self::getOrCreateForCustomer($customerId);
    }

    /**
     * Recent transactions for a wallet, optionally filtered to one or more
     * subscriptions (plans). Pass null for $subscriptionIds to see everything;
     * pass an array (even with just one id) to filter to specific plan(s).
     * Rows with no subscription_id (e.g. plain top-ups) are only included
     * when no filter is applied.
     */
    public static function transactions(int $walletId, int $limit = 20, ?array $subscriptionIds = null): array
    {
        $db = Database::getInstance();

        if ($subscriptionIds === null) {
            $stmt = $db->prepare(
                'SELECT wt.*, s.membership_id, p.name AS plan_name
                 FROM wallet_transactions wt
                 LEFT JOIN subscriptions s ON s.id = wt.subscription_id
                 LEFT JOIN plans p ON p.id = s.plan_id
                 WHERE wt.wallet_id = ?
                 ORDER BY wt.created_at DESC LIMIT ?'
            );
            $stmt->bindValue(1, $walletId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        if (empty($subscriptionIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($subscriptionIds), '?'));
        $sql = "SELECT wt.*, s.membership_id, p.name AS plan_name
                FROM wallet_transactions wt
                LEFT JOIN subscriptions s ON s.id = wt.subscription_id
                LEFT JOIN plans p ON p.id = s.plan_id
                WHERE wt.wallet_id = ? AND wt.subscription_id IN ($placeholders)
                ORDER BY wt.created_at DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $walletId, PDO::PARAM_INT);
        $i = 2;
        foreach ($subscriptionIds as $id) {
            $stmt->bindValue($i++, $id, PDO::PARAM_INT);
        }
        $stmt->bindValue($i, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Credit the wallet (e.g. top-up). Returns the new balance. */
    public static function credit(int $walletId, float $amount, string $description, string $referenceType = 'topup', ?int $referenceId = null, ?int $subscriptionId = null): float
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('UPDATE wallets SET balance = balance + ? WHERE id = ?');
            $stmt->execute([$amount, $walletId]);

            $stmt = $db->prepare(
                'INSERT INTO wallet_transactions (wallet_id, subscription_id, type, amount, description, reference_type, reference_id)
                 VALUES (?, ?, "credit", ?, ?, ?, ?)'
            );
            $stmt->execute([$walletId, $subscriptionId, $amount, $description, $referenceType, $referenceId]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        $stmt = $db->prepare('SELECT balance FROM wallets WHERE id = ?');
        $stmt->execute([$walletId]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Debit the wallet, capped at the current balance (never goes negative).
     * Returns ['success' => bool, 'balance' => float].
     */
    public static function debit(int $walletId, float $amount, string $description, string $referenceType = 'service_payment', ?int $referenceId = null, ?int $subscriptionId = null): array
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('SELECT balance FROM wallets WHERE id = ? FOR UPDATE');
            $stmt->execute([$walletId]);
            $balance = (float) $stmt->fetchColumn();

            if ($balance < $amount) {
                $db->rollBack();
                return ['success' => false, 'balance' => $balance];
            }

            $stmt = $db->prepare('UPDATE wallets SET balance = balance - ? WHERE id = ?');
            $stmt->execute([$amount, $walletId]);

            $stmt = $db->prepare(
                'INSERT INTO wallet_transactions (wallet_id, subscription_id, type, amount, description, reference_type, reference_id)
                 VALUES (?, ?, "debit", ?, ?, ?, ?)'
            );
            $stmt->execute([$walletId, $subscriptionId, $amount, $description, $referenceType, $referenceId]);

            $db->commit();
            return ['success' => true, 'balance' => $balance - $amount];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
