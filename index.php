<?php
$resultUrl = "";
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shopeeLink = $_POST['shopeeLink'] ?? '';
    $title       = $_POST['title'] ?? '';
    $desc        = 'facebook.com'; // Otomatis terkunci ke facebook.com sesuai permintaan Anda
    
    if (!empty($shopeeLink) && !empty($title) && isset($_FILES['imageFile'])) {
        $file = $_FILES['imageFile'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($fileExtension, $allowedExtensions)) {
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            
            $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
            $uploadTarget = 'uploads/' . $newFileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadTarget)) {
                $dbFile = 'database.json';
                $currentData = file_exists($dbFile) ? (json_decode(file_get_contents($dbFile), true) ?? []) : [];
                
                $uniqueId = rand(10000, 99999);
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $currentDir = str_replace('index.php', '', $currentUrl);
                
                $currentData[$uniqueId] = [
                    'dest' => $shopeeLink,
                    'title' => $title,
                    'desc' => $desc,
                    'img' => $currentDir . $uploadTarget
                ];
                
                file_put_contents($dbFile, json_encode($currentData, JSON_PRETTY_PRINT));
                $resultUrl = $currentDir . "redirect.php?id=" . $uniqueId;
            } else {
                $errorMsg = "Gagal mengupload gambar ke server.";
            }
        } else {
            $errorMsg = "Format file tidak didukung!";
        }
    } else {
        $errorMsg = "Mohon isi semua kolom wajib.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Link Preview Affiliate v2.1</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <form method="POST" enctype="multipart/form-data" id="genForm" class="bg-white p-6 rounded-xl shadow-lg w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2">📝 Pengaturan Konten</h2>
            
            <?php if(!empty($errorMsg)): ?>
                <div class="mb-3 p-2 bg-red-100 text-red-700 text-sm rounded-lg"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Link Affiliate Shopee *</label>
                    <input type="url" name="shopeeLink" id="shopeeLink" required placeholder="https://s.shopee.co.id/..." class="mt-1 w-full p-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Judul Postingan (Title) *</label>
                    <input type="text" name="title" id="title" required placeholder="Contoh: Liat tatapan mereka berdua..." class="mt-1 w-full p-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400">Deskripsi Singkat</label>
                    <input type="text" name="desc" id="desc" value="facebook.com" readonly class="mt-1 w-full p-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-500 outline-none cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Upload Gambar dari Galeri *</label>
                    <input type="file" name="imageFile" id="imageFile" accept="image/*" required class="mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>

                <button type="submit" id="submitBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition shadow-md cursor-pointer">
                    Proses & Ambil Link Pendek
                </button>
            </div>

            <?php if(!empty($resultUrl)): ?>
            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm font-semibold text-green-800 mb-2">✅ Sukses! Salin link dibawah ini:</p>
                <div class="flex gap-2">
                    <input type="text" id="resultLink" readonly value="<?php echo $resultUrl; ?>" class="w-full p-2 bg-white border border-green-300 rounded-lg text-sm text-blue-600 font-mono focus:outline-none">
                    
                    <button type="button" onclick="copyToClipboard()" id="btnCopy" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg text-sm transition flex items-center gap-1 shrink-0 cursor-pointer">
                        <svg id="iconCopy" class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                        <span id="textCopy">Copy</span>
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 flex flex-col justify-center">
            <h2 class="text-sm font-semibold text-gray-500 mb-3">🌐 Preview di Facebook</h2>
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm relative">
                
                <a href="#" id="infoLink" target="_blank" class="absolute top-3 right-3 bg-black/60 hover:bg-black/80 text-white w-7 h-7 rounded-full flex items-center justify-center font-serif text-sm font-bold z-10 shadow-md border border-white/20">i</a>
                
                <div class="relative bg-black aspect-[1.91/1] flex items-center justify-center overflow-hidden">
                    <img id="prevImg" src="https://via.placeholder.com/600x315?text=Pilih+Gambar" class="w-full h-full object-cover">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/10">
                        <svg class="w-16 h-16 text-white/90 drop-shadow-lg" fill="currentColor" viewBox="0 0 84 84"><circle cx="42" cy="42" r="40" fill="none" stroke="white" stroke-width="4"/><path d="M33 25v34l26-17z"/></svg>
                    </div>
                </div>
                
                <div class="p-3 bg-white border-t border-gray-100">
                    <p class="text-xs text-gray-500 lowercase tracking-wide">facebook.com</p>
                    <p id="prevTitle" class="text-sm font-semibold text-gray-900 mt-0.5 line-clamp-2">Judul postingan Anda</p>
                </div>
            </div>
        </div>
    </form>

    <script>
        const titleInput = document.getElementById('title');
        const shopeeInput = document.getElementById('shopeeLink');
        const fileInput = document.getElementById('imageFile');
        const form = document.getElementById('genForm');
        const btn = document.getElementById('submitBtn');

        titleInput.addEventListener('input', () => document.getElementById('prevTitle').innerText = titleInput.value || 'Judul postingan Anda');
        
        // Menghubungkan tanda (i) dengan link input shopee secara dinamis
        shopeeInput.addEventListener('input', () => document.getElementById('infoLink').href = shopeeInput.value || '#');
        
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) { document.getElementById('prevImg').src = e.target.result; }
                reader.readAsDataURL(file);
            }
        });

        form.addEventListener('submit', () => {
            btn.innerText = 'Memproses...';
            btn.disabled = true;
            btn.classList.add('opacity-70');
        });

        function copyToClipboard() {
            const copyText = document.getElementById("resultLink");
            const btnCopy = document.getElementById("btnCopy");
            const textCopy = document.getElementById("textCopy");
            
            navigator.clipboard.writeText(copyText.value).then(() => {
                btnCopy.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                btnCopy.classList.add('bg-blue-600');
                textCopy.innerText = "Copied!";
                
                setTimeout(() => {
                    btnCopy.classList.remove('bg-blue-600');
                    btnCopy.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                    textCopy.innerText = "Copy";
                }, 2000);
            });
        }
    </script>
</body>
</html>
