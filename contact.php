<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/admin/includes/lead_storage.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load .env file
// Try using vlucas/phpdotenv if it's available, otherwise parse .env manually
if (class_exists(\Dotenv\Dotenv::class)) {
   $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
   $dotenv->load();
} else {
   $envPath = __DIR__ . '/.env';
   if (file_exists($envPath)) {
      $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      foreach ($lines as $line) {
         $line = trim($line);
         if ($line === '' || strpos($line, '#') === 0) {
            continue;
         }
         if (strpos($line, '=') === false) {
            continue;
         }
         list($name, $value) = explode('=', $line, 2);
         $name = trim($name);
         $value = trim($value);
         if (strlen($value) >= 2 &&
            (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
         }
         $_ENV[$name] = $value;
         putenv("$name=$value");
      }
   }
}

function get_mail_config_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        $value = $_ENV[$key] ?? $default;
    }

    return trim((string) $value);
}

function is_placeholder_mail_value(string $value): bool
{
    $value = strtolower(trim($value));
    return $value === '' || strpos($value, 'your') !== false || strpos($value, 'example') !== false || strpos($value, 'changeme') !== false || strpos($value, 'password') !== false;
}

// Flash message variables to show result inside the form
$contact_message = '';
$contact_message_type = ''; // 'success', 'danger', 'warning', etc.

