<?php
function get_blog_storage_dir(): string
{
    return dirname(__DIR__) . '/data/blogs';
}

function get_blog_upload_dir(): string
{
    return dirname(__DIR__) . '/uploads';
}

function ensure_blog_storage(): void
{
    $dir = get_blog_storage_dir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Unable to create blog storage directory.');
    }
}

function load_blogs(): array
{
    ensure_blog_storage();

    $dir = get_blog_storage_dir();
    $files = glob($dir . '/*.json');
    $blogs = [];

    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            $blogs[] = $data;
        }
    }

    usort($blogs, function ($a, $b) {
        return strtotime($b['created_at'] ?? 'now') <=> strtotime($a['created_at'] ?? 'now');
    });

    return $blogs;
}

function save_blog(array $blog): void
{
    ensure_blog_storage();
    $slug = slugify($blog['slug'] ?? $blog['title'] ?? 'blog-post');
    $filename = get_blog_storage_dir() . '/' . $slug . '.json';
    $blog['slug'] = $slug;
    $blog['created_at'] = $blog['created_at'] ?? date('Y-m-d H:i:s');
    $blog['updated_at'] = date('Y-m-d H:i:s');

    file_put_contents($filename, json_encode($blog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
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
    $blogs = load_blogs();
    foreach ($blogs as $blog) {
        if (($blog['slug'] ?? '') === $slug) {
            return $blog;
        }
    }
    return null;
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
