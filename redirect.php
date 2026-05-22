<?php
// Matikan buffer output agar pengalihan bisa berjalan instan tanpa jeda script
ob_start();

$id = $_GET['id'] ?? '';

// Default Fallback jika data kosong
$destination = 'https://shopee.co.id';
$title       = 'Lihat Video Selengkapnya';
$image       = '';

// Ambil data dari JSON
if (!empty($id) && file_exists('database.json')) {
    $dbData = json_decode(file_get_contents('database.json'), true);
    if (isset($dbData[$id])) {
        $destination = $dbData[$id]['dest'];
        $title       = $dbData[$id]['title'];
        $image       = $dbData[$id]['img'];
    }
}

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Deteksi Akurat Bot Crawler Facebook
$isFacebookBot = (strpos($userAgent, 'facebookexternalhit') !== false || 
                  strpos($userAgent, 'Facebot') !== false || 
                  strpos($userAgent, 'facebookplatform') !== false);

if (!$isFacebookBot) {
    // --- JIKA MANUSIA ASLI (Langsung redirect INSTAN di tingkat server tanpa memuat HTML) ---
    // Ini taktik paling ampuh agar tidak memicu popup peringatan "Keluar dari Aplikasi"
    header("Location: " . $destination, true, 301);
    exit;
} else {
    // --- JIKA BOT FACEBOOK (Untuk memancing gambar preview besar & rapi) ---
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
        
        <meta property="og:type" content="article" />
        <meta property="og:title" content="<?php echo htmlspecialchars($title); ?>" />
        <meta property="og:description" content="facebook.com" />
        <meta property="og:image" content="<?php echo htmlspecialchars($image); ?>" />
        <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($image); ?>" />
        
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:image:type" content="image/jpeg" />
        
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo htmlspecialchars($title); ?>">
        <meta name="twitter:description" content="facebook.com">
        <meta name="twitter:image" content="<?php echo htmlspecialchars($image); ?>">
    </head>
    <body>
        </body>
    </html>
    <?php
    exit;
}
ob_end_flush();
?>
