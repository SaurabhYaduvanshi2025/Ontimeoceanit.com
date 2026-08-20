<?php
require_once __DIR__ . '/../../config/database.php';

function get_blog_upload_dir(): string
{
    return dirname(__DIR__) . '/uploads';
}

function load_blogs(): array
{
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT * FROM blogs ORDER BY created_at DESC');
        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        error_log('Load blogs query failed: ' . $e->getMessage());
        return [];
    }
}

function save_blog(array $blog): bool
{
    global $pdo;
    
    try {
        $slug = slugify($blog['slug'] ?? $blog['title'] ?? 'blog-post');
        
        $stmt = $pdo->prepare('
            INSERT INTO blogs (title, slug, content, image, author, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        
        return $stmt->execute([
            $blog['title'] ?? '',
            $slug,
            $blog['content'] ?? '',
            $blog['image'] ?? null,
            $blog['author'] ?? 'Admin',
            $blog['created_at'] ?? date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
    } catch (PDOException $e) {
        error_log('Save blog query failed: ' . $e->getMessage());
        return false;
    }
}

function update_blog(int $id, array $blog): bool
{
    global $pdo;
    
    try {
        $slug = slugify($blog['slug'] ?? $blog['title'] ?? 'blog-post');
        
        $stmt = $pdo->prepare('
            UPDATE blogs 
            SET title = ?, slug = ?, content = ?, image = ?, author = ?, updated_at = ?
            WHERE id = ?
        ');
        
        return $stmt->execute([
            $blog['title'] ?? '',
            $slug,
            $blog['content'] ?? '',
            $blog['image'] ?? null,
            $blog['author'] ?? 'Admin',
            date('Y-m-d H:i:s'),
            $id
        ]);
    } catch (PDOException $e) {
        error_log('Update blog query failed: ' . $e->getMessage());
        return false;
    }
}

function delete_blog(int $id): bool
{
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('DELETE FROM blogs WHERE id = ?');
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Delete blog query failed: ' . $e->getMessage());
        return false;
    }
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'blog-post';
}

function get_blog_by_slug(string $slug): ?array
{
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('SELECT * FROM blogs WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    } catch (PDOException $e) {
        error_log('Get blog query failed: ' . $e->getMessage());
        return null;
    }
}

function get_blog(int $id): ?array
{
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    } catch (PDOException $e) {
        error_log('Get blog query failed: ' . $e->getMessage());
        return null;
    }
}

function handle_blog_image_upload(array $file): ?string
{
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'] ?? '', $allowed, true)) {
        return null;
    }

    $dir = get_blog_upload_dir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return null;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'blog-' . uniqid() . '.' . $ext;
    $target = $dir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'admin/uploads/' . $filename;
    }

    return null;
}
