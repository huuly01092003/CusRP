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
        'ma_khach_hang' => $_GET['ma_khach_hang'] ?? '',
        'gkhl_status' => $_GET['gkhl_status'] ?? ''  // ✅ THÊM gkhl_status
    ];

    $data = [];
    $summary = [
        'total_khach_hang' => 0,
        'total_doanh_so' => 0,
        'total_san_luong' => 0,
        'total_gkhl' => 0
    ];

    $provinces = $this->model->getProvinces();
    $monthYears = $this->model->getMonthYears();

    if (!empty($thangNam)) {
        $data = $this->model->getDataByMonthYear($thangNam, $filters); // chỉ 100 dòng cho table
        $summary = $this->model->getSummaryStats($thangNam, $filters); // toàn bộ dữ liệu cho dashboard
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
        $location = $this->model->getCustomerLocation($maKhachHang);
        $gkhlInfo = $this->model->getGkhlInfo($maKhachHang);
        
        require_once 'views/detail.php';
    }
}
?>