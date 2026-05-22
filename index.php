<?php
$resultUrl = "";
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shopeeLink = $_POST['shopeeLink'] ?? '';
    $title       = $_POST['title'] ?? '';
    $desc        = $_POST['desc'] ?? 'Facebook.com';
    
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
    <title>Generator Link Affiliate Modern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-xl border border-slate-100 grid grid-cols-1 lg:grid-cols-2 overflow-hidden">
        <div class="p-8 lg:p-12">
            <h1 class="text-2xl font-bold text-slate-900 mb-6">Generator Link Preview</h1>
            
            <?php if(!empty($errorMsg)): ?>
                <div class="mb-6 p-4 bg-red-50 text-red-600 text-sm rounded-2xl border border-red-100"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-5" id="genForm">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Link Shopee</label>
                    <input type="url" name="shopeeLink" id="shopeeLink" required placeholder="https://s.shopee.co.id/..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Judul</label>
                        <input type="text" name="title" id="title" required placeholder="Judul..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Deskripsi</label>
                        <input type="text" name="desc" id="desc" placeholder="Facebook.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Unggah Gambar</label>
                    <input type="file" name="imageFile" id="imageFile" accept="image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-6 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer bg-slate-50 rounded-xl border border-slate-200">
                </div>
                <button type="submit" id="submitBtn" class="w-full bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-3.5 rounded-xl transition-all duration-200 shadow-lg mt-4 cursor-pointer">
                    Generate Link
                </button>
            </form>

            <?php if(!empty($resultUrl)): ?>
            <div class="mt-8 p-5 bg-emerald-50 rounded-2xl border border-emerald-100">
                <p class="text-xs font-bold text-emerald-800 uppercase mb-3">Link Siap Digunakan!</p>
                <div class="flex gap-2">
                    <input type="text" id="resultLink" readonly value="<?php echo $resultUrl; ?>" class="w-full p-3 bg-white border border-emerald-200 rounded-lg text-sm text-emerald-700 font-mono focus:outline-none">
                    <button type="button" onclick="copyToClipboard()" id="btnCopy" class="bg-emerald-600 hover:bg-emerald-700 active:scale-90 text-white font-bold px-5 rounded-lg text-sm transition-all duration-150 cursor-pointer">Copy</button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="bg-slate-100 p-8 lg:p-12 flex flex-col items-center justify-center border-l border-slate-200">
            <h2 class="text-xs font-bold text-slate-400 uppercase mb-6">Live Preview</h2>
            <div class="bg-white w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl">
                <div class="aspect-[1.91/1] bg-slate-200"><img id="prevImg" src="https://via.placeholder.com/600x315?text=Preview" class="w-full h-full object-cover"></div>
                <div class="p-4"><p id="prevDesc" class="text-[10px] font-bold text-slate-400 uppercase">FACEBOOK.COM</p><p id="prevTitle" class="text-sm font-bold text-slate-900 mt-1">Judul postingan Anda</p></div>
            </div>
        </div>
    </div>

    <script>
        const titleInput = document.getElementById('title');
        const descInput = document.getElementById('desc');
        const fileInput = document.getElementById('imageFile');
        const form = document.getElementById('genForm');
        const btn = document.getElementById('submitBtn');

        titleInput.addEventListener('input', () => document.getElementById('prevTitle').innerText = titleInput.value || 'Judul postingan Anda');
        descInput.addEventListener('input', () => document.getElementById('prevDesc').innerText = (descInput.value || 'FACEBOOK.COM').toUpperCase());
        fileInput.addEventListener('change', function() {
            if (this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => document.getElementById('prevImg').src = e.target.result;
                reader.readAsDataURL(this.files[0]);
            }
        });

        form.addEventListener('submit', () => {
            btn.innerText = 'Memproses...';
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
        });

        function copyToClipboard() {
            const copyText = document.getElementById("resultLink");
            const btnCopy = document.getElementById("btnCopy");
            navigator.clipboard.writeText(copyText.value).then(() => {
                btnCopy.innerText = "Copied!";
                btnCopy.classList.replace('bg-emerald-600', 'bg-blue-600');
                setTimeout(() => {
                    btnCopy.innerText = "Copy";
                    btnCopy.classList.replace('bg-blue-600', 'bg-emerald-600');
                }, 2000);
            });
        }
    </script>
</body>
</html>
