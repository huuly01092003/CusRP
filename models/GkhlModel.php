<?php
require_once 'config/database.php';

class GkhlModel {
    private $conn;
    private $table = "gkhl";
    private const PAGE_SIZE = 1000; // Pagination cho import

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function importCSV($filePath) {
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
            $batchSize = 100;
            $batch = [];
            
            $sql = "INSERT INTO {$this->table} (
                ma_nvbh, ten_nvbh, ma_kh_dms, ten_quay, ten_chu_cua_hang,
                ngay_sinh, thang_sinh, nam_sinh, sdt_zalo, sdt_dinh_danh,
                khop_sdt_dinh_danh, dang_ky_chuong_trinh, dang_ky_muc_doanh_so, dang_ky_trung_bay
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                ma_nvbh = VALUES(ma_nvbh),
                ten_nvbh = VALUES(ten_nvbh),
                ten_quay = VALUES(ten_quay),
                ten_chu_cua_hang = VALUES(ten_chu_cua_hang),
                ngay_sinh = VALUES(ngay_sinh),
                thang_sinh = VALUES(thang_sinh),
                nam_sinh = VALUES(nam_sinh),
                sdt_zalo = VALUES(sdt_zalo),
                sdt_dinh_danh = VALUES(sdt_dinh_danh),
                khop_sdt_dinh_danh = VALUES(khop_sdt_dinh_danh),
                dang_ky_chuong_trinh = VALUES(dang_ky_chuong_trinh),
                dang_ky_muc_doanh_so = VALUES(dang_ky_muc_doanh_so),
                dang_ky_trung_bay = VALUES(dang_ky_trung_bay)";
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($rows as $row) {
                if (empty($row) || count($row) < 14) {
                    continue;
                }
                
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }
                
                $data = [
                    !empty(trim($row[0])) ? trim($row[0]) : null,
                    !empty(trim($row[1])) ? trim($row[1]) : null,
                    !empty(trim($row[2])) ? trim($row[2]) : null,
                    !empty(trim($row[3])) ? trim($row[3]) : null,
                    !empty(trim($row[4])) ? trim($row[4]) : null,
                    $this->cleanNumber($row[5], true),
                    $this->cleanNumber($row[6], true),
                    $this->cleanNumber($row[7]),
                    !empty(trim($row[8])) ? trim($row[8]) : null,
                    !empty(trim($row[9])) ? trim($row[9]) : null,
                    $this->convertYNToBoolean($row[10]), // Chuyển Y/N thành 1/0/null
                    !empty(trim($row[11])) ? trim($row[11]) : null,
                    !empty(trim($row[12])) ? trim($row[12]) : null,
                    !empty(trim($row[13])) ? trim($row[13]) : null
                ];
                
                if (empty($data[2])) {
                    continue;
                }
                
                $batch[] = $data;
                
                // Thực thi batch khi đạt kích thước
                if (count($batch) >= $batchSize) {
                    foreach ($batch as $batchData) {
                        $stmt->execute($batchData);
                        if ($stmt->rowCount() > 0) {
                            $insertedCount++;
                        }
                    }
                    $batch = [];
                    // Giải phóng bộ nhớ
                    gc_collect_cycles();
                }
            }
            
            // Thực thi batch cuối cùng
            if (!empty($batch)) {
                foreach ($batch as $batchData) {
                    $stmt->execute($batchData);
                    if ($stmt->rowCount() > 0) {
                        $insertedCount++;
                    }
                }
            }
            
            $this->conn->commit();
            
            return ['success' => true, 'inserted' => $insertedCount];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function convertYNToBoolean($value) {
        if (empty($value) || $value === '' || $value === 'NULL') {
            return null;
        }
        
        $cleaned = strtoupper(trim($value));
        
        if ($cleaned === 'Y' || $cleaned === 'YES' || $cleaned === '1') {
            return 1;
        }
        
        if ($cleaned === 'N' || $cleaned === 'NO' || $cleaned === '0') {
            return 0;
        }
        
        return null;
    }

    private function cleanNumber($value, $asTinyInt = false) {
        if (empty($value) || $value === '' || $value === 'NULL') {
            return null;
        }
        
        $cleaned = str_replace([',', ' ', '.'], '', trim($value));
        
        if (is_numeric($cleaned)) {
            if ($asTinyInt) {
                return (int)$cleaned;
            }
            return $cleaned;
        }
        
        return null;
    }

    public function getAll($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($filters['ma_nvbh'])) {
            $sql .= " AND ma_nvbh = :ma_nvbh";
            $params[':ma_nvbh'] = $filters['ma_nvbh'];
        }
        
        if (!empty($filters['ma_kh_dms'])) {
            $sql .= " AND ma_kh_dms LIKE :ma_kh_dms";
            $params[':ma_kh_dms'] = '%' . $filters['ma_kh_dms'] . '%';
        }
        
        if (isset($filters['khop_sdt']) && $filters['khop_sdt'] !== '') {
            $sql .= " AND khop_sdt_dinh_danh = :khop_sdt";
            $params[':khop_sdt'] = $filters['khop_sdt'];
        }
        
        if (!empty($filters['nam_sinh'])) {
            $sql .= " AND nam_sinh = :nam_sinh";
            $params[':nam_sinh'] = $filters['nam_sinh'];
        }
        
        $sql .= " ORDER BY ma_kh_dms LIMIT 5000";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        // Fetch với MODE_LAZY để tối ưu bộ nhớ
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSaleStaff() {
        $sql = "SELECT DISTINCT ma_nvbh, ten_nvbh FROM {$this->table} 
                WHERE ma_nvbh IS NOT NULL ORDER BY ma_nvbh LIMIT 1000";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBirthYears() {
        $sql = "SELECT DISTINCT nam_sinh FROM {$this->table} 
                WHERE nam_sinh IS NOT NULL ORDER BY nam_sinh DESC LIMIT 100";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getPhoneMatchCount() {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE khop_sdt_dinh_danh = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}