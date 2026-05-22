<?php
$id = $_GET['id'] ?? '';

// Default Fallback jika data kosong
$destination = 'https://shopee.co.id';
$title       = 'Lihat Video Selengkapnya';
$description = 'Facebook.com';
$image       = '';

// Ambil data dari JSON
if (!empty($id) && file_exists('database.json')) {
    $dbData = json_decode(file_get_contents('database.json'), true);
    if (isset($dbData[$id])) {
        $destination = $dbData[$id]['dest'];
        $title       = $dbData[$id]['title'];
        $description = $dbData[$id]['desc'];
        $image       = $dbData[$id]['img'];
    }
}

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// 1. DETEKSI BOT RESMI FACEBOOK (Crawler/Scraper)
// Cek apakah yang datang adalah bot pemeriksa tautan milik Facebook
$isFacebookBot = (strpos($userAgent, 'facebookexternalhit') !== false || strpos($userAgent, 'Facebot') !== false);

// 2. DETEKSI KLIK ASLI MANUSIA DI DALAM APLIKASI FB
$isInAppFacebook = (strpos($userAgent, 'FBAN') !== false || strpos($userAgent, 'FBAV') !== false || strpos($userAgent, 'FB_IAB') !== false);

if ($isInAppFacebook) {
    // --- JIKA MANUSIA ASLI (Klik dari dalam aplikasi FB): LANGSUNG LEMPAR KE SHOPEE ---
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="referrer" content="no-referrer">
        <meta http-equiv="refresh" content="0;url=<?php echo $destination; ?>">
        <script type="text/javascript">
            window.location.replace("<?php echo $destination; ?>");
        </script>
    </head>
    <body>
        <p>Menuju halaman produk...</p>
    </body>
    </html>
    <?php
    exit;
} else {
    // --- JIKA BUKAN DARI IN-APP FB (Bisa jadi Bot Crawler FB, atau User dari Chrome/Safari) ---
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
        
        <meta property="og:type" content="website" />
        <meta property="og:title" content="<?php echo htmlspecialchars($title); ?>" />
        <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>" />
        <meta property="og:image" content="<?php echo htmlspecialchars($image); ?>" />
        <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($image); ?>" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        
        <?php if (!$isFacebookBot): ?>
        <meta http-equiv="refresh" content="1;url=<?php echo $destination; ?>">
        <script type="text/javascript">
            setTimeout(function(){
                window.location.replace("<?php echo $destination; ?>");
            }, 1000);
        </script>
        <?php endif; ?>
    </head>
    <body style="background:#f0f2f5; font-family:sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; margin:0;">
        <div style="text-align:center;">
            <p>Memuat Konten...</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
