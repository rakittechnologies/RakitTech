<?php
require __DIR__ . '/includes/bootstrap.php';
$siteName = setting('site_name', $config['site_name'] ?? 'RuangWeb');
$pageTitle = $siteName . ' — Jasa Pembuatan Website & Aplikasi Custom';
$stmt = $pdo->query("SELECT * FROM projects WHERE is_published = 1 ORDER BY sort_order ASC, created_at DESC");
$projects = $stmt->fetchAll();
$packageStmt = $pdo->query("SELECT * FROM service_packages WHERE is_active = 1 ORDER BY FIELD(category, 'Website Statis', 'Website Dinamis'), sort_order ASC, id ASC");
$servicePackages = $packageStmt->fetchAll();
$defaultFaqItems = [
    ['question' => 'Berapa biaya pembuatan website?', 'answer' => 'Biaya menyesuaikan jumlah halaman, desain, fitur, kebutuhan admin, integrasi, domain, dan hosting. Setelah konsultasi, kamu akan mendapat rincian penawaran terlebih dahulu.'],
    ['question' => 'Apakah website bisa dibuka lewat HP?', 'answer' => 'Ya. Desain dibuat responsif agar tata letak menyesuaikan ukuran layar HP, tablet, maupun komputer.'],
    ['question' => 'Apa perbedaan website statis dan dinamis?', 'answer' => 'Website statis cocok untuk informasi yang jarang berubah. Website dinamis menyediakan pengelolaan konten atau fitur interaktif seperti dashboard admin, booking, produk, dan transaksi.'],
    ['question' => 'Apakah saya bisa mengubah isi website sendiri?', 'answer' => 'Bisa jika website dibuat dengan dashboard admin atau sistem pengelolaan konten. Untuk website statis, perubahan konten dapat dibantu sebagai layanan pembaruan.'],
    ['question' => 'Apakah demo portofolio bisa dicoba?', 'answer' => 'Bisa jika tautan demo tersedia. Demo digunakan untuk melihat contoh tampilan dan fitur, bukan untuk mengakses panel admin atau data internal.'],
    ['question' => 'Bagaimana sistem pembayaran proyek?', 'answer' => 'Pembayaran mengikuti penawaran yang disepakati. Untuk proyek pembuatan website, pembayaran dapat menggunakan DP dan pelunasan sesuai tahap kerja yang tertulis dalam kesepakatan.'],
    ['question' => 'Siapa pemilik website setelah selesai?', 'answer' => 'Kepemilikan mengikuti model kerja sama dan perjanjian. Pada model Project-Based, website dan aset yang disepakati diserahkan setelah pembayaran lunas.'],
    ['question' => 'Apakah domain dan hosting sudah termasuk?', 'answer' => 'Jangan menganggap domain atau hosting otomatis termasuk. Keduanya hanya termasuk jika tertulis jelas pada rincian paket atau penawaran. Jika tidak tercantum, biaya domain, hosting/server, email bisnis, layanan pihak ketiga, dan perpanjangan tahun berikutnya dibayar terpisah oleh klien. Nominal dan masa aktif dikonfirmasi sebelum pembayaran.'],
    ['question' => 'Apakah tersedia perawatan setelah website rilis?', 'answer' => 'Perawatan dapat dibahas sebagai layanan berkala. Batas garansi bug, pembaruan, backup, dan pekerjaan tambahan perlu tercantum dalam penawaran.'],
    ['question' => 'Berapa lama pengerjaan website?', 'answer' => 'Estimasi tergantung jenis website, jumlah halaman, fitur, kelengkapan materi, dan kecepatan persetujuan. Estimasi disampaikan setelah kebutuhan dibahas.'],
];
$faqStored = json_decode((string)setting('faq_items', ''), true);
$faqItems = is_array($faqStored) ? array_values(array_filter($faqStored, fn($item) => is_array($item) && trim((string)($item['question'] ?? '')) !== '' && trim((string)($item['answer'] ?? '')) !== '')) : $defaultFaqItems;
$detailProject = null;
if (isset($_GET['project']) && ctype_digit((string)$_GET['project'])) {
    $detailStmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND is_published = 1 LIMIT 1");
    $detailStmt->execute([(int)$_GET['project']]);
    $detailProject = $detailStmt->fetch() ?: null;
}
if ($detailProject) {
    $pageTitle = $detailProject['title'] . ' — Portofolio ' . $siteName;
}
require __DIR__ . '/includes/header.php';
if ($detailProject):
    $detailFeatures = array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', (string)($detailProject['features'] ?? ''))));
    $detailAdvantages = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)($detailProject['advantages'] ?? ''))));
    $detailRequirements = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)($detailProject['system_requirements'] ?? ''))));
    $galleryStmt = $pdo->prepare('SELECT image_path FROM project_images WHERE project_id=? ORDER BY sort_order ASC, id ASC LIMIT 9');
    $galleryStmt->execute([(int)$detailProject['id']]);
    $detailGallery = $galleryStmt->fetchAll();
    if (!$detailGallery && !empty($detailProject['image_path'])) $detailGallery = [['image_path'=>$detailProject['image_path']]];
