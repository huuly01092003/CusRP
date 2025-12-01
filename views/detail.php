<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 25px;
        }
        .data-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .badge-null {
            background: #dc3545;
        }
        .badge-not-null {
            background: #28a745;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .nav-tabs .nav-link {
            border: 1px solid #dee2e6;
            margin-right: 5px;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-user me-2"></i>Chi tiết Khách hàng
            </span>
            <a href="report.php?thang_nam=<?= urlencode($thangNam) ?>" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if (!empty($data)): ?>
            <div class="info-card">
                <div class="row">
                    <div class="col-md-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Thông tin khách hàng</h5>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">Mã KH:</th>
                                <td><strong><?= htmlspecialchars($data[0]['ma_khach_hang']) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Tên KH:</th>
                                <td><?= htmlspecialchars($data[0]['ten_khach_hang']) ?></td>
                            </tr>
                            <tr>
                                <th>Địa chỉ:</th>
                                <td><?= htmlspecialchars($data[0]['dia_chi_khach_hang']) ?></td>
                            </tr>
                            <tr>
                                <th>Điện thoại:</th>
                                <td><?= htmlspecialchars($data[0]['so_dien_thoai']) ?></td>
                            </tr>
                            <tr>
                                <th>Mã số thuế:</th>
                                <td><?= htmlspecialchars($data[0]['ma_so_thue']) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-building me-2"></i>Thông tin đơn vị</h5>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">Mã đơn vị:</th>
                                <td><?= htmlspecialchars($data[0]['ma_don_vi']) ?></td>
                            </tr>
                            <tr>
                                <th>Tên đơn vị:</th>
                                <td><?= htmlspecialchars($data[0]['ten_don_vi']) ?></td>
                            </tr>
                            <tr>
                                <th>Mã NV:</th>
                                <td><?= htmlspecialchars($data[0]['ma_nhan_vien']) ?></td>
                            </tr>
                            <tr>
                                <th>Tên NV:</th>
                                <td><?= htmlspecialchars($data[0]['ten_nhan_vien']) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-tags me-2"></i>Phân loại</h5>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">Phân loại:</th>
                                <td><span class="badge bg-info"><?= htmlspecialchars($data[0]['phan_loai_khach_hang']) ?></span></td>
                            </tr>
                            <tr>
                                <th>Kênh:</th>
                                <td><span class="badge bg-success"><?= htmlspecialchars($data[0]['kenh']) ?></span></td>
                            </tr>
                            <tr>
                                <th>Tỉnh/TP:</th>
                                <td><?= htmlspecialchars($data[0]['ma_tinh_tp']) ?></td>
                            </tr>
                            <tr>
                                <th>Tháng/Năm:</th>
                                <td><strong><?= htmlspecialchars($data[0]['thang_nam']) ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="data-card">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#all">
                                <i class="fas fa-list me-2"></i>Tất cả giao dịch (<?= count($data) ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#null-date">
                                <i class="fas fa-calendar-times me-2"></i>Ngày = NULL 
                                (<?= count(array_filter($data, fn($d) => empty($d['ngay']))) ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#not-null-date">
                                <i class="fas fa-calendar-check me-2"></i>Ngày ≠ NULL 
                                (<?= count(array_filter($data, fn($d) => !empty($d['ngay']))) ?>)
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div id="all" class="tab-pane fade show active">
                            <?php renderTable($data); ?>
                        </div>
                        <div id="null-date" class="tab-pane fade">
                            <?php renderTable(array_filter($data, fn($d) => empty($d['ngay']))); ?>
                        </div>
                        <div id="not-null-date" class="tab-pane fade">
                            <?php renderTable(array_filter($data, fn($d) => !empty($d['ngay']))); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>Không tìm thấy dữ liệu cho khách hàng này.
            </div>
        <?php endif; ?>
    </div>

    <?php
    function renderTable($data) {
        if (empty($data)) {
            echo '<div class="alert alert-info">Không có dữ liệu</div>';
            return;
        }
    ?>
        <div class="table-responsive">
            <table class="table table-hover table-sm detail-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Ngày</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Đơn vị</th>
                        <th class="text-end">Sản lượng</th>
                        <th class="text-end">Doanh số</th>
                        <th class="text-end">Chiết khấu</th>
                        <th class="text-end">DS sau CK</th>
                        <th>Loại SP</th>
                        <th>Ngành hàng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_values($data) as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <?php if (empty($row['ngay'])): ?>
                                    <span class="badge badge-null">NULL</span>
                                <?php else: ?>
                                    <span class="badge badge-not-null"><?= date('d/m/Y', strtotime($row['ngay'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['ma_san_pham']) ?></td>
                            <td><?= htmlspecialchars($row['ten_san_pham']) ?></td>
                            <td><?= htmlspecialchars($row['don_vi_co_ban']) ?></td>
                            <td class="text-end"><?= number_format($row['tong_san_luong'], 0) ?></td>
                            <td class="text-end"><?= number_format($row['tong_doanh_so'], 0) ?></td>
                            <td class="text-end"><?= number_format($row['tong_chiet_khau'], 0) ?></td>
                            <td class="text-end"><strong><?= number_format($row['tong_doanh_so_sau_ck'], 0) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['loai_san_pham']) ?></span></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($row['nganh_hang']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.detail-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                },
                pageLength: 50,
                order: [[1, 'desc']]
            });
        });
    </script>
</body>
</html>