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
            
            $file = fopen($filePath, 'r');
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
            
            while (($row = fgetcsv($file, 0, ',')) !== FALSE) {
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }
                
                // Bỏ cột A (index 0), lấy từ cột B (index 1) đến AV (index 47)
                $data = array_slice($row, 1, 47);
                
                // Xử lý ngày tháng
                if (!empty($data[8])) {
                    $data[8] = $this->convertExcelDate($data[8]);
                } else {
                    $data[8] = null;
                }
                
                // Thêm thang_nam vào cuối
                $data[] = $thangNam;
                
                $stmt->execute($data);
                $insertedCount++;
            }
            
            fclose($file);
            $this->conn->commit();
            
            return ['success' => true, 'count' => $insertedCount];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function convertExcelDate($dateValue) {
        if (is_numeric($dateValue)) {
            $unixDate = ($dateValue - 25569) * 86400;
            return date('Y-m-d', $unixDate);
        }
        return date('Y-m-d', strtotime($dateValue));
    }

    public function getDataByMonthYear($thangNam, $filters = []) {
        $sql = "SELECT DISTINCT 
                    ma_khach_hang, ten_khach_hang, dia_chi_khach_hang, 
                    ma_tinh_tp, phan_loai_khach_hang, kenh,
                    SUM(tong_doanh_so) as total_doanh_so,
                    SUM(tong_san_luong) as total_san_luong
                FROM {$this->table} 
                WHERE thang_nam = :thang_nam AND ngay IS NULL";
        
        $params = [':thang_nam' => $thangNam];
        
        if (!empty($filters['ma_tinh_tp'])) {
            $sql .= " AND ma_tinh_tp = :ma_tinh_tp";
            $params[':ma_tinh_tp'] = $filters['ma_tinh_tp'];
        }
        
        if (!empty($filters['ma_khach_hang'])) {
            $sql .= " AND ma_khach_hang LIKE :ma_khach_hang";
            $params[':ma_khach_hang'] = '%' . $filters['ma_khach_hang'] . '%';
        }
        
        $sql .= " GROUP BY ma_khach_hang, ten_khach_hang, dia_chi_khach_hang, ma_tinh_tp, phan_loai_khach_hang, kenh
                  ORDER BY total_doanh_so DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerDetail($maKhachHang, $thangNam) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ma_khach_hang = :ma_khach_hang 
                AND thang_nam = :thang_nam
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
}
?>