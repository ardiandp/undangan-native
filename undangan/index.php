<?php
require_once __DIR__ . '/../includes/functions.php';

// Ambil ID unik dari URL
$id_unik = isset($_GET['to']) ? $_GET['to'] : '';

// Dapatkan data undangan
$undangan = getUndanganByUnikId($id_unik);

if (!$undangan) {
    die("Undangan tidak ditemukan");
}

// Catat kunjungan
addHitCounter($undangan['id'], $_SERVER['REMOTE_ADDR']);

// Dapatkan data tamu (jika ada parameter nama tamu)
$nama_tamu = isset($_GET['nama']) ? urldecode($_GET['nama']) : '';
$tamu = null;
if ($nama_tamu) {
    $tamu = getTamuByUndanganId($undangan['id']);
    $tamu = array_filter($tamu, function($t) use ($nama_tamu) {
        return strtolower($t['nama_tamu']) === strtolower($nama_tamu);
    });
    $tamu = reset($tamu);
}

// Dapatkan ucapan dan galeri
$ucapan = getUcapanByUndanganId($undangan['id']);
$galeri = getGaleriByUndanganId($undangan['id']);
$total_hits = getTotalHits($undangan['id']);

// Generate link share
$link_undangan = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?to=" . $id_unik;
if ($nama_tamu) {
    $link_undangan .= "&nama=" . urlencode($nama_tamu);
}
$share_text = "Undangan Pernikahan " . $undangan['nama_pria'] . " & " . $undangan['nama_wanita'] . ". Buka link berikut: " . $link_undangan;
$whatsapp_link = "https://wa.me/?text=" . urlencode($share_text);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Undangan pernikahan <?= htmlspecialchars($undangan['nama_pria']) ?> & <?= htmlspecialchars($undangan['nama_wanita']) ?>">
    <title><?= htmlspecialchars($undangan['judul_undangan']) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="../assets/images/uploads/<?= $undangan['foto_pasangan'] ?>" type="image/png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/theme-<?= $undangan['tema'] ?>.css">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="../assets/images/uploads/<?= $undangan['foto_cover'] ?>" as="image">
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen">
        <div class="spinner"></div>
        <p>Memuat Undangan...</p>
    </div>

    <!-- Floating Music Player -->
    <div class="music-player">
        <audio id="wedding-music" loop>
            <source src="../assets/audio/music.mp3" type="audio/mpeg">
        </audio>
        <button id="music-toggle" class="music-btn">
            <i class="fas fa-music"></i>
            <span id="music-status">Musik: OFF</span>
        </button>
    </div>

    <!-- Floating Navigation -->
    <nav class="floating-nav">
        <ul>
            <li><a href="#home"><i class="fas fa-home"></i></a></li>
            <li><a href="#couple"><i class="fas fa-heart"></i></a></li>
            <li><a href="#event"><i class="fas fa-calendar-alt"></i></a></li>
            <li><a href="#gallery"><i class="fas fa-images"></i></a></li>
            <li><a href="#wishes"><i class="fas fa-comments"></i></a></li>
        </ul>
    </nav>

    <div class="undangan-container">
        <!-- Header Cover -->
        <section id="home" class="cover-section">
            <div class="cover" style="background-image: url('../assets/images/uploads/<?= $undangan['foto_cover'] ?>')">
                <div class="overlay">
                    <div class="couple-names animate__animated animate__fadeInDown">
                        <h1><?= htmlspecialchars($undangan['nama_pria']) ?> <span class="and-symbol">&</span> <?= htmlspecialchars($undangan['nama_wanita']) ?></h1>
                    </div>
                    <div class="save-the-date animate__animated animate__fadeInUp">
                        <p><?= date('d F Y', strtotime($undangan['tanggal_akad'])) ?></p>
                    </div>
                    <div class="scroll-down animate__animated animate__fadeIn animate__delay-1s">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Countdown Timer -->
        <section class="countdown-section">
            <div class="container">
                <h2>Menuju Hari Bahagia</h2>
                <div class="countdown-timer">
                    <div class="timer-box">
                        <span id="days">00</span>
                        <small>Hari</small>
                    </div>
                    <div class="timer-box">
                        <span id="hours">00</span>
                        <small>Jam</small>
                    </div>
                    <div class="timer-box">
                        <span id="minutes">00</span>
                        <small>Menit</small>
                    </div>
                    <div class="timer-box">
                        <span id="seconds">00</span>
                        <small>Detik</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- Info Pasangan -->
        <section id="couple" class="couple-section">
            <div class="section-title">
                <h2 data-aos="fade-up">Kami Yang Berbahagia</h2>
                <div class="heart-divider" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-heart"></i>
                </div>
            </div>
            
            <div class="couple-container">
                <div class="groom" data-aos="fade-right">
                    <div class="couple-img">
                        <img src="../assets/images/uploads/<?= $undangan['foto_pasangan'] ?>" alt="<?= htmlspecialchars($undangan['nama_pria']) ?>">
                    </div>
                    <h3><?= htmlspecialchars($undangan['nama_pria']) ?></h3>
                    <p>Putra dari Bapak <?= htmlspecialchars(explode(' & ', $undangan['nama_ortu_pria'])[0]) ?> <br>dan Ibu <?= htmlspecialchars(explode(' & ', $undangan['nama_ortu_pria'])[1] ?? '') ?></p>
                </div>
                
                <div class="couple-icon" data-aos="zoom-in" data-aos-delay="300">
                    <i class="fas fa-heart"></i>
                </div>
                
                <div class="bride" data-aos="fade-left">
                    <div class="couple-img">
                        <img src="../assets/images/uploads/<?= $undangan['foto_pasangan'] ?>" alt="<?= htmlspecialchars($undangan['nama_wanita']) ?>">
                    </div>
                    <h3><?= htmlspecialchars($undangan['nama_wanita']) ?></h3>
                    <p>Putri dari Bapak <?= htmlspecialchars(explode(' & ', $undangan['nama_ortu_wanita'])[0]) ?> <br>dan Ibu <?= htmlspecialchars(explode(' & ', $undangan['nama_ortu_wanita'])[1] ?? '') ?></p>
                </div>
            </div>
        </section>

        <!-- Acara -->
        <section id="event" class="event-section">
            <div class="section-title">
                <h2 data-aos="fade-up">Detail Acara</h2>
                <div class="heart-divider" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-heart"></i>
                </div>
            </div>
            
            <div class="event-container">
                <div class="event-card" data-aos="fade-up">
                    <div class="event-icon">
                        <i class="fas fa-mosque"></i>
                    </div>
                    <h3>Akad Nikah</h3>
                    <div class="event-details">
                        <p><i class="far fa-calendar-alt"></i> <?= date('l, d F Y', strtotime($undangan['tanggal_akad'])) ?></p>
                        <p><i class="far fa-clock"></i> <?= date('H:i', strtotime($undangan['tanggal_akad'])) ?> WIB</p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($undangan['tempat_akad']) ?></p>
                        <p><?= htmlspecialchars($undangan['alamat_lengkap']) ?></p>
                    </div>
                </div>
                
                <?php if ($undangan['tanggal_resepsi']): ?>
                <div class="event-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="event-icon">
                        <i class="fas fa-glass-cheers"></i>
                    </div>
                    <h3>Resepsi</h3>
                    <div class="event-details">
                        <p><i class="far fa-calendar-alt"></i> <?= date('l, d F Y', strtotime($undangan['tanggal_resepsi'])) ?></p>
                        <p><i class="far fa-clock"></i> <?= date('H:i', strtotime($undangan['tanggal_resepsi'])) ?> WIB</p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($undangan['tempat_resepsi']) ?></p>
                        <p><?= htmlspecialchars($undangan['alamat_lengkap']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="maps-container" data-aos="fade-up">
                <iframe src="<?= htmlspecialchars($undangan['google_maps']) ?>" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </section>

        <!-- Konfirmasi Kehadiran -->
        <section class="rsvp-section">
            <div class="section-title">
                <h2 data-aos="fade-up">Konfirmasi Kehadiran</h2>
                <div class="heart-divider" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-heart"></i>
                </div>
            </div>
            
            <div class="rsvp-container" data-aos="fade-up">
                <?php if ($tamu): ?>
                    <?php if ($tamu['status_konfirmasi'] === 'menunggu'): ?>
                        <form action="proses_konfirmasi.php" method="post" class="rsvp-form">
                            <input type="hidden" name="undangan_id" value="<?= $undangan['id'] ?>">
                            <input type="hidden" name="nama_tamu" value="<?= htmlspecialchars($tamu['nama_tamu']) ?>">
                            
                            <div class="form-group">
                                <label>Status Kehadiran</label>
                                <div class="radio-group">
                                    <label class="radio-btn">
                                        <input type="radio" name="status" value="hadir" checked>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Hadir</span>
                                    </label>
                                    <label class="radio-btn">
                                        <input type="radio" name="status" value="tidak_hadir">
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Tidak Hadir</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Jumlah Hadir</label>
                                <input type="number" name="jumlah_hadir" min="1" value="1" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Pesan (Opsional)</label>
                                <textarea name="pesan" placeholder="Tulis pesan untuk mempelai..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn-submit">Konfirmasi Kehadiran</button>
                        </form>
                    <?php else: ?>
                        <div class="rsvp-confirmed">
                            <i class="fas fa-check-circle"></i>
                            <h3>Terima kasih telah mengkonfirmasi kehadiran Anda</h3>
                            <p>Status: <strong><?= $tamu['status_konfirmasi'] === 'hadir' ? 'Hadir' : 'Tidak Hadir' ?></strong></p>
                            <?php if ($tamu['jumlah_hadir'] > 0): ?>
                                <p>Jumlah Hadir: <strong><?= $tamu['jumlah_hadir'] ?> orang</strong></p>
                            <?php endif; ?>
                            <?php if ($tamu['pesan']): ?>
                                <p>Pesan Anda: <em>"<?= htmlspecialchars($tamu['pesan']) ?>"</em></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="rsvp-info">
                        <i class="fas fa-info-circle"></i>
                        <p>Silakan gunakan link undangan khusus yang dikirimkan kepada Anda untuk konfirmasi kehadiran.</p>
                        <p>Jika Anda belum menerima link khusus, silakan hubungi mempelai.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Galeri -->
        <section id="gallery" class="gallery-section">
            <div class="section-title">
                <h2 data-aos="fade-up">Galeri Kami</h2>
                <div class="heart-divider" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-heart"></i>
                </div>
            </div>
            
            <div class="gallery-container">
                <?php if (!empty($galeri)): ?>
                    <div class="gallery-grid">
                        <?php foreach ($galeri as $foto): ?>
                            <div class="gallery-item" data-aos="zoom-in">
                                <img src="../assets/images/uploads/<?= $foto['nama_file'] ?>" alt="<?= htmlspecialchars($foto['keterangan']) ?>" class="gallery-img">
                                <?php if ($foto['keterangan']): ?>
                                    <div class="gallery-caption"><?= htmlspecialchars($foto['keterangan']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-gallery" data-aos="fade-up">
                        <p>Galeri foto akan segera tersedia</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Ucapan -->
        <section id="wishes" class="wishes-section">
            <div class="section-title">
                <h2 data-aos="fade-up">Ucapan & Doa</h2>
                <div class="heart-divider" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-heart"></i>
                </div>
            </div>
            
            <div class="wishes-container">
                <div class="wishes-form" data-aos="fade-right">
                    <h3>Kirim Ucapan</h3>
                    <form action="proses_ucapan.php" method="post">
                        <input type="hidden" name="undangan_id" value="<?= $undangan['id'] ?>">
                        <div class="form-group">
                            <input type="text" name="nama_pengirim" placeholder="Nama Anda" required>
                        </div>
                        <div class="form-group">
                            <textarea name="pesan" placeholder="Tulis ucapan dan doa Anda..." required></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Kirim Ucapan</button>
                    </form>
                </div>
                
                <div class="wishes-list" data-aos="fade-left">
                    <h3>Ucapan dari Tamu</h3>
                    <?php if (!empty($ucapan)): ?>
                        <div class="wishes-messages">
                            <?php foreach ($ucapan as $item): ?>
                                <div class="wish-item">
                                    <div class="wish-header">
                                        <h4><?= htmlspecialchars($item['nama_pengirim']) ?></h4>
                                        <small><?= date('d M Y H:i', strtotime($item['waktu_kirim'])) ?></small>
                                    </div>
                                    <p><?= htmlspecialchars($item['pesan']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-wishes">
                            <p>Jadilah yang pertama mengirim ucapan</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer-section">
            <div class="footer-container">
                <div class="footer-logo">
                    <h3><?= htmlspecialchars($undangan['nama_pria']) ?> & <?= htmlspecialchars($undangan['nama_wanita']) ?></h3>
                    <p>Terima kasih atas doa dan restu Anda</p>
                </div>
                
                <div class="footer-stats">
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <p><?= count($ucapan) ?> Ucapan</p>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-eye"></i>
                        <p><?= $total_hits ?> Kunjungan</p>
                    </div>
                </div>
                
                <div class="footer-share">
                    <h4>Bagikan Undangan</h4>
                    <div class="share-buttons">
                        <a href="<?= $whatsapp_link ?>" class="whatsapp-btn" target="_blank">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <button onclick="copyToClipboard('<?= $link_undangan ?>')" class="copy-btn">
                            <i class="fas fa-copy"></i> Salin Link
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="footer-copyright">
                <p>&copy; <?= date('Y') ?> Undangan Pernikahan <?= htmlspecialchars($undangan['nama_pria']) ?> & <?= htmlspecialchars($undangan['nama_wanita']) ?></p>
            </div>
        </footer>
    </div>

    <!-- JQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <!-- AOS (Animate On Scroll) -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Magnific Popup -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
    
    <script>
        // Inisialisasi AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Countdown Timer
        function updateCountdown() {
            const weddingDate = new Date("<?= date('M d, Y H:i:s', strtotime($undangan['tanggal_akad'])) ?>").getTime();
            const now = new Date().getTime();
            const distance = weddingDate - now;
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById("days").innerHTML = days.toString().padStart(2, '0');
            document.getElementById("hours").innerHTML = hours.toString().padStart(2, '0');
            document.getElementById("minutes").innerHTML = minutes.toString().padStart(2, '0');
            document.getElementById("seconds").innerHTML = seconds.toString().padStart(2, '0');
            
            if (distance < 0) {
                clearInterval(countdown);
                document.getElementById("days").innerHTML = "00";
                document.getElementById("hours").innerHTML = "00";
                document.getElementById("minutes").innerHTML = "00";
                document.getElementById("seconds").innerHTML = "00";
            }
        }
        
        updateCountdown();
        const countdown = setInterval(updateCountdown, 1000);
        
        // Music Player
        const music = document.getElementById("wedding-music");
        const musicToggle = document.getElementById("music-toggle");
        const musicStatus = document.getElementById("music-status");
        
        musicToggle.addEventListener("click", function() {
            if (music.paused) {
                music.play();
                musicStatus.textContent = "Musik: ON";
                musicToggle.classList.add("playing");
            } else {
                music.pause();
                musicStatus.textContent = "Musik: OFF";
                musicToggle.classList.remove("playing");
            }
        });
        
        // Smooth scrolling for navigation
        $('.floating-nav a').on('click', function(e) {
            e.preventDefault();
            const target = $(this).attr('href');
            $('html, body').animate({
                scrollTop: $(target).offset().top - 70
            }, 800);
        });
        
        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Link undangan berhasil disalin!');
            }, function(err) {
                console.error('Gagal menyalin: ', err);
            });
        }
        
        // Hide loading screen when page is loaded
        $(window).on('load', function() {
            $('.loading-screen').fadeOut('slow');
        });
        
        // Initialize gallery lightbox
        $('.gallery-grid').magnificPopup({
            delegate: 'img',
            type: 'image',
            gallery: {
                enabled: true
            }
        });
    </script>
</body>
</html>