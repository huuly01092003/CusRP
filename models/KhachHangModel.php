<?php
require_once 'config/database.php';

class KhachHangModel {
    private $conn;
    private $table = "khachhang_baocao";
    private const PAGE_SIZE = 100; // Phân trang

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ✅ FIXED: Tối ưu query với phân trang
public function getDataByMonthYear($thangNam, $filters = [], $page = 1) {
    $page = max(1, (int)$page);
    $offset = ($page - 1) * self::PAGE_SIZE;

    $sql = "
        SELECT 
            kh.ma_khach_hang,
            kh.ten_khach_hang,
            kh.dia_chi_khach_hang,
            kh.ma_tinh_tp,
            kh.phan_loai_khach_hang,
            kh.kenh,
            kh.tong_doanh_so_sau_ck AS total_doanh_so,
            kh.tong_san_luong AS total_san_luong,
            (CASE WHEN EXISTS (SELECT 1 FROM gkhl g WHERE g.ma_kh_dms = kh.ma_khach_hang) THEN 1 ELSE 0 END) AS has_gkhl
        FROM {$this->table} kh
        WHERE kh.thang_nam = :thang_nam
        AND kh.ngay IS NULL
    ";

    $params = [':thang_nam' => $thangNam];

    if (!empty($filters['ma_tinh_tp'])) {
        $sql .= " AND kh.ma_tinh_tp = :ma_tinh_tp";
        $params[':ma_tinh_tp'] = $filters['ma_tinh_tp'];
    }

    if (!empty($filters['ma_khach_hang'])) {
        $sql .= " AND kh.ma_khach_hang LIKE :ma_khach_hang";
        $params[':ma_khach_hang'] = '%' . $filters['ma_khach_hang'] . '%';
    }

    if (isset($filters['gkhl_status']) && $filters['gkhl_status'] !== '') {
        if ($filters['gkhl_status'] == '1') {
            $sql .= " AND EXISTS (SELECT 1 FROM gkhl g WHERE g.ma_kh_dms = kh.ma_khach_hang)";
        } else {
            $sql .= " AND NOT EXISTS (SELECT 1 FROM gkhl g WHERE g.ma_kh_dms = kh.ma_khach_hang)";
        }
    }

    $sql .= " ORDER BY kh.tong_doanh_so_sau_ck DESC LIMIT :limit OFFSET :offset";

    $stmt = $this->conn->prepare($sql);

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }

