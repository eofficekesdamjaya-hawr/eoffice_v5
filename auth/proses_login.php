<?php
require_once __DIR__.'/../config/session.php';

// Aktifkan koneksi ke database kamu
require_once '../config/koneksi.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role_target = isset($_POST['role_target']) ? $_POST['role_target'] : 'admin';

    // =========================================================================
    // SKENARIO 1: LOGIN ADMIN, PEJABAT, & PIMPINAN (Tanpa Cek Database)
    // =========================================================================
    
    $admin_accounts = [
        'superadmin@gmail.com'        => ['role' => 'superadmin', 'nama' => 'Super Admin'],
        'setum@gmail.com'             => ['role' => 'setum', 'nama' => 'Sekretariat Umum'],
        'admin@gmail.com'             => ['role' => 'admin', 'nama' => 'Administrator IT'],
        'kasituud2026@gmail.com'      => ['role' => 'kasi_tuud', 'nama' => 'Kasi TUUD'],
        'kakesdamjaya2026@gmail.com'  => ['role' => 'kakesdam_jaya', 'nama' => 'Kakesdam Jaya'],
        'wakakesdamjaya2026@gmail.com' => ['role' => 'wakakesdam_jaya', 'nama' => 'Wakakesdam Jaya'],
        'spripimpinan2026@gmail.com'  => ['role' => 'spri_pimpinan', 'nama' => 'Spri Pimpinan']
    ];

    if (array_key_exists($email, $admin_accounts)) {
        session_unset();
        $_SESSION['id_user']    = $email;
        $_SESSION['nama_user']  = $admin_accounts[$email]['nama'];
        $_SESSION['email']      = $email;
        $_SESSION['tipe_akses'] = $admin_accounts[$email]['role']; 
        $_SESSION['status']     = "login";

        header("Location: ../dashboard/dashboard_admin.php");
        exit;
    }

    // =========================================================================
    // SKENARIO 2: LOGIN RUANGAN, JAJARAN, & SATUAN LAIN (Query dari Database)
    // =========================================================================
    
    switch ($role_target) {
        
        case 'ruangan':
            // Menggunakan query inner join sesuai kode asli kamu
            $stmt = $conn->prepare("SELECT u.id, u.nama, u.email, u.password, u.status, r.role_key, r.tipe_akses FROM users u INNER JOIN master_role r ON u.id_role = r.id_role WHERE u.email = ? AND r.tipe_akses = 'ruangan' LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            // Verifikasi Password (Jika menggunakan password_hash di DB, ganti dengan password_verify)
            if ($user && $password === $user['password']) {
                if ($user['status'] == 'tidak_aktif') {
                    $_SESSION['error'] = "Akun belum aktif.";
                    header("Location: login_ruangan.php");
                    exit;
                }

                session_unset();
                $_SESSION['id_user']    = $user['id'];
                $_SESSION['nama_user']  = $user['nama'];
                $_SESSION['tipe_akses'] = 'ruangan';
                $_SESSION['status']     = "login";
                
                header("Location: ../ruangan/dashboard.php");
                exit;
            } else {
                $_SESSION['error'] = "Email atau Password Ruangan salah.";
                header("Location: login_ruangan.php");
                exit;
            }
            break;

        case 'jajaran':
            // Menggunakan query tables users sesuai kode asli kamu
            $stmt = $conn->prepare("SELECT id, nama, password, status FROM users WHERE email = ? AND role = ? LIMIT 1");
            $stmt->bind_param("ss", $email, $role_target);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && $password === $user['password']) {
                if ($user['status'] == 'tidak_aktif') {
                    header("Location: login_jajaran.php?error=not_active");
                    exit;
                }

                session_unset();
                $_SESSION['id_user']    = $user['id'];
                $_SESSION['nama_user']  = $user['nama'] ?? 'Satuan Jajaran';
                $_SESSION['tipe_akses'] = 'jajaran';
                $_SESSION['status']     = "login";
                
                header("Location: ../jajaran/dashboard.php");
                exit;
            } else {
                header("Location: login_jajaran.php?error=wrong_password");
                exit;
            }
            break;

        case 'satuan_lain':
            // Menggunakan query tables users sesuai kode asli kamu
            $stmt = $conn->prepare("SELECT id, nama, password, status FROM users WHERE email = ? AND role = ? LIMIT 1");
            $stmt->bind_param("ss", $email, $role_target);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && $password === $user['password']) {
                if ($user['status'] == 'tidak_aktif') {
                    header("Location: login_satuan_lain.php?error=not_active");
                    exit;
                }

                session_unset();
                $_SESSION['id_user']    = $user['id'];
                $_SESSION['nama_user']  = $user['nama'] ?? 'Satuan Luar';
                $_SESSION['tipe_akses'] = 'satuan_lain';
                $_SESSION['status']     = "login";
                
                header("Location: ../satuan_lain/dashboard_satuan_lain.php");
                exit;
            } else {
                header("Location: login_satuan_lain.php?error=wrong_password");
                exit;
            }
            break;

        default:
            header("Location: ../dashboard_utama.php");
            exit;
    }

} else {
    header("Location: ../dashboard_utama.php");
    exit;
}
