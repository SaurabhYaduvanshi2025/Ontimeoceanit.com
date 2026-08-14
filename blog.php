<?php
require_once __DIR__ . '/admin/includes/blog_storage.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$blog = $slug !== '' ? get_blog_by_slug($slug) : null;

if (!$blog) {
    header('HTTP/1.1 404 Not Found');
    echo 'Blog post not found.';
    exit;
}

$title = $blog['meta_title'] ?? $blog['title'] ?? 'Blog';
$description = $blog['meta_description'] ?? 'Read our latest blog post.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 16px; line-height: 1.7; }
        .card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; }
        img { max-width: 100%; border-radius: 10px; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?= htmlspecialchars($blog['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if (!empty($blog['image'])): ?>
            <img src="<?= htmlspecialchars($blog['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($blog['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <p><?= nl2br(htmlspecialchars($blog['content'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
    </div>
</body>
</html>