    $stmt->bindValue(':limit', self::PAGE_SIZE, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    // ✅ MỚI: Đếm tổng số bản ghi theo filter
    public function getFilteredCount($thangNam, $filters = []) {
        $sql = "SELECT COUNT(DISTINCT kh.ma_khach_hang) as total
                FROM {$this->table} kh
                WHERE kh.thang_nam = :thang_nam 
                AND kh.ngay IS NULL";
        
        $params = [':thang_nam' => $thangNam];
        
        if (!empty($filters['ma_tinh_tp'])) {
            $sql .= " AND kh.ma_tinh_tp = :ma_tinh_tp";
            $params[':ma_tinh_tp'] = $filters['ma_tinh_tp'];
        }
        
        if (!empty($filters['ma_khach_hang'])) {
            $sql .= " AND kh.ma_khach_hang LIKE :ma_khach_hang";
            $params[':ma_khach_hang'] = '%' . $filters['ma_khach_hang'] . '%';
        }
        
        // Xử lý filter GKHL
        if (isset($filters['gkhl_status']) && $filters['gkhl_status'] !== '') {
            if ($filters['gkhl_status'] === '1') {
                $sql .= " AND EXISTS (SELECT 1 FROM gkhl g WHERE g.ma_kh_dms = kh.ma_khach_hang)";
            } elseif ($filters['gkhl_status'] === '0') {
                $sql .= " AND NOT EXISTS (SELECT 1 FROM gkhl g WHERE g.ma_kh_dms = kh.ma_khach_hang)";
            }
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // ✅ MỚI: Lấy tổng số thống kê cho dashboard
// Lấy tổng số thống kê cho dashboard (không bị giới hạn 100 dòng)
public function getSummaryStats($thangNam, $filters = []) {
    $sql = "SELECT 
                COUNT(DISTINCT kh.ma_khach_hang) as total_khach_hang,
                SUM(kh.tong_doanh_so_sau_ck) as total_doanh_so,
                SUM(kh.tong_san_luong) as total_san_luong,
                SUM(CASE WHEN g.ma_kh_dms IS NOT NULL THEN 1 ELSE 0 END) as total_gkhl
            FROM {$this->table} kh
            LEFT JOIN gkhl g ON g.ma_kh_dms = kh.ma_khach_hang
            WHERE kh.thang_nam = :thang_nam 
              AND kh.ngay IS NULL";

    $params = [':thang_nam' => $thangNam];

    if (!empty($filters['ma_tinh_tp'])) {
        $sql .= " AND kh.ma_tinh_tp = :ma_tinh_tp";
        $params[':ma_tinh_tp'] = $filters['ma_tinh_tp'];
    }

    if (!empty($filters['ma_khach_hang'])) {
        $sql .= " AND kh.ma_khach_hang LIKE :ma_khach_hang";
        $params[':ma_khach_hang'] = '%' . $filters['ma_khach_hang'] . '%';
    }

    if (isset($filters['gkhl_status']) && $filters['gkhl_status'] !== '') {
        if ($filters['gkhl_status'] === '1') {
            $sql .= " AND g.ma_kh_dms IS NOT NULL";
        } elseif ($filters['gkhl_status'] === '0') {
            $sql .= " AND g.ma_kh_dms IS NULL";
        }
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



    public function getCustomerDetail($maKhachHang, $thangNam) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ma_khach_hang = :ma_khach_hang 
                AND thang_nam = :thang_nam
                AND NOT (tong_san_luong = 0 OR tong_doanh_so = 0)
                AND tong_doanh_so > 0
                ORDER BY ngay IS NULL, ngay DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':ma_khach_hang' => $maKhachHang,
            ':thang_nam' => $thangNam
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProvinces() {
        $sql = "SELECT DISTINCT ma_tinh_tp FROM {$this->table} 
                WHERE ma_tinh_tp IS NOT NULL AND ma_tinh_tp != '' 
                ORDER BY ma_tinh_tp";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getMonthYears() {
        $sql = "SELECT DISTINCT thang_nam FROM {$this->table} 
                ORDER BY thang_nam DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getCustomerLocation($maKhachHang) {
        $sql = "SELECT location FROM dskh WHERE ma_kh = :ma_kh LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':ma_kh' => $maKhachHang]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['location'] ?? null;
    }

    public function getGkhlInfo($maKhachHang) {
        $sql = "SELECT 
                    ma_kh_dms, 
                    ten_quay,
                    dang_ky_chuong_trinh, 
                    dang_ky_muc_doanh_so, 
                    dang_ky_trung_bay,
                    khop_sdt_dinh_danh
                FROM gkhl 
                WHERE ma_kh_dms = :ma_kh_dms 
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':ma_kh_dms' => $maKhachHang]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Các phương thức import giữ nguyên...
    public function importCSV($filePath, $thangNam) {
        try {
            $this->conn->beginTransaction();
            
            $fileContent = file_get_contents($filePath);
            if (!mb_check_encoding($fileContent, 'UTF-8')) {
                $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'auto');
            }
            
            $rows = array_map(function($line) {
                return str_getcsv($line, ',', '"');
            }, explode("\n", $fileContent));
            
            $isFirstRow = true;
            $insertedCount = 0;
            
            $sql = "INSERT INTO {$this->table} (
                ma_don_vi, ten_don_vi, ma_nhan_vien, ten_nhan_vien, 
                ma_khach_hang, ten_khach_hang, dia_chi_khach_hang, so_dien_thoai,
                ngay, ma_san_pham, ten_san_pham, don_vi_co_ban,
                tong_san_luong, tong_doanh_so, tong_chiet_khau, tong_doanh_so_sau_ck,
                san_luong_trong_tuyen, doanh_so_trong_tuyen, chiet_khau_trong_tuyen, doanh_so_trong_tuyen_sau_ck,
                tong_san_luong_hoan_thanh, tong_doanh_so_hoan_thanh, tong_chiet_khau_hoan_thanh, tong_doanh_so_hoan_thanh_sau_ck,
                san_luong_hoan_thanh_trong_tuyen, doanh_so_hoan_thanh_trong_tuyen, chiet_khau_hoan_thanh_trong_tuyen, doanh_so_hoan_thanh_trong_tuyen_sau_ck,
                loai_san_pham, nganh_hang, nhan_hang, qua_tang, ban_diem,
                phan_loai_khach_hang, kenh, mien_bac_phan_loai, gan_ket_hoa_linh,
                nhom_sieu_thi, nhan_hang_mau, hong_ma, mo_moi, nhom_ngoc_chau,
                ma_so_thue, ma_khach_hang_tham_chieu, ma_phuong_xa, ma_quan_huyen, ma_tinh_tp,
                thang_nam
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($rows as $row) {
                if (empty($row) || count($row) < 48) continue;
                
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }
                
                $data = array_slice($row, 1, 47);
                
                for ($i = 0; $i < 8; $i++) {
                    $data[$i] = !empty(trim($data[$i])) ? trim($data[$i]) : null;
                }
                
                if (!empty(trim($data[8]))) {
                    $data[8] = $this->convertDate($data[8]);
                } else {
                    $data[8] = null;
                }
                
                for ($i = 9; $i <= 11; $i++) {
                    $data[$i] = !empty(trim($data[$i])) ? trim($data[$i]) : null;
                }
                
                for ($i = 12; $i <= 27; $i++) {
                    $data[$i] = $this->cleanNumber($data[$i]);
                }
                
                for ($i = 28; $i <= 46; $i++) {
                    $data[$i] = !empty(trim($data[$i])) ? trim($data[$i]) : null;
                }
                
                $data[] = $thangNam;
                
                $stmt->execute($data);
                $insertedCount++;
            }
            
            $this->conn->commit();
            
            return ['success' => true, 'count' => $insertedCount];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function cleanNumber($value) {
        if (empty($value) || $value === '' || $value === 'NULL') return null;
        $cleaned = str_replace([',', ' ', '.'], '', trim($value));
        return is_numeric($cleaned) ? $cleaned : null;
    }

    private function convertDate($dateValue) {
        if (empty($dateValue) || $dateValue === 'NULL') return null;
        
        $dateValue = trim($dateValue);
        
        if (is_numeric($dateValue)) {
            $unixDate = ($dateValue - 25569) * 86400;
            return date('Y-m-d', $unixDate);
        }
        
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateValue, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
            return $dateValue;
        }
        
        $timestamp = strtotime($dateValue);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return null;
    }
}
?>