?>
<main>
<section class="section project-detail-section"><div class="wrap">
  <a class="btn btn-outline" href="index.php#portofolio">← Kembali ke portofolio</a>
  <div class="project-detail-layout">
  <article class="project-detail">
    <?php if ($detailGallery): ?><section class="portfolio-gallery-section belisc-style-gallery" aria-label="Galeri gambar <?= e($detailProject['title']) ?>"><div class="portfolio-gallery-heading"><div><span class="kicker">PROJECT GALLERY</span><h2>Galeri hasil proyek</h2><p>Pilih thumbnail untuk mengganti foto utama, atau klik foto untuk memperbesar.</p></div><span class="gallery-count-pill"><?= count($detailGallery) ?> FOTO</span></div><button class="gallery-main-image" id="galleryMainOpen" type="button" aria-label="Perbesar gambar proyek"><img id="galleryMainImage" src="<?= e($detailGallery[0]['image_path']) ?>" alt="Screenshot <?= e($detailProject['title']) ?> — gambar 1"><span class="gallery-main-zoom" aria-hidden="true">⌕ Perbesar</span><span class="gallery-main-count" id="galleryMainCount">1 / <?= count($detailGallery) ?></span></button><?php if (count($detailGallery)>1): ?><div class="gallery-thumbnails" id="galleryThumbnails" aria-label="Pilih gambar proyek"><?php foreach ($detailGallery as $gi=>$galleryImage): ?><button class="gallery-thumbnail <?= $gi===0?'is-active':'' ?>" type="button" data-gallery-index="<?= $gi ?>" aria-label="Tampilkan gambar <?= $gi+1 ?>" aria-pressed="<?= $gi===0?'true':'false' ?>"><img src="<?= e($galleryImage['image_path']) ?>" alt="Thumbnail <?= $gi+1 ?>" loading="lazy"><span><?= $gi+1 ?></span></button><?php endforeach; ?></div><?php endif; ?></section><?php endif; ?>
    <div class="project-detail-body"><div class="detail-intro"><span class="kicker">Portofolio <?= e($siteName) ?></span><h1><?= e($detailProject['title']) ?></h1><p class="detail-lead">Contoh pengembangan website yang dapat menjadi referensi untuk kebutuhan bisnis atau proyek kamu.</p><div class="project-detail-meta"><span><b>Kategori</b><?= e($detailProject['category'] ?: 'Belum ditentukan') ?></span><span><b>Jenis website</b><?= e($detailProject['website_type']) ?></span><span><b>Metode penjualan</b><?= e((string)($detailProject['sales_method'] ?? 'Project-Based')) ?></span><span><b>Status proyek</b><?= e((string)($detailProject['project_status'] ?? 'Demo / contoh konsep')) ?></span></div></div></div>
      <div class="detail-content-grid"><section class="detail-content-main"><h2>Deskripsi proyek</h2><p class="project-detail-description"><?= nl2br(e($detailProject['description'])) ?></p>
      <?php if ($detailFeatures): ?><h2>Fitur utama</h2><ul class="project-detail-features project-detail-features-grid"><?php foreach ($detailFeatures as $feature): ?><li><?= e($feature) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php if ($detailAdvantages): ?><h2>Keunggulan</h2><ul class="project-detail-features project-detail-features-grid"><?php foreach ($detailAdvantages as $advantage): ?><li><?= e($advantage) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php if (!empty($detailProject['demo_access'])): ?><h2>Akses demo</h2><div class="project-detail-description"><?= nl2br(e($detailProject['demo_access'])) ?></div><?php endif; ?>
      <?php if (!empty($detailProject['notes'])): ?><h2>Catatan</h2><div class="project-detail-description"><?= nl2br(e($detailProject['notes'])) ?></div><?php endif; ?>
      <?php if ($detailRequirements): ?><h2>System Requirements</h2><ul class="project-detail-features"><?php foreach ($detailRequirements as $requirement): ?><li><?= e($requirement) ?></li><?php endforeach; ?></ul><?php endif; ?></section></div>
  </article>
  <aside class="detail-contact-card"><span class="detail-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 14.8 9.2 21 12l-6.2 2.8L12 21l-2.8-6.2L3 12l6.2-2.8L12 3Z"/><path d="m18.5 2 .8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8.8-2.2Z"/></svg></span><span class="detail-card-eyebrow">LANGKAH BERIKUTNYA</span><h3>Tertarik dengan konsep ini?</h3><p>Coba demo untuk melihat gambaran website, atau diskusikan versi yang sesuai kebutuhan kamu langsung melalui WhatsApp.</p><div class="project-detail-actions"><a class="btn btn-primary" href="<?= e($detailProject['demo_url']) ?>" target="_blank" rel="noopener noreferrer">Lihat demo website ↗</a><a class="btn btn-whatsapp" href="<?= e('https://wa.me/' . $waNumber . '?text=' . rawurlencode('Halo ' . $siteName . ', saya tertarik dengan portofolio "' . $detailProject['title'] . '".
Kategori: ' . ($detailProject['category'] ?: 'Belum ditentukan') . '
Jenis website: ' . $detailProject['website_type'] . '
Saya ingin konsultasi untuk membuat website serupa.')) ?>" target="_blank" rel="noopener noreferrer">Konsultasi via WhatsApp <span>↗</span></a><button class="btn portfolio-share-button" id="portfolioShareButtonSidebar" type="button"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.7 10.7 6.6-4.4M8.7 13.3l6.6 4.4"/></svg> Bagikan portofolio</button></div><small>Pesan WhatsApp sudah disiapkan sesuai portofolio ini.</small></aside>
  </div>
