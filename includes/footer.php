<footer class="footer"><div class="wrap footer-grid">
  <div class="footer-brand"><a class="logo" href="index.php"><?php if (setting('logo')): ?><img class="site-logo-image" src="<?= e(setting('logo')) ?>" alt="<?= e($siteName) ?> logo"><?php else: ?><span class="logo-mark"><?= e(mb_strtoupper(mb_substr($siteName, 0, 1))) ?></span><?php endif; ?><span><?= e($siteName) ?></span></a><p>Solusi website profesional untuk membantu bisnis berkembang.</p></div>
  <div class="footer-social-area"><h3>Social Media Kami</h3><div class="footer-social-links">
  <?php $socials = [
    ['Instagram','social_instagram','<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>'],
    ['LinkedIn','social_linkedin','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9v10M5 5v.1M10 19v-6a3 3 0 0 1 6 0v6M10 10v9M3 3h18v18H3z"/></svg>'],
    ['X','social_x','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 3l16 18M20 3L4 21M4 3h4l12 18h-4z"/></svg>'],
    ['Facebook','social_facebook','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 21v-8h3l.5-4H14V7c0-1.2.4-2 2-2h2V1.5C17 1.3 16 1 14.5 1 11.5 1 10 2.8 10 6v3H7v4h3v8z"/></svg>'],
    ['TikTok','social_tiktok','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3v11.2a4.2 4.2 0 1 1-4-4.2M14 3c.7 3.2 2.6 5 6 5"/></svg>']
  ]; foreach ($socials as [$label,$key,$svg]): $socialUrl = trim((string)setting($key,'')); if (setting($key . '_enabled','1') !== '0' && $socialUrl !== ''): ?><a href="<?= e($socialUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($label) ?>" title="<?= e($label) ?>"><?= $svg ?><span><?= e($label) ?></span></a><?php endif; endforeach; $email = trim((string)setting('social_gmail','')); if (setting('social_gmail_enabled','1') !== '0' && $email !== ''): ?><a href="mailto:<?= e($email) ?>" aria-label="Gmail" title="Gmail"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m3 6 9 7 9-7"/></svg><span>Gmail</span></a><?php endif; ?>
  </div></div>
  <div class="footer-bottom"><span>© <?= date('Y') ?> <?= e($siteName) ?>. Semua hak dilindungi.</span></div>
</div></footer>
<a class="wa-float" href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp">☏</a>
<div id="siteToast" class="site-toast" role="status" aria-live="polite"></div>
<script src="assets/site.js?v=7" defer></script>
</body></html>
