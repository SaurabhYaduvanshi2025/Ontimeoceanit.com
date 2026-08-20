<?php
require_once __DIR__ . '/../../config/database.php';

function load_leads(): array
{
    global $pdo;

    try {
        $stmt = $pdo->query('SELECT * FROM leads ORDER BY created_at DESC');
        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        error_log('Load leads query failed: ' . $e->getMessage());
        return [];
    }
}

function save_lead(array $lead): bool
{
    global $pdo;

    try {
        $stmt = $pdo->prepare('
            INSERT INTO leads (name, email, phone, service, message)
            VALUES (?, ?, ?, ?, ?)
        ');

        return $stmt->execute([
            $lead['name'] ?? '',
            $lead['email'] ?? '',
            $lead['phone'] ?? '',
            $lead['subject'] ?? ($lead['service'] ?? ''),
            $lead['message'] ?? '',
        ]);
    } catch (PDOException $e) {
        error_log('Save lead query failed: ' . $e->getMessage());
        return false;
    }
}

function delete_lead(int $id): bool
{
    global $pdo;

    try {
        $stmt = $pdo->prepare('DELETE FROM leads WHERE id = ?');
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Delete lead query failed: ' . $e->getMessage());
        return false;
    }
}

function get_lead(int $id): ?array
{
    global $pdo;

    try {
        $stmt = $pdo->prepare('SELECT * FROM leads WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    } catch (PDOException $e) {
        error_log('Get lead query failed: ' . $e->getMessage());
        return null;
    }
}