</div></section>
</main>
<div class="portfolio-share-dialog" id="portfolioShareDialog" aria-hidden="true"><button class="portfolio-share-backdrop" type="button" aria-label="Tutup menu bagikan"></button><section class="portfolio-share-panel" role="dialog" aria-modal="true" aria-labelledby="portfolioShareTitle"><button class="portfolio-share-close" type="button" aria-label="Tutup">×</button><h2 id="portfolioShareTitle">Mau bagikan lewat mana?</h2><div class="portfolio-share-options">
<button type="button" class="portfolio-share-option" data-share="whatsapp"><span class="portfolio-share-icon wa">◉</span><span>WhatsApp</span></button>
<button type="button" class="portfolio-share-option" data-share="telegram"><span class="portfolio-share-icon telegram">➤</span><span>Telegram</span></button>
<button type="button" class="portfolio-share-option" data-share="line"><span class="portfolio-share-icon line">LINE</span><span>Line</span></button>
<button type="button" class="portfolio-share-option" data-share="facebook"><span class="portfolio-share-icon facebook">f</span><span>Facebook</span></button>
<button type="button" class="portfolio-share-option" data-share="x"><span class="portfolio-share-icon xbrand">𝕏</span><span>X</span></button>
<button type="button" class="portfolio-share-option" data-share="copy"><span class="portfolio-share-icon">🔗</span><span>Salin Link</span></button>
<button type="button" class="portfolio-share-option" data-share="email"><span class="portfolio-share-icon mail">✉</span><span>E-Mail</span></button>
<button type="button" class="portfolio-share-option" data-share="native"><span class="portfolio-share-icon">•••</span><span>Lainnya</span></button>
</div><p class="portfolio-share-dialog-status" id="portfolioShareDialogStatus" role="status" aria-live="polite"></p></section></div>
<div class="portfolio-lightbox" id="portfolioLightbox" aria-hidden="true"><button type="button" class="lightbox-backdrop" aria-label="Tutup tampilan gambar"></button><span class="lightbox-counter" id="lightboxCounter">1 / 1</span><button type="button" class="lightbox-close" aria-label="Tutup gambar">×</button><button type="button" class="lightbox-nav lightbox-prev" aria-label="Gambar sebelumnya">‹</button><div class="lightbox-stage"><img id="portfolioLightboxImage" src="" alt="Tampilan gambar portofolio diperbesar"></div><button type="button" class="lightbox-nav lightbox-next" aria-label="Gambar berikutnya">›</button><div class="lightbox-thumbnails" id="lightboxThumbnails" aria-label="Pilih gambar"></div></div>
<script>
(()=>{const box=document.getElementById('portfolioLightbox');if(!box)return;const image=document.getElementById('portfolioLightboxImage'),counter=document.getElementById('lightboxCounter'),main=document.getElementById('galleryMainImage'),mainCount=document.getElementById('galleryMainCount'),modalThumbs=document.getElementById('lightboxThumbnails');const buttons=[...document.querySelectorAll('[data-gallery-index]')];const sources=buttons.map(b=>b.querySelector('img')?.src).filter(Boolean);if(!sources.length&&main?.src)sources.push(main.src);let active=0;if(modalThumbs){modalThumbs.innerHTML=sources.map((src,i)=>'<button type="button" class="lightbox-thumb" data-lightbox-index="'+i+'" aria-label="Tampilkan gambar '+(i+1)+'"><img src="'+src.replace(/&/g,'&amp;').replace(/\"/g,'&quot;')+'" alt=""></button>').join('');modalThumbs.querySelectorAll('[data-lightbox-index]').forEach(b=>b.addEventListener('click',()=>show(Number(b.dataset.lightboxIndex))))}const updateMain=()=>{if(main){main.src=sources[active];main.alt='Screenshot proyek — gambar '+(active+1)}if(mainCount)mainCount.textContent=(active+1)+' / '+sources.length;buttons.forEach((b,i)=>{b.classList.toggle('is-active',i===active);b.setAttribute('aria-pressed',i===active?'true':'false')})};const show=i=>{if(!sources.length)return;active=(i+sources.length)%sources.length;image.src=sources[active];image.alt='Gambar portofolio '+(active+1)+' dari '+sources.length;if(counter)counter.textContent=(active+1)+' / '+sources.length;if(modalThumbs)modalThumbs.querySelectorAll('[data-lightbox-index]').forEach((b,i)=>{b.classList.toggle('is-active',i===active);if(i===active)b.scrollIntoView({block:'nearest',inline:'nearest',behavior:'smooth'})});updateMain()};const open=i=>{show(i);box.classList.add('is-open');box.setAttribute('aria-hidden','false');document.body.classList.add('gallery-modal-open')};const close=()=>{box.classList.remove('is-open');box.setAttribute('aria-hidden','true');image.src='';document.body.classList.remove('gallery-modal-open')};buttons.forEach((b,i)=>b.addEventListener('click',()=>{active=i;updateMain()}));document.getElementById('galleryMainOpen')?.addEventListener('click',()=>open(active));box.querySelector('.lightbox-prev')?.addEventListener('click',()=>show(active-1));box.querySelector('.lightbox-next')?.addEventListener('click',()=>show(active+1));box.querySelectorAll('.lightbox-close,.lightbox-backdrop').forEach(b=>b.addEventListener('click',close));document.addEventListener('keydown',e=>{if(!box.classList.contains('is-open'))return;if(e.key==='Escape')close();if(e.key==='ArrowLeft')show(active-1);if(e.key==='ArrowRight')show(active+1)});let x=0;box.addEventListener('touchstart',e=>{x=e.changedTouches[0].screenX},{passive:true});box.addEventListener('touchend',e=>{const dx=e.changedTouches[0].screenX-x;if(Math.abs(dx)>50)show(active+(dx<0?1:-1))},{passive:true});updateMain()})();
(()=>{const dialog=document.getElementById('portfolioShareDialog');if(!dialog)return;const status=document.getElementById('portfolioShareDialogStatus'),toast=document.getElementById('siteToast'),url=()=>window.location.href,title=document.title,shareText='Lihat portofolio <?= e(addslashes((string)$detailProject['title'])) ?> di <?= e(addslashes((string)$siteName)) ?>';let toastTimer;const showToast=(message)=>{if(!toast)return;toast.textContent=message;toast.classList.add('show');clearTimeout(toastTimer);toastTimer=setTimeout(()=>toast.classList.remove('show'),1600)};const open=()=>{dialog.classList.add('is-open');dialog.setAttribute('aria-hidden','false');document.body.style.overflow='hidden'};const close=()=>{dialog.classList.remove('is-open');dialog.setAttribute('aria-hidden','true');document.body.style.overflow=''};document.getElementById('portfolioShareButton')?.addEventListener('click',open);document.getElementById('portfolioShareButtonSidebar')?.addEventListener('click',open);dialog.querySelectorAll('.portfolio-share-close,.portfolio-share-backdrop').forEach(b=>b.addEventListener('click',close));document.addEventListener('keydown',e=>{if(e.key==='Escape'&&dialog.classList.contains('is-open'))close()});dialog.querySelectorAll('[data-share]').forEach(button=>button.addEventListener('click',async()=>{const type=button.dataset.share,encodedUrl=encodeURIComponent(url()),encodedText=encodeURIComponent(shareText);const targets={whatsapp:'https://wa.me/?text='+encodedText+'%20'+encodedUrl,telegram:'https://t.me/share/url?url='+encodedUrl+'&text='+encodedText,line:'https://social-plugins.line.me/lineit/share?url='+encodedUrl,facebook:'https://www.facebook.com/sharer/sharer.php?u='+encodedUrl,x:'https://twitter.com/intent/tweet?text='+encodedText+'&url='+encodedUrl,email:'mailto:?subject='+encodeURIComponent(title)+'&body='+encodedText+'%0A'+encodedUrl};if(type==='copy'){try{if(navigator.clipboard&&window.isSecureContext)await navigator.clipboard.writeText(url());else{const t=document.createElement('textarea');t.value=url();t.style.position='fixed';t.style.opacity='0';document.body.appendChild(t);t.select();if(!document.execCommand('copy'))throw Error('copy');t.remove()}status.textContent='Link berhasil disalin';showToast('Link berhasil disalin')}catch(e){status.textContent='Gagal menyalin link';showToast('Gagal menyalin link')}return}if(type==='native'&&navigator.share){try{await navigator.share({title,text:shareText,url:url()});showToast('Berhasil dibagikan')}catch(e){if(e.name!=='AbortError')status.textContent='Fitur berbagi tidak tersedia.'}return}if(type==='native'){status.textContent='Pilih aplikasi dari opsi berbagi perangkat atau gunakan Salin Link.';return}window.open(targets[type],'_blank','noopener,noreferrer,width=700,height=600');showToast('Berhasil dibagikan');}));})();
</script>
<?php require __DIR__ . '/includes/footer.php'; exit; endif; ?>
<main>
<section class="wrap hero" id="beranda">
  <div class="hero-copy">
    <span class="eyebrow"><span class="dot"></span> Website untuk bisnis yang ingin berkembang</span>
    <h1>Bisnis kamu layak punya <span class="gradient">website profesional.</span></h1>
    <p><?= e($siteName) ?> melayani pembuatan website dan aplikasi custom untuk UMKM, bisnis lokal, dan perusahaan. Kami membantu mengubah kebutuhan bisnis menjadi solusi digital yang sesuai alur kerja, target, dan anggaran.i penting, dan menemukan cara untuk menghubungi kamu.</p>
    <p class="hero-supporting">Mulai dari tujuan, proses kerja, dan anggaran yang tersedia. Kami bantu menentukan halaman, fitur, dan alur penggunaan yang benar-benar dibutuhkan—mulai dari website profil bisnis dan toko online hingga aplikasi web khusus. Tampilan dirancang nyaman di HP maupun komputer dan dapat direncanakan untuk berkembang bertahap.</p>
    <div class="hero-benefits" aria-label="Keunggulan layanan"><span><b>✓</b> Desain responsif</span><span><b>✓</b> Sesuai kebutuhan</span><span><b>✓</b> Siap dikembangkan</span></div>
    <div class="actions"><a class="btn btn-primary" href="<?= e('https://wa.me/' . $waNumber . '?text=' . rawurlencode('Halo ' . $siteName . ', saya ingin konsultasi gratis tentang pembuatan website atau aplikasi custom.')) ?>" target="_blank" rel="noopener noreferrer">Konsultasi Gratis via WhatsApp ↗</a><a class="btn btn-outline" href="#portofolio">Lihat portofolio ↓</a></div>
    <div class="micro">Untuk UMKM, bisnis lokal, dan perusahaan · Konsultasi awal gratis · Penawaran disusun sesuai kebutuhan</div>
  </div>
  <div class="preview" aria-label="Ilustrasi website responsif"><div class="browserbar"><i></i><i></i><i></i><span>preview-website.id</span></div>
    <div class="mocksite"><div class="mocktop"><span>brandkamu.</span><span class="mockpill">Website bisnis</span></div><h3>Ide hebat, tampil lebih profesional.</h3><p>Perkenalkan bisnis kamu melalui website yang dirancang sesuai karakter brand.</p><span class="mockbutton">Mulai sekarang ↗</span><div class="mockcards"><div></div><div></div><div></div></div></div>
    <div class="floating-note"><strong>✓ Responsif</strong><span>HP, tablet, dan desktop</span></div>
  </div>
</section>
<section class="section audience-section"><div class="wrap"><div class="section-head"><span class="kicker">Untuk siapa layanan ini?</span><h2>Solusi digital untuk UMKM, bisnis lokal, dan perusahaan.</h2><p>Mulai dari memperkenalkan brand, menerima pesanan, hingga mengelola proses kerja secara online. Website disusun berdasarkan tujuan yang ingin dicapai, supaya pengunjung tahu apa yang kamu tawarkan dan langkah berikutnya yang perlu dilakukan.</p></div><div class="audience-grid"><article class="audience-card"><span class="audience-number">01</span><h3>Kenalkan bisnis</h3><p>Tampilkan profil, layanan, lokasi, jam operasional, dan cara menghubungi bisnis secara profesional.</p><span class="audience-tag">Company profile</span></article><article class="audience-card"><span class="audience-number">02</span><h3>Promosikan produk</h3><p>Buat halaman promosi atau katalog agar calon pelanggan lebih mudah memahami produk dan penawaranmu.</p><span class="audience-tag">Landing page & katalog</span></article><article class="audience-card"><span class="audience-number">03</span><h3>Kelola proses online</h3><p>Jika dibutuhkan, tambahkan fitur seperti akun admin, booking, data pelanggan, atau transaksi.</p><span class="audience-tag">Website dinamis</span></article></div><div class="audience-footnote"><span aria-hidden="true">✦</span> Tidak harus langsung kompleks. Mulai dari fitur yang benar-benar dibutuhkan dan kembangkan seiring waktu.</div></div></section>
<section class="section services-showcase" id="layanan"><div class="wrap">
  <div class="section-head"><span class="kicker">Layanan</span><h2>Pilih layanan sesuai kebutuhan bisnis.</h2><p>Website profil bisnis membantu calon pelanggan mengenal usaha; katalog dan toko online menampilkan produk serta pemesanan; aplikasi web membantu mengelola data dan alur kerja. Jenis layanan yang tersedia ditampilkan pada paket di bawah ini.</p></div>
  <?php foreach (['Website Statis','Website Dinamis'] as $packageCategory): $categoryPackages = array_values(array_filter($servicePackages, fn($item) => $item['category'] === $packageCategory)); if (!$categoryPackages) continue; ?>
  <div class="service-group"><div class="service-group-head"><h3><?= e($packageCategory) ?></h3><p><?= $packageCategory === 'Website Statis' ? 'Cocok untuk bisnis yang membutuhkan website profesional dengan informasi yang relatif jarang berubah. Pembaruan konten dapat dibantu oleh developer.' : 'Cocok untuk bisnis yang membutuhkan pengelolaan data, akun admin, booking, transaksi, atau pembaruan konten mandiri.' ?></p></div>
    <div class="service-cards">
    <?php foreach ($categoryPackages as $package): $packageFeatures = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$package['features']))); ?>
      <?php $serviceDetails = ['category'=>$package['category'],'name'=>$package['name'],'description'=>$package['description'],'price'=>'Rp'.number_format((int)$package['price'],0,',','.'),'original_price'=>'Rp'.number_format((int)$package['price'],0,',','.'),'discount_price'=>((isset($package['discount_price']) && $package['discount_price'] !== null && (int)$package['discount_price'] > 0 && (int)$package['discount_price'] < (int)$package['price']) ? 'Rp'.number_format((int)$package['discount_price'],0,',','.') : ''),'features'=>array_values($packageFeatures),'full_description'=>(string)($package['full_description'] ?? ''),'suitable_for'=>(string)($package['suitable_for'] ?? ''),'work_duration'=>(string)($package['work_duration'] ?? ''),'revision_terms'=>(string)($package['revision_terms'] ?? ''),'included_items'=>array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)($package['included_items'] ?? ''))))),'excluded_items'=>array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)($package['excluded_items'] ?? '')))))]; ?>
      <article class="service-card <?= ($package['ribbon'] ?? '') === 'Pilihan populer' ? 'service-card-featured' : '' ?>" tabindex="0" role="button" aria-label="Lihat detail layanan <?= e($package['name']) ?>" data-service-details="<?= e((string)json_encode($serviceDetails, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) ?>">
        <?php if (!empty($package['ribbon'])): ?><span class="service-ribbon"><?= e($package['ribbon']) ?></span><?php endif; ?>
        <h4><?= e($package['name']) ?></h4><p class="service-desc"><?= e($package['description']) ?></p><div class="price-label"><?= !empty($package['discount_price']) && (int)$package['discount_price'] > 0 && (int)$package['discount_price'] < (int)$package['price'] ? 'Harga promo' : 'Harga mulai' ?></div><div class="service-price"><?php if (!empty($package['discount_price']) && (int)$package['discount_price'] > 0 && (int)$package['discount_price'] < (int)$package['price']): $saving = (int)$package['price'] - (int)$package['discount_price']; ?><span class="service-price-original"><span class="sr-only">Harga normal </span>Rp<?= number_format((int)$package['price'], 0, ',', '.') ?></span><span class="service-price-current">Rp<?= number_format((int)$package['discount_price'], 0, ',', '.') ?></span><span class="service-discount-badge">Hemat Rp<?= number_format($saving, 0, ',', '.') ?></span><?php else: ?>Rp<?= number_format((int)$package['price'], 0, ',', '.') ?><?php endif; ?></div>
        <ul><?php foreach ($packageFeatures as $feature): ?><li><?= e($feature) ?></li><?php endforeach; ?></ul><button class="btn btn-outline service-detail-trigger" type="button">Lihat detail layanan <span aria-hidden="true">↗</span></button><a class="btn <?= !empty($package['ribbon']) ? 'btn-primary' : 'btn-outline' ?>" href="<?= e('?paket=' . rawurlencode((string)$package['name']) . '#konsultasi') ?>">Konsultasikan Kebutuhan</a>
      </article>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <p class="service-note"><strong>Catatan harga, domain, dan hosting:</strong> harga yang tampil adalah harga awal/promo untuk lingkup fitur yang disepakati, bukan otomatis mencakup semua kebutuhan. Domain dan hosting hanya termasuk bila disebutkan secara eksplisit pada detail paket atau penawaran tertulis. Jika tidak tercantum, biaya pembelian dan perpanjangan domain/hosting, email bisnis, layanan pihak ketiga, serta pekerjaan di luar lingkup dibayar terpisah. Sebelum pembayaran, minta rincian fitur, jumlah revisi, masa aktif layanan, biaya tahunan, dan dukungan setelah rilis.</p>
