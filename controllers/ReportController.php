<?php
require_once 'models/KhachHangModel.php';

class ReportController {
    private $model;

    public function __construct() {
        $this->model = new KhachHangModel();
    }

    public function index() {
        $thangNam = $_GET['thang_nam'] ?? '';
        $filters = [
            'ma_tinh_tp' => $_GET['ma_tinh_tp'] ?? '',
            'ma_khach_hang' => $_GET['ma_khach_hang'] ?? ''
        ];

        $data = [];
        $provinces = $this->model->getProvinces();
        $monthYears = $this->model->getMonthYears();

        if (!empty($thangNam)) {
            $data = $this->model->getDataByMonthYear($thangNam, $filters);
        }

        require_once 'views/report.php';
    }

    public function detail() {
        $maKhachHang = $_GET['ma_khach_hang'] ?? '';
        $thangNam = $_GET['thang_nam'] ?? '';

        if (empty($maKhachHang) || empty($thangNam)) {
            header('Location: report.php');
            exit;
        }

        $data = $this->model->getCustomerDetail($maKhachHang, $thangNam);
        require_once 'views/detail.php';
    }
}
?>