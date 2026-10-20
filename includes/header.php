<?php
$siteName = setting('site_name', $config['site_name'] ?? 'RuangWeb');
$pageTitle = $pageTitle ?? ($siteName . ' — Jasa Pembuatan Website');
$siteLogo = setting('logo');
$siteFavicon = setting('favicon');
$seoEnabled = setting('seo_enabled', '1') !== '0';
$seoTitle = trim((string)setting('seo_title', ''));
if (!$seoEnabled) { $seoTitle = ''; $seoKeywords = ''; $siteUrl = ''; $ogImageUrl = ''; $googleVerification = ''; $bingVerification = ''; $metaAuthor = ''; }
$seoDescription = trim((string)setting('seo_description', ''));
$seoKeywords = trim((string)setting('seo_keywords', ''));
$siteUrl = rtrim(trim((string)setting('site_url', '')), '/');
$ogImageUrl = trim((string)setting('og_image_url', ''));
$robotsIndex = setting('robots_index', '1') !== '0';
$googleVerification = trim((string)setting('google_site_verification', ''));
$bingVerification = trim((string)setting('bing_site_verification', ''));
$metaAuthor = trim((string)setting('meta_author', ''));
$effectiveTitle = $seoTitle !== '' && !isset($_GET['project']) ? $seoTitle : $pageTitle;
$effectiveDescription = $seoDescription !== '' ? $seoDescription : 'NEXORA membantu UMKM, bisnis lokal, dan perusahaan membuat website profesional sesuai kebutuhan. Konsultasi gratis melalui WhatsApp.';
if (isset($detailProject) && is_array($detailProject)) {
    $effectiveTitle = trim((string)($detailProject['title'] ?? 'Portofolio')) . ' — ' . $siteName;
    $projectDescription = trim(strip_tags((string)($detailProject['description'] ?? '')));
    if ($projectDescription !== '') $effectiveDescription = mb_substr($projectDescription, 0, 300);
}
$canonicalUrl = '';
if ($siteUrl !== '') {
    $canonicalUrl = $siteUrl . (isset($_GET['project']) && ctype_digit((string)$_GET['project']) ? '/index.php?project=' . rawurlencode((string)$_GET['project']) : '/');
}
$effectiveOgImage = $ogImageUrl;
if ($effectiveOgImage === '' && isset($detailProject['image_path']) && $siteUrl !== '') $effectiveOgImage = $siteUrl . '/' . ltrim((string)$detailProject['image_path'], '/');
if ($effectiveOgImage === '' && $siteUrl !== '' && $siteLogo) $effectiveOgImage = $siteUrl . '/' . ltrim($siteLogo, '/');
$waNumber = preg_replace('/\D+/', '', setting('whatsapp_number', $config['whatsapp_number'] ?? ''));
$waText = rawurlencode('Halo, saya ingin konsultasi jasa pembuatan website.');
$waUrl = 'https://wa.me/' . $waNumber . '?text=' . $waText;
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0b1020">
<?php if ($seoEnabled): ?><meta name="description" content="<?= e($effectiveDescription) ?>"><?php endif; ?>
<?php if ($seoEnabled && $seoKeywords !== ''): ?><meta name="keywords" content="<?= e($seoKeywords) ?>"><?php endif; ?>
<?php if ($seoEnabled && $metaAuthor !== ''): ?><meta name="author" content="<?= e($metaAuthor) ?>"><?php endif; ?>
<?php if ($seoEnabled): ?><meta name="robots" content="<?= $robotsIndex ? 'index, follow, max-image-preview:large' : 'noindex, nofollow' ?>"><?php endif; ?>
<?php if ($canonicalUrl !== ''): ?><link rel="canonical" href="<?= e($canonicalUrl) ?>"><?php endif; ?>
<?php if ($seoEnabled): ?><meta property="og:type" content="<?= isset($detailProject) && is_array($detailProject) ? 'article' : 'website' ?>">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($effectiveTitle) ?>">
<meta property="og:description" content="<?= e($effectiveDescription) ?>">
<?php if ($canonicalUrl !== ''): ?><meta property="og:url" content="<?= e($canonicalUrl) ?>"><?php endif; ?>
<?php if ($effectiveOgImage !== ''): ?><meta property="og:image" content="<?= e($effectiveOgImage) ?>"><?php endif; ?>
<meta name="twitter:card" content="<?= $effectiveOgImage !== '' ? 'summary_large_image' : 'summary' ?>">
<meta name="twitter:title" content="<?= e($effectiveTitle) ?>">
<meta name="twitter:description" content="<?= e($effectiveDescription) ?>">
<?php if ($effectiveOgImage !== ''): ?><meta name="twitter:image" content="<?= e($effectiveOgImage) ?>"><?php endif; ?>
<?php if ($googleVerification !== ''): ?><meta name="google-site-verification" content="<?= e($googleVerification) ?>"><?php endif; ?>
<?php if ($bingVerification !== ''): ?><meta name="msvalidate.01" content="<?= e($bingVerification) ?>"><?php endif; ?><?php endif; ?>
<title><?= e($effectiveTitle) ?></title>
<?php if ($siteFavicon): ?><link rel="icon" href="<?= e($siteFavicon) ?>"><?php endif; ?>
<link rel="stylesheet" href="assets/style.css?v=9">
</head>
<body>
<header class="site-header wrap">
  <a class="logo" href="index.php"><?php if ($siteLogo): ?><img class="site-logo-image" src="<?= e($siteLogo) ?>" alt="<?= e($siteName) ?> logo"><?php else: ?><span class="logo-mark"><?= e(mb_strtoupper(mb_substr($siteName, 0, 1))) ?></span><?php endif; ?><span><?= e($siteName) ?></span></a>
  <nav class="nav-links" aria-label="Navigasi utama">
    <a href="index.php#beranda" aria-current="page">Home</a><a href="index.php#layanan">Layanan</a><a href="index.php#portofolio">Portofolio</a><a href="index.php#proses">Cara Kerja</a><a href="index.php#faq">FAQ</a>
  </nav>
  <a class="btn btn-primary" href="index.php#konsultasi">Konsultasi Gratis ↓</a>
</header>