</div></section>
<div class="service-modal" id="serviceDetailModal" aria-hidden="true">
  <div class="service-modal-backdrop" data-service-modal-close></div>
  <section class="service-modal-panel" role="dialog" aria-modal="true" aria-labelledby="serviceModalTitle" aria-describedby="serviceModalDescription">
    <button class="service-modal-close" type="button" aria-label="Tutup detail layanan" data-service-modal-close>×</button>
    <span class="service-modal-kicker" id="serviceModalCategory">Detail layanan</span>
    <h2 id="serviceModalTitle">Detail layanan</h2>
    <p id="serviceModalDescription"></p>
    <div class="service-modal-price"><span>Estimasi harga</span><div class="service-modal-price-values"><del id="serviceModalOriginalPrice" hidden></del><strong id="serviceModalPrice"></strong><span id="serviceModalDiscountBadge" hidden>Harga diskon</span></div></div>
    <section class="service-modal-detail-block" id="serviceModalSuitableWrap" hidden><h3>Cocok untuk</h3><p id="serviceModalSuitable"></p></section>
    <section class="service-modal-detail-block" id="serviceModalDurationWrap" hidden><h3>Durasi pengerjaan</h3><p id="serviceModalDuration"></p></section>
    <section class="service-modal-detail-block" id="serviceModalRevisionWrap" hidden><h3>Ketentuan revisi</h3><p id="serviceModalRevision"></p></section>
    <div class="service-modal-inclusions-grid" id="serviceModalInclusionsGrid">
      <section class="service-modal-detail-block service-modal-inclusion-card" id="serviceModalIncludedWrap" hidden><h3>Yang termasuk</h3><ul id="serviceModalIncluded"></ul></section>
      <section class="service-modal-detail-block service-modal-exclusion-card" id="serviceModalExcludedWrap" hidden><h3>Yang tidak termasuk</h3><ul id="serviceModalExcluded"></ul></section>
    </div>
    <section class="service-modal-detail-block"><h3>Fitur paket</h3><ul id="serviceModalFeatures"></ul></section>
    <p class="service-modal-note" id="serviceModalNote" hidden>Rincian akhir dapat disesuaikan setelah kebutuhan, fitur, dan target website dibahas saat konsultasi.</p>
    <a class="btn btn-primary" id="serviceModalConsult" href="#konsultasi">Konsultasikan layanan ini <span aria-hidden="true">↗</span></a>
  </section>
