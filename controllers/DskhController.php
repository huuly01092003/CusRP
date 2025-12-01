<?php
require_once 'models/DskhModel.php';

class DskhController {
    private $model;

    public function __construct() {
        $this->model = new DskhModel();
    }

    public function showImportForm() {
        require_once 'views/dskh/import.php';
    }

    public function handleUpload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: dskh.php');
            exit;
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Vui lòng chọn file CSV';
            header('Location: dskh.php');
            exit;
        }

        $file = $_FILES['csv_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($ext !== 'csv') {
            $_SESSION['error'] = 'Chỉ chấp nhận file CSV';
            header('Location: dskh.php');
            exit;
        }

        $result = $this->model->importCSV($file['tmp_name']);
        
        if ($result['success']) {
            $_SESSION['success'] = "Import thành công {$result['inserted']} bản ghi vào DSKH";
        } else {
            $_SESSION['error'] = "Import thất bại: {$result['error']}";
        }

        header('Location: dskh.php');
        exit;
    }

    public function showList() {
        $filters = [
            'tinh' => $_GET['tinh'] ?? '',
            'quan_huyen' => $_GET['quan_huyen'] ?? '',
            'ma_kh' => $_GET['ma_kh'] ?? '',
            'loai_kh' => $_GET['loai_kh'] ?? ''
        ];

        $data = $this->model->getAll($filters);
        $provinces = $this->model->getProvinces();
        $districts = $this->model->getDistricts();
        $customerTypes = $this->model->getCustomerTypes();
        $totalCount = $this->model->getTotalCount();

        require_once 'views/dskh/list.php';
    }
}