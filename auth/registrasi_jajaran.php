<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Jajaran - E-Office Kesdam Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #e9ecef; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .reg-box { background: white; width: 100%; max-width: 500px; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .logo-img { width: 80px; margin-bottom: 15px; }
        .btn-register { background-color: #1a5928; color: white; border: none; }
        .btn-register:hover { background-color: #14451f; color: white; }
    </style>
</head>
<body>

<div class="reg-box text-center">
    <img src="../assets/img/logo1.png" alt="Logo Kesdam" class="logo-img">
    <h4 class="fw-bold mb-1">Registrasi Akun Jajaran</h4>
    <p class="text-muted small mb-4">Silahkan lengkapi data untuk akses pengiriman surat</p>

    <form action="proses_registrasi.php" method="POST" class="text-start">
        <div class="mb-3">
            <label class="form-label small fw-bold">Nama Satuan / Jajaran</label>
            <input type="text" name="nama_satuan" class="form-control" placeholder="Contoh: Rumkit Tk. II Moh. Ridwan Meuraksa" required>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Email Resmi</label>
            <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold">Konfirmasi Password</label>
                <input type="password" name="konfirmasi_password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Nomor WhatsApp Personel</label>
            <input type="tel" name="no_hp" class="form-control" placeholder="0812xxxx" required>
        </div>
        
        <button type="submit" class="btn btn-register w-100 py-2 mb-3">Daftar Sekarang</button>
        
        <div class="text-center">
            <p class="small text-muted">Sudah punya akun? <a href="login_jajaran.php" class="text-primary text-decoration-none">Login di sini</a></p>
        </div>
    </form>

    <div class="mt-4 pt-3 border-top">
        <p class="text-muted" style="font-size: 0.75rem;">Copyright@IT Kesdam Jaya/Jayakarta 2026</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>