</div>
<section class="section portfolio-section" id="portofolio"><div class="wrap">
  <div class="portfolio-top"><div class="section-head"><span class="kicker">Portofolio</span><h2>Contoh proyek dan solusi yang dikembangkan.</h2><p>Lihat screenshot, fitur utama, deskripsi proyek, dan tautan demo jika tersedia. Informasi portofolio menjelaskan contoh pengerjaan; status proyek ditampilkan sesuai keterangan yang diisi admin.</p></div><a class="btn btn-outline" href="#konsultasi">Minta contoh sesuai bisnis ↓</a></div>
  <div class="catalog">
  <?php if (!$projects): ?><div class="empty-state">Portofolio sedang disiapkan. Hubungi kami untuk melihat contoh yang sesuai dengan kebutuhanmu.</div>
  <?php else: foreach ($projects as $p):
    $features = array_filter(array_map('trim', explode(',', (string)($p['features'] ?? ''))));
    $legacyColors = ['blue'=>'#5B77EB','green'=>'#18AE70','orange'=>'#F58B2C','pink'=>'#E15B9E','violet'=>'#8B5CF6'];
    $projectColor = strtoupper((string)($p['theme_color'] ?? '#5B77EB'));
    if (isset($legacyColors[strtolower($projectColor)])) $projectColor = $legacyColors[strtolower($projectColor)];
    if (!preg_match('/^#[0-9A-F]{6}$/', $projectColor)) $projectColor = '#5B77EB';
    $colors = ['#5B77EB'=>'cover-blue','#18AE70'=>'cover-green','#F58B2C'=>'cover-orange','#E15B9E'=>'cover-pink','#8B5CF6'=>'cover-violet'];
    $cover = $colors[$projectColor] ?? 'cover-blue';
  ?>
    <article class="project" style="--project-accent:<?= e($projectColor) ?>">
      <a class="project-cover <?= e($cover) ?> project-cover-link <?= !empty($p['image_path']) ? 'has-project-image' : '' ?>" href="<?= e('index.php?project=' . (int)$p['id']) ?>" aria-label="Lihat detail <?= e($p['title']) ?>" <?php if (!empty($p['image_path'])): ?>style="background-image:url(<?= e($p['image_path']) ?>);--project-accent:<?= e($projectColor) ?>"<?php endif; ?>><div class="cover-top"><span><?= e($p['category'] ?: 'Portofolio') ?></span><span class="cover-label"><?= e($p['website_type']) ?></span></div><div class="cover-title"><?= e($p['title']) ?></div><span class="cover-detail-hint">Lihat detail proyek →</span></a>
      <div class="project-body"><h3><?= e($p['title']) ?></h3><p><?= e($p['description']) ?></p>
        <div class="project-meta"><?php foreach (array_slice($features, 0, 4) as $feature): ?><span><?= e($feature) ?></span><?php endforeach; ?></div>
        <div class="project-actions"><a class="btn btn-outline" href="<?= e('https://wa.me/' . $waNumber . '?text=' . rawurlencode('Halo ' . $siteName . ', saya ingin bertanya tentang portofolio "' . $p['title'] . '".
