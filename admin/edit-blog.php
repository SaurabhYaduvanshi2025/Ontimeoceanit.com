<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/blog_storage.php';
require_once __DIR__ . '/../config/database.php';

require_admin_login();

$errors = [];
$message = '';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit('Invalid blog ID.');
}

/* Fetch existing blog */
$stmt = $pdo->prepare(
    'SELECT id, title, slug, meta_title, meta_description, content, image, status
     FROM blogs
     WHERE id = ?
     LIMIT 1'
);
$stmt->execute([$id]);

$blog = $stmt->fetch();

if (!$blog) {
    http_response_code(404);
    exit('Blog not found.');
}

/* Update blog */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submittedToken = (string) ($_POST['csrf_token'] ?? '');

    if (!verify_admin_csrf_token($submittedToken)) {

        $errors[] = 'Invalid security token.';

    } else {

        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $metaTitle = trim((string) ($_POST['meta_title'] ?? ''));
        $metaDescription = trim((string) ($_POST['meta_description'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $status = ($_POST['status'] ?? 'published') === 'draft'
            ? 'draft'
            : 'published';

        if ($title === '') {
            $errors[] = 'Title is required.';
        }

        if ($content === '') {
            $errors[] = 'Content is required.';
        }

        if (empty($errors)) {

            $slug = $slug ?: $title;
            $metaTitle = $metaTitle ?: $title;

            /* Keep old image by default */
            $imagePath = $blog['image'];

            /* Upload new image if selected */
            if (
                isset($_FILES['image']) &&
                $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                $newImagePath = handle_blog_image_upload($_FILES['image']);

                if ($newImagePath !== null && $newImagePath !== '') {
                    $imagePath = $newImagePath;
                }
            }

            try {

                $stmt = $pdo->prepare(
                    'UPDATE blogs
                     SET title = ?,
                         slug = ?,
                         meta_title = ?,
                         meta_description = ?,
                         content = ?,
                         image = ?,
                         status = ?
                     WHERE id = ?'
                );

                $stmt->execute([
                    $title,
                    $slug,
                    $metaTitle,
                    $metaDescription,
                    $content,
                    $imagePath,
                    $status,
                    $id
                ]);

                $message = 'Blog updated successfully.';

                /* Refresh displayed data */
                $stmt = $pdo->prepare(
                    'SELECT id, title, slug, meta_title, meta_description,
                            content, image, status
                     FROM blogs
                     WHERE id = ?
                     LIMIT 1'
                );

                $stmt->execute([$id]);
                $blog = $stmt->fetch();

            } catch (PDOException $e) {

                error_log('Blog update failed: ' . $e->getMessage());

                $errors[] = 'Unable to update blog.';
            }
        }
    }
}

$csrfToken = generate_admin_csrf_token();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Blog</title>

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

        .wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 24px rgba(0,0,0,.06);
        }

        h1 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 16px;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }

        textarea {
            min-height: 220px;
            resize: vertical;
        }

        button {
            margin-top: 20px;
            background: #2563eb;
            color: #fff;
            border: 0;
            border-radius: 8px;
            padding: 11px 18px;
            cursor: pointer;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #2563eb;
        }

        .message {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .current-image {
            max-width: 220px;
            margin-top: 10px;
            border-radius: 8px;
            display: block;
        }
    </style>
</head>

<body>

<div class="wrapper">

    <a class="back" href="blogs.php">
        ← Back to Blog Manager
    </a>

    <div class="card">

        <h1>Edit Blog</h1>

        <?php if ($message !== ''): ?>
            <div class="message success">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="message error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="title">Title</label>

            <input
                type="text"
                id="title"
                name="title"
                value="<?= htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="slug">Slug</label>

            <input
                type="text"
                id="slug"
                name="slug"
                value="<?= htmlspecialchars($blog['slug'], ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="meta_title">Meta Title</label>

            <input
                type="text"
                id="meta_title"
                name="meta_title"
                value="<?= htmlspecialchars($blog['meta_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="meta_description">Meta Description</label>

            <textarea
                id="meta_description"
                name="meta_description"
            ><?= htmlspecialchars($blog['meta_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

            <label for="status">Status</label>

            <select id="status" name="status">
                <option
                    value="published"
                    <?= $blog['status'] === 'published' ? 'selected' : '' ?>
                >
                    Published
                </option>

                <option
                    value="draft"
                    <?= $blog['status'] === 'draft' ? 'selected' : '' ?>
                >
                    Draft
                </option>
            </select>

            <label>Current Image</label>

            <?php if (!empty($blog['image'])): ?>

                <img
                    class="current-image"
                    src="<?= htmlspecialchars($blog['image'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') ?>"
                >

            <?php else: ?>

                <p>No image uploaded.</p>

            <?php endif; ?>

            <label for="image">Replace Image (optional)</label>

            <input
                type="file"
                id="image"
                name="image"
                accept="image/*"
            >

            <label for="content">Content</label>

            <textarea
                id="content"
                name="content"
                required
            ><?= htmlspecialchars($blog['content'], ENT_QUOTES, 'UTF-8') ?></textarea>

            <button type="submit">
                Update Blog
            </button>

        </form>

    </div>

</div>

</body>
</html>