<?php
require_once 'config/database.php';

class KhachHangModel {
    private $conn;
    private $table = "khachhang_baocao";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function importCSV($filePath, $thangNam) {
        try {
            $this->conn->beginTransaction();
            
            // Đọc file với encoding UTF-8
            $fileContent = file_get_contents($filePath);
            
            // Chuyển encoding nếu cần
            if (!mb_check_encoding($fileContent, 'UTF-8')) {
                $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'auto');
            }
            
            // Parse CSV
            $rows = array_map(function($line) {
                return str_getcsv($line, ',', '"');
            }, explode("\n", $fileContent));
            
            $isFirstRow = true;
            $insertedCount = 0;
            
            // SQL prepared statement
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
                // Bỏ qua dòng trống
                if (empty($row) || count($row) < 48) {
                    continue;
                }
                
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }
                
                // Bỏ cột A (index 0), lấy từ cột B (index 1) đến AV (index 47)
                $data = array_slice($row, 1, 47);
                
                // Xử lý từng cột
                // Cột 0-7: Text fields (ma_don_vi -> so_dien_thoai)
                for ($i = 0; $i < 8; $i++) {
                    $data[$i] = !empty(trim($data[$i])) ? trim($data[$i]) : null;
                }
                
                // Cột 8: ngay (index 8 trong $data)
                if (!empty(trim($data[8]))) {
                    $data[8] = $this->convertDate($data[8]);
                } else {
                    $data[8] = null;
                }
                
                // Cột 9-11: Text fields (ma_san_pham, ten_san_pham, don_vi_co_ban)
                for ($i = 9; $i <= 11; $i++) {
                    $data[$i] = !empty(trim($data[$i])) ? trim($data[$i]) : null;
                }
                
                // Cột 12-27: Numeric fields (các trường số)
                for ($i = 12; $i <= 27; $i++) {
                    $data[$i] = $this->cleanNumber($data[$i]);
                }
                
                // Cột 28-46: Text fields (loai_san_pham -> ma_tinh_tp)
                for ($i = 28; $i <= 46; $i++) {
                    $data[$i] = !empty(trim($data[$i])) ? trim($data[$i]) : null;
                }
                
                // Thêm thang_nam vào cuối
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
        if (empty($value) || $value === '' || $value === 'NULL') {
            return null;
        }
        
        // Loại bỏ dấu phẩy, khoảng trắng
        $cleaned = str_replace([',', ' ', '.'], '', trim($value));
        
        // Kiểm tra nếu là số
        if (is_numeric($cleaned)) {
            return $cleaned;
        }
        
        return null;
    }

    private function convertDate($dateValue) {
        if (empty($dateValue) || $dateValue === 'NULL') {
            return null;
        }
        
        $dateValue = trim($dateValue);
        
        // Nếu là số (Excel serial date)
        if (is_numeric($dateValue)) {
            $unixDate = ($dateValue - 25569) * 86400;
            return date('Y-m-d', $unixDate);
        }
        
        // Nếu là định dạng dd/mm/yyyy
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateValue, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        // Nếu là định dạng yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
            return $dateValue;
        }
        
        // Thử parse với strtotime
        $timestamp = strtotime($dateValue);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return null;
    }

    // ✅ FIXED: Sửa lại logic lọc GKHL
    public function getDataByMonthYear($thangNam, $filters = []) {
        // Xây dựng query cơ bản với subquery để xác định has_gkhl
        $sql = "SELECT 
                    kh.ma_khach_hang, 
                    kh.ten_khach_hang, 
                    kh.dia_chi_khach_hang, 
                    kh.ma_tinh_tp, 
                    kh.phan_loai_khach_hang, 
                    kh.kenh,
                    SUM(kh.tong_doanh_so_sau_ck) as total_doanh_so,
                    SUM(kh.tong_san_luong) as total_san_luong,
                    MAX(CASE WHEN g.ma_kh_dms IS NOT NULL THEN 1 ELSE 0 END) as has_gkhl
                FROM {$this->table} kh
                LEFT JOIN gkhl g ON kh.ma_khach_hang = g.ma_kh_dms
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
        
        // GROUP BY trước
        $sql .= " GROUP BY kh.ma_khach_hang, kh.ten_khach_hang, kh.dia_chi_khach_hang, 
                  kh.ma_tinh_tp, kh.phan_loai_khach_hang, kh.kenh";
        
        // ✅ FIXED: Lọc GKHL sau khi GROUP BY bằng HAVING
        if (isset($filters['gkhl_status']) && $filters['gkhl_status'] !== '') {
            if ($filters['gkhl_status'] === '1') {
                // Chỉ lấy khách hàng đã tham gia GKHL
                $sql .= " HAVING has_gkhl = 1";
            } elseif ($filters['gkhl_status'] === '0') {
                // Chỉ lấy khách hàng chưa tham gia GKHL
                $sql .= " HAVING has_gkhl = 0";
            }
        }
        
        $sql .= " ORDER BY total_doanh_so DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $sql = "SELECT DISTINCT ma_tinh_tp FROM {$this->table} WHERE ma_tinh_tp IS NOT NULL AND ma_tinh_tp != '' ORDER BY ma_tinh_tp";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getMonthYears() {
        $sql = "SELECT DISTINCT thang_nam FROM {$this->table} ORDER BY thang_nam DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Lấy thông tin Location từ bảng dskh
    public function getCustomerLocation($maKhachHang) {
        $sql = "SELECT location FROM dskh WHERE ma_kh = :ma_kh LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':ma_kh' => $maKhachHang]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['location'] ?? null;
    }

    // Lấy thông tin gắn kết hoa linh
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
}
?>