Kategori: ' . ($p['category'] ?: 'Belum ditentukan') . '
Jenis website: ' . $p['website_type'] . '
Saya ingin Tanya Proyek ini untuk membuat website serupa.')) ?>" target="_blank" rel="noopener noreferrer">Tanya proyek ↗</a><a class="btn btn-primary" href="<?= e('index.php?project=' . (int)$p['id']) ?>">Lihat detail →</a></div>
      </div>
    </article>
  <?php endforeach; endif; ?>
  </div>
</div></section>
<section class="section" id="proses"><div class="wrap"><div class="section-head"><span class="kicker">Cara kerja</span><h2>Dari ide menjadi website, langkah demi langkah.</h2><p>Alur kerja yang jelas membantu kamu mengetahui kebutuhan, proses pengerjaan, dan hasil yang akan diterima.</p></div>
<div class="steps"><article class="step"><span class="step-num">LANGKAH 01</span><h3>Konsultasi kebutuhan</h3><p>Ceritakan bisnis, pengguna, tujuan, fitur, dan anggaran melalui WhatsApp.</p></article><article class="step"><span class="step-num">LANGKAH 02</span><h3>Penawaran & pembayaran awal</h3><p>Kami rincikan lingkup kerja, biaya, estimasi waktu, jumlah revisi, domain/hosting, serta skema DP dan pelunasan jika digunakan.</p></article><article class="step"><span class="step-num">LANGKAH 03</span><h3>Desain & pengembangan</h3><p>Website atau aplikasi dibuat sesuai kesepakatan. Materi dan persetujuan dari klien membantu menjaga jadwal.</p></article><article class="step"><span class="step-num">LANGKAH 04</span><h3>Pengujian & revisi</h3><p>Fungsi utama dan tampilan diuji di HP serta komputer. Perbaikan dilakukan sesuai lingkup dan ketentuan revisi.</p></article><article class="step"><span class="step-num">LANGKAH 05</span><h3>Serah terima & dukungan</h3><p>Setelah pembayaran dan pemeriksaan sesuai kesepakatan, website dipublikasikan serta akses/aset yang termasuk diserahkan. Garansi dan perawatan mengikuti penawaran.</p></article></div>
<div class="sales-model-landing" id="model-bisnis">
  <div class="section-head"><span class="kicker">Cara kerja & model kerja sama</span><h3 class="sales-model-heading">Pilih cara kerja sama yang sesuai.</h3><p>Bagian ini menjelaskan siapa yang mengerjakan website, cara pembayaran, serta dukungan setelah website selesai. Untuk kebanyakan kebutuhan bisnis, mulai dari pembuatan proyek atau perawatan berkala; model sewa dan platform mandiri tersedia bila memang sesuai.</p></div>
  <div class="sales-model-cards">
    <article class="sales-model-card"><span class="sales-model-number">01</span><h3>Project-Based</h3><p class="sales-model-subtitle">Jual lepas / beli putus</p><dl><dt>Skema pembayaran</dt><dd>Bayar sekali, bisa menggunakan DP dan pelunasan setelah proyek selesai.</dd><dt>Hak milik website & aset</dt><dd>Menjadi milik klien setelah pembayaran lunas, termasuk kode dan database sesuai kesepakatan.</dd><dt>Cocok untuk</dt><dd>Company profile, website perusahaan, atau toko online khusus.</dd></dl></article>
    <article class="sales-model-card"><span class="sales-model-number">02</span><h3>Website Retainer</h3><p class="sales-model-subtitle">Maintenance & perawatan</p><dl><dt>Skema pembayaran</dt><dd>Biaya rutin bulanan atau tahunan.</dd><dt>Hak milik website & aset</dt><dd>Tetap milik klien. Biaya dibayarkan untuk perawatan, backup, pembaruan, dan keamanan.</dd><dt>Cocok untuk</dt><dd>Website yang sudah dibuat dan perlu dirawat secara berkala.</dd></dl></article>
    <article class="sales-model-card"><span class="sales-model-number">03</span><h3>WaaS</h3><p class="sales-model-subtitle">Website as a Service / sewa website</p><dl><dt>Skema pembayaran</dt><dd>Langganan bulanan tetap, dengan biaya awal lebih ringan; paket dapat mencakup hosting, domain, dan perawatan.</dd><dt>Hak milik website & aset</dt><dd>Website dan sistem tetap milik agensi. Klien mendapat hak pakai selama langganan aktif.</dd><dt>Cocok untuk</dt><dd>UMKM yang ingin punya website tanpa biaya awal besar.</dd></dl></article>
    <article class="sales-model-card"><span class="sales-model-number">04</span><h3>SaaS</h3><p class="sales-model-subtitle">Platform pembuat website</p><dl><dt>Skema pembayaran</dt><dd>Langganan bulanan atau tahunan sesuai paket fitur dan kapasitas.</dd><dt>Hak milik website & aset</dt><dd>Platform tetap milik penyedia. Pengguna memakai alatnya untuk membuat dan mengelola website sendiri.</dd><dt>Cocok untuk</dt><dd>Pengguna yang ingin membuat website sendiri melalui platform siap pakai.</dd></dl></article>
  </div><p class="sales-model-disclaimer">Rincian biaya, domain, hosting, akses, serta kepemilikan akhir mengikuti penawaran dan perjanjian yang disepakati sebelum pengerjaan.</p>
