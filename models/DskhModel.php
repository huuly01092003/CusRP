<?php
require_once 'config/database.php';

class DskhModel {
    private $conn;
    private $table = "dskh";

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
            
            $sql = "INSERT INTO {$this->table} (
                ma_kh, area, ma_gsbh, ma_npp, ma_nvbh, ten_nvbh,
                ten_kh, loai_kh, dia_chi, quan_huyen, tinh, location
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                area = VALUES(area),
                ma_gsbh = VALUES(ma_gsbh),
                ma_npp = VALUES(ma_npp),
                ma_nvbh = VALUES(ma_nvbh),
                ten_nvbh = VALUES(ten_nvbh),
                ten_kh = VALUES(ten_kh),
                loai_kh = VALUES(loai_kh),
                dia_chi = VALUES(dia_chi),
                quan_huyen = VALUES(quan_huyen),
                tinh = VALUES(tinh),
                location = VALUES(location)";
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($rows as $row) {
                if (empty($row) || count($row) < 12) {
                    continue;
                }
                
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }
                
                $data = [];
                for ($i = 0; $i < 12; $i++) {
                    $data[$i] = !empty(trim($row[$i])) ? trim($row[$i]) : null;
                }
                
                if (empty($data[0])) {
                    continue;
                }
                
                $stmt->execute($data);
                
                if ($stmt->rowCount() > 0) {
                    $insertedCount++;
                }
            }
            
            $this->conn->commit();
            
            return ['success' => true, 'inserted' => $insertedCount];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getAll($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($filters['tinh'])) {
            $sql .= " AND tinh = :tinh";
            $params[':tinh'] = $filters['tinh'];
        }
        
        if (!empty($filters['quan_huyen'])) {
            $sql .= " AND quan_huyen = :quan_huyen";
            $params[':quan_huyen'] = $filters['quan_huyen'];
        }
        
        if (!empty($filters['ma_kh'])) {
            $sql .= " AND ma_kh LIKE :ma_kh";
            $params[':ma_kh'] = '%' . $filters['ma_kh'] . '%';
        }
        
        if (!empty($filters['loai_kh'])) {
            $sql .= " AND loai_kh = :loai_kh";
            $params[':loai_kh'] = $filters['loai_kh'];
        }
        
        $sql .= " ORDER BY ma_kh";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProvinces() {
        $sql = "SELECT DISTINCT tinh FROM {$this->table} WHERE tinh IS NOT NULL ORDER BY tinh";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getDistricts($tinh = null) {
        $sql = "SELECT DISTINCT quan_huyen FROM {$this->table} WHERE quan_huyen IS NOT NULL";
        $params = [];
        
        if ($tinh) {
            $sql .= " AND tinh = :tinh";
            $params[':tinh'] = $tinh;
        }
        
        $sql .= " ORDER BY quan_huyen";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getCustomerTypes() {
        $sql = "SELECT DISTINCT loai_kh FROM {$this->table} WHERE loai_kh IS NOT NULL ORDER BY loai_kh";
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
}