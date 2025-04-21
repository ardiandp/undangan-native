<?php
require_once __DIR__ . '/../includes/functions.php';

// Ambil ID unik dari URL
$id_unik = isset($_GET['id']) ? $_GET['id'] : '';

// Dapatkan data undangan
$undangan = getUndanganByUnikId($id_unik);

if (!$undangan) {
    header("Location: index.php");
    exit();
}

// Catat kunjungan
addHitCounter($undangan['id'], $_SERVER['REMOTE_ADDR']);

// Dapatkan data terkait
$ucapan = getUcapanByUndanganId($undangan['id']);
$galeri = getGaleriByUndanganId($undangan['id']);
$total_hits = getTotalHits($undangan['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Undangan - <?= htmlspecialchars($undangan['judul_undangan']) ?></title>
    
    <!-- Gunakan CSS yang sama dengan index.php -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/theme-<?= $undangan['tema'] ?>.css">
</head>
<body>
    <!-- Header -->
    <header class="detail-header">
        <div class="container">
            <h1><?= htmlspecialchars($undangan['judul_undangan']) ?></h1>
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="detail-container">
        <!-- Info Pasangan -->
        <section class="detail-section couple-info">
            <div class="couple-images">
                <img src="../assets/images/uploads/<?= $undangan['foto_pasangan'] ?>" alt="<?= htmlspecialchars($undangan['nama_pria'] . ' & ' . $undangan['nama_wanita']) ?>">
            </div>
            <div class="couple-names">
                <h2><?= htmlspecialchars($undangan['nama_pria']) ?> & <?= htmlspecialchars($undangan['nama_wanita']) ?></h2>
                <p class="wedding-date"><?= date('d F Y', strtotime($undangan['tanggal_akad'])) ?></p>
            </div>
        </section>

        <!-- Detail Acara -->
        <section class="detail-section event-details">
            <h3><i class="fas fa-calendar-alt"></i> Detail Acara</h3>
            
            <div class="event-card">
                <h4>Akad Nikah</h4>
                <p><i class="far fa-clock"></i> <?= date('H:i', strtotime($undangan['tanggal_akad'])) ?> WIB</p>
                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($undangan['tempat_akad']) ?></p>
            </div>

            <?php if ($undangan['tanggal_resepsi']): ?>
            <div class="event-card">
                <h4>Resepsi</h4>
                <p><i class="far fa-clock"></i> <?= date('H:i', strtotime($undangan['tanggal_resepsi'])) ?> WIB</p>
                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($undangan['tempat_resepsi']) ?></p>
            </div>
            <?php endif; ?>

            <div class="map-container">
                <iframe src="<?= htmlspecialchars($undangan['google_maps']) ?>" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </section>

        <!-- Galeri -->
        <?php if (!empty($galeri)): ?>
        <section class="detail-section gallery-section">
            <h3><i class="fas fa-images"></i> Galeri</h3>
            <div class="gallery-grid">
                <?php foreach ($galeri as $foto): ?>
                <div class="gallery-item">
                    <img src="../assets/images/uploads/<?= $foto['nama_file'] ?>" alt="<?= htmlspecialchars($foto['keterangan']) ?>">
                    <?php if ($foto['keterangan']): ?>
                    <p class="gallery-caption"><?= htmlspecialchars($foto['keterangan']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Ucapan -->
        <section class="detail-section wishes-section">
            <h3><i class="fas fa-comments"></i> Ucapan</h3>
            
            <div class="wishes-list">
                <?php if (!empty($ucapan)): ?>
                    <?php foreach ($ucapan as $item): ?>
                    <div class="wish-item">
                        <div class="wish-header">
                            <h4><?= htmlspecialchars($item['nama_pengirim']) ?></h4>
                            <small><?= date('d M Y H:i', strtotime($item['waktu_kirim'])) ?></small>
                        </div>
                        <p><?= htmlspecialchars($item['pesan']) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-wishes">Belum ada ucapan</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="detail-footer">
        <p>Total Dilihat: <?= $total_hits ?> kali</p>
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($undangan['nama_pria']) ?> & <?= htmlspecialchars($undangan['nama_wanita']) ?></p>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Inisialisasi AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Smooth scrolling untuk anchor link
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top - 20
            }, 500);
        });
    </script>
</body>
</html>