</div>

</div></section>
<section class="section why-ruangweb-section" id="keunggulan"><div class="wrap">
  <div class="section-head"><span class="kicker">Kenapa <?= e($siteName) ?>?</span><h2>Website yang dirancang untuk kebutuhan nyata.</h2><p>Bukan sekadar terlihat menarik. Struktur, fitur, dan pengalaman pengguna disesuaikan dengan tujuan website kamu.</p></div>
  <div class="why-ruangweb-grid">
    <article class="why-ruangweb-card"><span class="why-ruangweb-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4M7 8h10M7 11h6"/></svg></span><h3>Nyaman di semua layar</h3><p>Tata letak dirancang agar tetap mudah digunakan melalui ponsel, tablet, maupun komputer.</p></article>
    <article class="why-ruangweb-card"><span class="why-ruangweb-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 14.5 9.5 21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5L12 3Z"/></svg></span><h3>Sesuai kebutuhan</h3><p>Halaman dan fitur dibahas berdasarkan tujuan bisnis, bukan sekadar menambahkan fitur yang tidak diperlukan.</p></article>
    <article class="why-ruangweb-card"><span class="why-ruangweb-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4zM4 9h16M8 5v4M8 13h4M8 16h8"/></svg></span><h3>Alur yang jelas</h3><p>Kebutuhan, estimasi, proses pengerjaan, dan peninjauan hasil dibicarakan agar ekspektasi lebih terarah.</p></article>
    <article class="why-ruangweb-card"><span class="why-ruangweb-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19V5M4 19h16M7 15l4-4 3 2 5-6"/><path d="M15 7h4v4"/></svg></span><h3>Dapat dikembangkan</h3><p>Jenis website dan fondasi fitur dipilih dengan mempertimbangkan kebutuhan saat ini serta rencana berikutnya.</p></article>
  </div>
</div></section>
<section class="section"><div class="wrap"><div class="cta-panel"><div><span class="kicker">Mulai dari konsultasi</span><h2>Belum tahu website yang cocok?</h2><p>Ceritakan kebutuhan bisnis kamu. Kita bahas solusi yang masuk akal sebelum menentukan fitur dan anggaran.</p></div><a class="btn btn-primary" href="<?= e('?paket=' . rawurlencode('Belum tahu, ingin konsultasi dulu') . '#konsultasi') ?>">Chat WhatsApp sekarang ↓</a></div></div></section>
<?php if ($faqItems): ?><section class="section" id="faq"><div class="wrap faq"><div class="section-head"><span class="kicker">FAQ</span><h2>Hal yang sering ditanyakan.</h2><p>Belum menemukan jawaban? Silakan konsultasi langsung melalui WhatsApp.</p><a class="btn btn-outline" href="#konsultasi">Tanyakan langsung ↓</a></div>
<div class="faq-list"><?php foreach ($faqItems as $faq): ?><details><summary><?= e((string)$faq['question']) ?></summary><p><?= nl2br(e((string)$faq['answer'])) ?></p></details><?php endforeach; ?></div></div></section><?php endif; ?>
<section class="section contact-section" id="konsultasi"><div class="wrap contact-grid">
  <div class="contact-copy"><span class="kicker">Pesan atau konsultasi</span><h2>Isi singkat, lalu lanjut ngobrol di WhatsApp.</h2><p>Pesan di samping terisi otomatis dari isian kamu. Tombol akan membuka WhatsApp, lalu kamu tinggal meninjau dan mengirim pesan.</p><div class="contact-note"><span class="contact-note-icon">↗</span><span>Konsultasi awal untuk memahami kebutuhan, fitur, dan perkiraan biaya website.</span></div></div>
  <form class="contact-form" id="consultationForm">
    <label for="consultName">Nama Anda</label><input id="consultName" name="nama" type="text" placeholder="Contoh: Budi" autocomplete="name" required>
    <label for="consultBusiness">Jenis usaha</label><input id="consultBusiness" name="usaha" type="text" placeholder="Contoh: Kedai kopi, klinik gigi, bimbel" required>
    <label for="consultPackage">Paket yang diminati</label><select id="consultPackage" name="paket" required><option value="" disabled selected>Pilih paket website</option><?php foreach ($servicePackages as $package): ?><option value="<?= e($package['name']) ?>" data-package-name="<?= e($package['name']) ?>"><?= e($package['name']) ?> (<?= !empty($package['discount_price']) && (int)$package['discount_price'] > 0 && (int)$package['discount_price'] < (int)$package['price'] ? 'diskon Rp'.number_format((int)$package['discount_price'], 0, ',', '.') : 'mulai Rp'.number_format((int)$package['price'], 0, ',', '.') ?>)</option><?php endforeach; ?><option value="Belum tahu, ingin konsultasi dulu" data-package-name="Belum tahu, ingin konsultasi dulu">Belum tahu, ingin konsultasi dulu</option></select>
    <label for="consultNeeds">Kebutuhan (boleh dikosongkan)</label><textarea id="consultNeeds" name="kebutuhan" rows="4" placeholder="Contoh: ingin ada galeri produk dan peta lokasi"></textarea>
    <div class="message-preview" aria-live="polite"><span>Pratinjau pesan</span><p id="consultPreview">Halo <?= e($siteName) ?>, saya ingin konsultasi pembuatan website.</p></div>
    <button class="btn btn-primary contact-submit" type="submit"><span aria-hidden="true">●</span> Kirim lewat WhatsApp</button>
  </form>