if (isset($_POST['submit'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    save_lead([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject,
        'message' => $message,
        'status' => 'new'
    ]);

    $mailHost = get_mail_config_value('MAIL_HOST');
    $mailUsername = get_mail_config_value('MAIL_USERNAME');
    $mailPassword = get_mail_config_value('MAIL_PASSWORD');
    $mailPort = (int) get_mail_config_value('MAIL_PORT', '587');
    $mailFrom = get_mail_config_value('MAIL_FROM', $mailUsername);
    $mailFromName = get_mail_config_value('MAIL_FROM_NAME', 'Website Contact');

   $mailEnabled = true;
   if ($mailHost === '' || $mailUsername === '' || $mailPassword === '' || is_placeholder_mail_value($mailHost) || is_placeholder_mail_value($mailUsername) || is_placeholder_mail_value($mailPassword) || $mailPort <= 0) {
      $contact_message = 'Thank you! Your request was saved, but email delivery is not enabled because the SMTP credentials are not configured correctly. Please update the mail settings in the .env file.';
      $contact_message_type = 'warning';
      $mailEnabled = false;
   }

    $mail = new PHPMailer(true);

   try {
        $mail->isSMTP();
        $mail->Host = $mailHost;
        $mail->SMTPAuth = true;
        $mail->Username = $mailUsername;
        $mail->Password = $mailPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $mailPort;



        $mail->setFrom($mailFrom, $mailFromName);
        $mail->addAddress($mailUsername);
        $mail->addReplyTo($email, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "
<h2>New Contact Form Submission</h2>

<p><strong>Name:</strong> {$name}</p>

<p><strong>Email:</strong> {$email}</p>

<p><strong>Phone:</strong> {$phone}</p>

<p><strong>Subject:</strong> {$subject}</p>

<p><strong>Message:</strong><br>{$message}</p>
";
        $mail->AltBody = "
Name: $name

Email: $email

Phone: $phone

Subject: $subject

Message:
$message
";

      if ($mailEnabled) {
         $mail->send();
         $contact_message = 'Email sent successfully!';
         $contact_message_type = 'success';
      }
    } catch (Exception $e) {
        error_log('Contact form mail failed: ' . $e->getMessage());
      if ($contact_message === '') {
         $contact_message = 'Your request was saved, but the email could not be sent. Please verify your SMTP credentials, especially your Gmail app password.';
         $contact_message_type = 'danger';
      }
    }

}

?>



<!doctype html>
<html class="no-js" lang="zxx">


<head>
   <meta charset="utf-8">
   <meta http-equiv="x-ua-compatible" content="ie=edge">
   <title>Ontimeoceanit - IT Service HTML Template</title>
   <meta name="description" content="">
   <meta name="viewport" content="width=device-width, initial-scale=1">

   <!-- Place favicon.ico in the root directory -->

   <link rel="shortcut icon" type="image/x-icon" href="assets/imgs/logo/ontimelogo.png">

   <!-- CSS here -->
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

   <!-- preloader start -->
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
   <!-- preloader start -->

   <!-- Back to top start -->
   <div class="backtotop-wrap cursor-pointer">
      <svg class="backtotop-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
         <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
      </svg>
   </div>
   <!-- Back to top end -->

   <!-- search area start -->
   <div class="df-search-area">
      <div class="container">
         <div class="row">
            <div class="col-xl-12">
               <div class="df-search-form">
                  <div class="df-search-close text-center mb-20">
                     <button class="df-search-close-btn df-search-close-btn"></button>
                  </div>
                  <form action="#">
                     <div class="df-search-input mb-10">
                        <input type="text" placeholder="Search here...">
                        <button type="submit"><i class="icon-search"></i></button>
                     </div>
                     <div class="df-search-category">
                        <span>Search by : </span>
                        <a href="#">Modified Wotech, </a>
                        <a href="#">Wotech Installation, </a>
                        <a href="#">Wotech Cornering, </a>
                        <a href="#">Wotech Renovation </a>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="body-overlay"></div>
   <!-- search area end -->

   <!-- Offcanvas area start -->
   <div class="fix">
      <div class="offcanvas__info">
         <div class="offcanvas__wrapper">
            <div class="offcanvas__content">
               <div class="offcanvas__top mb-40 d-flex justify-content-between align-items-center">
                  <div class="offcanvas__logo">
                     <a href="dashboard.html">
                        <img src="assets/imgs/logo/logo-white.svg" alt="Header Logo">
                     </a>
                  </div>
                  <div class="offcanvas__close">
                     <button>
                        <i class="fal fa-times"></i>
                     </button>
                  </div>
               </div>
               <div class="mobile-menu fix mb-40"></div>
            </div>
         </div>
      </div>
   </div>
   <div class="offcanvas__overlay"></div>
   <div class="offcanvas__overlay-white"></div>
   <!-- Offcanvas area start -->

   <!-- Header area start -->
   <?php include 'includes/header.php'; ?>
   <!-- Header area end --> 


 <main>
   
 <!-- Breadcrumb area start --> 
<div class="breadcrumb__area theme-bg-1 p-relative pt-160 pb-160">
   <div class="breadcrumb__thumb" data-background="assets/imgs/resources/page-title-bg-1.png"></div>
   <div class="breadcrumb__thumb_2" data-background="assets/imgs/resources/page-title-bg-2.png"></div>
   <div class="small-container">
      <div class="row justify-content-center">
         <div class="col-xxl-12">
            <div class="breadcrumb__wrapper p-relative">
               <h2 class="breadcrumb__title">Contact</h2>
               <div class="breadcrumb__menu">
                  <nav>
                     <ul>
                        <li><span><a href="index.html">Home</a></span></li>
                        <li><span>Contact</span></li>
                     </ul>
                  </nav>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
 <!-- Breadcrumb area end --> 

 <section class="contact-page-section section-space">
   <div class="small-container">
      <div class="row">
         <div class="col-xxl-4 col-xl-4 col-lg-4">
            <div class="contact-p-info-area">
               <div class="contact-box mb-30">
                  <div class="icon-1">
                     <i class="fat fa-location-dot"></i>
                  </div>
                  <div class="info">
                     <span>Location</span>
                     <h4>Laxmi Park Nangloi New Delhi</h4>
                  </div>
               </div>
               <div class="contact-box mb-30">
                  <div class="icon-1">
                     <i class="fat fa-phone-volume"></i>
                  </div>
                  <div class="info">
                     <span>Call Us 7/24</span>
                     <h4><a href="tel:919625703233">+91 96257 03233</a></h4>
                  </div>
               </div>
               <div class="contact-box">
                  <div class="icon-1">
                     <i class="fat fa-envelope"></i>
                  </div>
                  <div class="info">
                     <span>Make A quote</span>
                     <h4><a href="mailto:Info@wotech.com">Info@wotech.com</a></h4>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-xxl-8 col-xl-8 col-lg-8">
            <div class="contact-page-form-area">
               <div class="title-box mb-40 wow fadeInLeft" data-wow-delay=".5s">
                  <span class="section-sub-title">LET’S TALK</span>
                  <h3 class="section-title mt-10">Let’s Get in Touch</h3>
               </div>
               <div class="contact-page-form">
                     <div class="contact-page-form">
    <form action="" method="POST">

        <div class="row">

            <div class="col-lg-6">
                <label for="name">Your Name <span>*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    placeholder="Enter Your Name"
                    required>
            </div>

            <div class="col-lg-6">
                <label for="email">Your Email <span>*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter Your Email"
                    required>
            </div>

            <div class="col-lg-6">
                <label for="phone">Phone Number <span>*</span></label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    class="form-control"
                    placeholder="Enter Your Phone Number"
                    required>
            </div>

            <div class="col-lg-6">
                <label for="subject">Subject <span>*</span></label>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    class="form-control"
                    placeholder="Enter Subject"
                    required>
            </div>

            <div class="col-lg-12">
                <label for="message">Your Message <span>*</span></label>
                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    class="form-control"
                    placeholder="Write your message here..."
                    required></textarea>
            </div>

            <?php if (!empty($contact_message)): ?>
            <div class="col-lg-12 mt-2">
               <div class="alert alert-<?php echo htmlspecialchars($contact_message_type ?: 'info'); ?> alert-dismissible fade show" role="alert" style="border-radius:12px;">
                  <?php echo htmlspecialchars($contact_message); ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
               </div>
            </div>
            <?php endif; ?>

            <div class="col-lg-12 mt-3">
               <button type="submit" name="submit" class="primary-btn-1 btn-hover">
                  Send Message &nbsp; | <i class="icon-right-arrow"></i>
               </button>
            </div>

        </div>

    </form>
</div>
            </div>
            </div>
         </div>
      </div>
   </div>
 </section>

 <div class="container-fluid g-0 fix">
   <div class="row">
      <div class="col-xxl-12">
         <div class="contact-map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3500.7243552710543!2d77.0551583!3d28.667970999999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390d05141df45205%3A0x13f51412eafc44ab!2sHanuman%20Mandir%2C%20Shiv%20Ram%20Park!5e0!3m2!1sen!2sin!4v1784172712976!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
         </div>
      </div>
   </div>
</div>


 </main>     

   <!-- Footer area start -->
   <?php include 'includes/footer.php'; ?>
   <!-- Footer area end -->
   

</body>


</html>
