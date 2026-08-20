<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/blog_storage.php';
require_once __DIR__ . '/../config/database.php';
require_admin_login();
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = (string) ($_POST['csrf_token'] ?? '');
    if (!verify_admin_csrf_token($submittedToken)) {
        $errors[] = 'Invalid security token.';
    } else {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $meta_title = trim((string) ($_POST['meta_title'] ?? ''));
        $meta_description = trim((string) ($_POST['meta_description'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $imagePath = handle_blog_image_upload($_FILES['image'] ?? []);

        if ($title === '' || $content === '') {
            $errors[] = 'Title and content are required.';
        }

        if (empty($errors)) {
            $blogSlug = $slug ?: $title;
            $blogMetaTitle = $meta_title ?: $title;

            $stmt = $pdo->prepare(
                'INSERT INTO blogs
    (title, slug, meta_title, meta_description, content, image, status)
    VALUES
    (?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $title,
                $blogSlug,
                $blogMetaTitle,
                $meta_description,
                $content,
                $imagePath,
                'published'
            ]);

            $message = 'Blog post created successfully.';
        }
    }
}

$stmt = $pdo->query(
    'SELECT id, title, slug, meta_title, meta_description, content, image, status, created_at, updated_at
     FROM blogs
     ORDER BY created_at DESC'
);

$blogs = $stmt->fetchAll();

$csrfToken = generate_admin_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Blog Manager</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            margin: 0;
            color: #111827;
        }

        .app {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #111827;
            color: #fff;
            padding: 24px 16px;
        }

        .brand {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .sidebar a {
            color: #e5e7eb;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 8px;
            display: block;
            margin-bottom: 6px;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background: #1f2937;
            color: #fff;
        }

        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: #fff;
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .content {
            padding: 24px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .06);
            padding: 24px;
            margin-bottom: 20px;
        }

        form {
            display: grid;
            gap: 14px;
        }

        label {
            font-weight: 600;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        button {
            background: #2563eb;
            color: #fff;
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            cursor: pointer;
        }

        .message {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .message.success {
            background: #dcfce7;
            color: #166534;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .blog-list {
            display: grid;
            gap: 14px;
        }

        .blog-item {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
        }

        .blog-item h3 {
            margin: 0 0 6px;
        }

        .blog-meta {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .blog-img {
            max-width: 220px;
            border-radius: 8px;
            margin-top: 8px;
        }

        @media (max-width: 768px) {
            .app {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .content {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">Admin Panel</div>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="leads.php">Leads</a>
                <a class="active" href="blogs.php">Blog</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <div class="main">
            <header class="topbar"><strong>Blog Manager</strong></header>
            <div class="content">
                <div class="card">
                    <h2>Create New Blog Post</h2>
                    <?php if ($message !== ''): ?>
                        <div class="message success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="message error">
                            <ul style="margin: 0; padding-left: 18px;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token"
                            value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <label>Title</label>
                        <input type="text" name="title" required>

                        <label>Slug (optional)</label>
                        <input type="text" name="slug" placeholder="my-seo-friendly-blog-post">

                        <label>Meta Title (optional)</label>
                        <input type="text" name="meta_title" placeholder="SEO title">

                        <label>Meta Description (optional)</label>
                        <textarea name="meta_description" placeholder="Short SEO description"></textarea>

                        <label>Image</label>
                        <input type="file" name="image" accept="image/*">

                        <label>Content</label>
                        <textarea name="content" required placeholder="Write your blog post content here..."></textarea>

                        <button type="submit">Publish Blog</button>
                    </form>
                </div>

                <div class="card">
                    <h2>Existing Blog Posts</h2>
                    <?php if (empty($blogs)): ?>
                        <div>No posts yet.</div>
                    <?php else: ?>
                        <div class="blog-list">
                            <?php foreach ($blogs as $blog): ?>
                                <div class="blog-item">
                                    <h3><?= htmlspecialchars($blog['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></h3>
                                    <div class="blog-meta">Slug:
                                        <?= htmlspecialchars($blog['slug'] ?? '-', ENT_QUOTES, 'UTF-8') ?> · Created:
                                        <?= htmlspecialchars($blog['created_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <?php if (!empty($blog['image'])): ?>
                                        <img class="blog-img" src="<?= htmlspecialchars($blog['image'], ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($blog['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php endif; ?>
                                    <p><?= htmlspecialchars(substr(strip_tags($blog['content'] ?? ''), 0, 180), ENT_QUOTES, 'UTF-8') ?>...
                                    </p>

                                    <div style="margin-top: 12px;">
                                        <a href="edit-blog.php?id=<?= (int) $blog['id'] ?>" style="
                                            display: inline-block;
                                              background: #2563eb;
                                              color: #fff;
                                               padding: 8px 14px;
                                               border-radius: 6px;
                                                text-decoration: none;
                                               ">
                                            Edit
                                        </a>
                                    </div>



                                    <form method="post" action="delete-blog.php" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= (int) $blog['id'] ?>">

                                        <input type="hidden" name="csrf_token"
                                            value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                                        <button type="submit"
                                            onclick="return confirm('Are you sure you want to delete this blog?');" style="
                                            background: #dc2626;
                                            color: #fff;
                                            border: 0;
                                            padding: 8px 14px;
                                            border-radius: 6px;
                                            cursor: pointer;
                                            ">
                                            Delete
                                        </button>
                                    </form>


                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>