</div></section>
<script>
(function(){
  const modal=document.getElementById('serviceDetailModal');
  if(!modal)return;
  const title=modal.querySelector('#serviceModalTitle');
  const category=modal.querySelector('#serviceModalCategory');
  const description=modal.querySelector('#serviceModalDescription');
  const price=modal.querySelector('#serviceModalPrice');
  const consult=modal.querySelector('#serviceModalConsult');
  let lastFocus=null;
  function fillList(id,items){
    const list=modal.querySelector('#'+id);list.replaceChildren();
    (Array.isArray(items)?items:[]).filter(item=>String(item).trim()!=='').forEach(item=>{const li=document.createElement('li');li.textContent=String(item).trim();list.appendChild(li);});
    const section=list.closest('.service-modal-detail-block');if(section)section.hidden=list.children.length===0;
  }
  function fillText(id,value){
    const el=modal.querySelector('#'+id);el.textContent=String(value||'').trim();
    const section=el.closest('.service-modal-detail-block');if(section)section.hidden=el.textContent==='';
  }
  function openModal(card){
    lastFocus=document.activeElement;
    let data={};try{data=JSON.parse(card.dataset.serviceDetails||'{}')}catch(error){data={};}
    title.textContent=data.name||'Detail layanan';category.textContent=data.category||'Detail layanan';
    description.textContent=(data.full_description||'').trim()||data.description||'';
    price.textContent=data.discount_price||data.price||'Hubungi kami';
    const originalPrice=modal.querySelector('#serviceModalOriginalPrice');
    const discountBadge=modal.querySelector('#serviceModalDiscountBadge');
    const hasDiscount=Boolean(data.discount_price);
    originalPrice.textContent=data.original_price||data.price||'';originalPrice.hidden=!hasDiscount;
    discountBadge.hidden=!hasDiscount;
    fillText('serviceModalSuitable',data.suitable_for);
    fillText('serviceModalDuration',data.work_duration);
    fillText('serviceModalRevision',data.revision_terms);
    fillList('serviceModalIncluded',data.included_items);
    fillList('serviceModalExcluded',data.excluded_items);
    const inclusionsGrid=modal.querySelector('#serviceModalInclusionsGrid');
    inclusionsGrid.hidden=modal.querySelector('#serviceModalIncludedWrap').hidden&&modal.querySelector('#serviceModalExcludedWrap').hidden;
    fillList('serviceModalFeatures',data.features);
    modal.querySelector('#serviceModalNote').hidden=!(data.full_description||data.suitable_for||data.work_duration||data.revision_terms||((data.included_items||[]).length)||((data.excluded_items||[]).length));
    const consultationLink=card.querySelector('a[href*="#konsultasi"]');
    consult.href=consultationLink?.getAttribute('href')||'#konsultasi';
    modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.body.classList.add('service-modal-open');
    modal.querySelector('.service-modal-close').focus();
  }
  function closeModal(){modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('service-modal-open');if(lastFocus&&typeof lastFocus.focus==='function')lastFocus.focus();}
  document.querySelectorAll('.service-card').forEach(card=>{
    card.addEventListener('click',event=>{if(event.target.closest('a'))return;if(event.target.closest('button')&&!event.target.closest('.service-detail-trigger'))return;openModal(card);});
    card.addEventListener('keydown',event=>{if(event.target!==card)return;if(event.key==='Enter'||event.key===' '){event.preventDefault();openModal(card);}});
  });
  modal.querySelectorAll('[data-service-modal-close]').forEach(el=>el.addEventListener('click',closeModal));
  document.addEventListener('keydown',event=>{if(event.key==='Escape'&&modal.classList.contains('is-open'))closeModal();});
})();
</script>
<script>
(function(){
  const form=document.getElementById('consultationForm');
  if(!form)return;
  const fields={nama:document.getElementById('consultName'),usaha:document.getElementById('consultBusiness'),paket:document.getElementById('consultPackage'),kebutuhan:document.getElementById('consultNeeds')};
  const preview=document.getElementById('consultPreview');
  const siteName=<?= json_encode($siteName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const requestedPackage=new URLSearchParams(window.location.search).get('paket');
  if(requestedPackage){const matchingOption=[...fields.paket.options].find(option=>option.dataset.packageName===requestedPackage);if(matchingOption){fields.paket.value=matchingOption.value;}}
  function message(){const n=fields.nama.value.trim()||'(nama belum diisi)';const u=fields.usaha.value.trim()||'(jenis usaha belum diisi)';const p=fields.paket.value||'(paket belum dipilih)';const k=fields.kebutuhan.value.trim();return 'Halo '+siteName+', saya '+n+'. Saya memiliki usaha '+u+' dan tertarik dengan paket '+p+'.'+(k?'\nKebutuhan: '+k:'')+'\nSaya ingin konsultasi lebih lanjut.';}
  function update(){preview.textContent=message();}
  Object.values(fields).forEach(el=>el.addEventListener('input',update));
  form.addEventListener('submit',function(e){e.preventDefault();if(!form.reportValidity())return;const url='https://wa.me/<?= e($waNumber) ?>?text='+encodeURIComponent(message());window.open(url,'_blank','noopener,noreferrer');});
  update();
})();
</script>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
