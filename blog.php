<?php
require_once __DIR__ . '/admin/includes/blog_storage.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$blogs = load_blogs();

if ($slug !== '') {
    $blog = get_blog_by_slug($slug);
    if (!$blog) {
        header('HTTP/1.1 404 Not Found');
        echo 'Blog post not found.';
        exit;
    }

    $page_title = $blog['meta_title'] ?? $blog['title'] ?? 'Blog';
    $page_description = $blog['meta_description'] ?? 'Read our latest blog post.';
} else {
    $page_title = 'Blog';
    $page_description = 'Read our latest blog posts and insights.';
}
?>
<!doctype html>
<html class="no-js" lang="zxx">
<head>
   <meta charset="utf-8">
   <meta http-equiv="x-ua-compatible" content="ie=edge">
   <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>
   <meta name="description" content="<?= htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8') ?>">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="shortcut icon" type="image/x-icon" href="assets/imgs/logo/ontimelogo.png">
   <link rel="stylesheet" href="assets/css/bootstrap.min.css">
   <link rel="stylesheet" href="assets/css/meanmenu.min.css">
   <link rel="stylesheet" href="assets/css/animate.css">
   <link rel="stylesheet" href="assets/css/swiper.min.css">
   <link rel="stylesheet" href="assets/css/slick.css">
   <link rel="stylesheet" href="assets/css/magnific-popup.css">
   <link rel="stylesheet" href="assets/css/fontawesome-pro.css">
   <link rel="stylesheet" href="assets/css/icomoon.css">
   <link rel="stylesheet" href="assets/css/spacing.css">
   <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
   <div id="preloader">
      <div class="bd-loader-inner">
         <div class="bd-loader">
            <span class="bd-loader-item"></span>
            <span class="bd-loader-item"></span>
            <span class="bd-loader-item"></span>
            <span class="bd-loader-item"></span>
            <span class="bd-loader-item"></span>
            <span class="bd-loader-item"></span>
            <span class="bd-loader-item"></span>
            <span class="bd-loader-item"></span>
         </div>
      </div>
   </div>

   <div class="backtotop-wrap cursor-pointer">
      <svg class="backtotop-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
         <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
      </svg>
   </div>

   <div class="body-overlay"></div>
   <?php include __DIR__ . '/includes/header.php'; ?>

   <main>
      <?php if ($slug !== ''): ?>
         <div class="breadcrumb__area theme-bg-1 p-relative pt-160 pb-160">
            <div class="breadcrumb__thumb" data-background="assets/imgs/resources/page-title-bg-1.png"></div>
            <div class="breadcrumb__thumb_2" data-background="assets/imgs/resources/page-title-bg-2.png"></div>
            <div class="small-container">
               <div class="row justify-content-center">
                  <div class="col-xxl-12">
                     <div class="breadcrumb__wrapper p-relative">
                        <h2 class="breadcrumb__title"><?= htmlspecialchars($blog['title'] ?? 'Blog', ENT_QUOTES, 'UTF-8') ?></h2>
                        <div class="breadcrumb__menu">
                           <nav>
                              <ul>
                                 <li><span><a href="index.php">Home</a></span></li>
                                 <li><span><a href="blog.php">Blog</a></span></li>
                                 <li><span><?= htmlspecialchars($blog['title'] ?? 'Blog Details', ENT_QUOTES, 'UTF-8') ?></span></li>
                              </ul>
                           </nav>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <section class="blog-details-page section-space">
            <div class="small-container">
               <div class="row g-5">
                  <div class="col-xxl-8 col-xl-8 col-lg-8">
                     <article class="blog-details-post">
                        <?php if (!empty($blog['image'])): ?>
                           <div class="blog-details-thumb mb-30">
                              <img src="<?= htmlspecialchars($blog['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($blog['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                           </div>
                        <?php endif; ?>

                        <div class="blog-details-content">
                           <div class="post-meta mb-20">
                              <span class="p-relative"><i class="fal fa-user"></i> By Admin</span>
                              <span class="p-relative"><i class="fal fa-calendar-alt"></i> <?= htmlspecialchars($blog['created_at'] ?? date('d M, Y'), ENT_QUOTES, 'UTF-8') ?></span>
                           </div>

                           <h3 class="blog-details-title mb-30"><?= htmlspecialchars($blog['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>

                           <div class="blog-content-body">
                              <?= nl2br(htmlspecialchars((string) ($blog['content'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>
                           </div>
                        </div>
                     </article>
                  </div>

                  <div class="col-xxl-4 col-xl-4 col-lg-4">
                     <aside class="blog-sidebar">
                        <div class="sidebar-widget">
                           <h4 class="sidebar-title">Recent Posts</h4>
                           <div class="sidebar-post-list">
                              <?php foreach (array_slice($blogs, 0, 4) as $recentBlog): ?>
                                 <div class="sidebar-post-item d-flex align-items-center mb-20">
                                    <?php if (!empty($recentBlog['image'])): ?>
                                       <a class="w-img blog-sidebar-thumb" href="blog.php?slug=<?= urlencode((string) ($recentBlog['slug'] ?? '')) ?>">
                                          <img src="<?= htmlspecialchars($recentBlog['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($recentBlog['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                       </a>
                                    <?php endif; ?>
                                    <div class="sidebar-post-content">
                                       <h6 class="blog-sidebar-post-title mt-10">
                                          <a href="blog.php?slug=<?= urlencode((string) ($recentBlog['slug'] ?? '')) ?>"><?= htmlspecialchars($recentBlog['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></a>
                                       </h6>
                                    </div>
                                 </div>
                              <?php endforeach; ?>
                           </div>
                        </div>
                     </aside>
                  </div>
               </div>
            </div>
         </section>
      <?php else: ?>
         <div class="breadcrumb__area theme-bg-1 p-relative pt-160 pb-160">
            <div class="breadcrumb__thumb" data-background="assets/imgs/resources/page-title-bg-1.png"></div>
            <div class="breadcrumb__thumb_2" data-background="assets/imgs/resources/page-title-bg-2.png"></div>
            <div class="small-container">
               <div class="row justify-content-center">
                  <div class="col-xxl-12">
                     <div class="breadcrumb__wrapper p-relative">
                        <h2 class="breadcrumb__title">Blog</h2>
                        <div class="breadcrumb__menu">
                           <nav>
                              <ul>
                                 <li><span><a href="index.php">Home</a></span></li>
                                 <li><span>Blog</span></li>
                              </ul>
                           </nav>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <section class="blog-section-one section-space">
            <div class="small-container">
               <?php if (empty($blogs)): ?>
                  <div class="row">
                     <div class="col-12">
                        <p class="mb-0">No blog posts yet. Please add a post from the admin area.</p>
                     </div>
                  </div>
               <?php else: ?>
                  <div class="row g-4">
                     <?php foreach ($blogs as $blogItem): ?>
                        <div class="col-xxl-4 col-xl-4 col-lg-6">
                           <div class="blog-style-one">
                              <?php if (!empty($blogItem['image'])): ?>
                                 <a class="blog-image w-img" href="blog.php?slug=<?= urlencode((string) ($blogItem['slug'] ?? '')) ?>">
                                    <img src="<?= htmlspecialchars($blogItem['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($blogItem['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                 </a>
                              <?php endif; ?>
                              <div class="blog-content">
                                 <div class="post-meta">
                                    <span class="p-relative"><a href="blog.php?slug=<?= urlencode((string) ($blogItem['slug'] ?? '')) ?>"><i class="fal fa-user"></i> By Admin</a></span>
                                    <span class="p-relative"><a href="blog.php?slug=<?= urlencode((string) ($blogItem['slug'] ?? '')) ?>"><i class="fal fa-calendar-alt"></i> <?= htmlspecialchars($blogItem['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></a></span>
                                 </div>
                                 <hr>
                                 <h5 class="blog-title mb-30"><a href="blog.php?slug=<?= urlencode((string) ($blogItem['slug'] ?? '')) ?>"><?= htmlspecialchars($blogItem['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></a></h5>
                                 <div class="blog-link">
                                    <a class="primary-btn-5 btn-hover" href="blog.php?slug=<?= urlencode((string) ($blogItem['slug'] ?? '')) ?>">
                                       Read MORE &nbsp; | <i class="icon-right-arrow"></i>
                                       <span style="top: 147.172px; left: 108.5px;"></span>
                                    </a>
                                 </div>
                              </div>
                           </div>
                        </div>
                     <?php endforeach; ?>
                  </div>
               <?php endif; ?>
            </div>
         </section>
      <?php endif; ?>
   </main>

   <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
