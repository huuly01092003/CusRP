<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo Khách hàng</title>
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
        .filter-card {
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
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-detail {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-chart-line me-2"></i>Hệ thống Báo cáo Khách hàng
            </span>
            <a href="index.php" class="btn btn-light">
                <i class="fas fa-upload me-2"></i>Import Dữ liệu
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="filter-card">
            <h5 class="mb-4"><i class="fas fa-filter me-2"></i>Bộ lọc dữ liệu</h5>
            <form method="GET" action="report.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tháng/Năm</label>
                        <select name="thang_nam" class="form-select" required>
                            <option value="">-- Chọn tháng/năm --</option>
                            <?php foreach ($monthYears as $my): ?>
                                <option value="<?= $my ?>" <?= ($thangNam === $my) ? 'selected' : '' ?>>
                                    <?= $my ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tỉnh/Thành phố</label>
                        <select name="ma_tinh_tp" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <?php foreach ($provinces as $province): ?>
                                <option value="<?= $province ?>" <?= ($filters['ma_tinh_tp'] === $province) ? 'selected' : '' ?>>
                                    <?= $province ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Mã khách hàng</label>
                        <input type="text" name="ma_khach_hang" class="form-control" 
                               placeholder="Nhập mã KH..." value="<?= htmlspecialchars($filters['ma_khach_hang']) ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!empty($data)): ?>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-box">
                        <h2><?= number_format(count($data)) ?></h2>
                        <p class="mb-0">Tổng số khách hàng</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <h2><?= number_format(array_sum(array_column($data, 'total_doanh_so')), 0) ?></h2>
                        <p class="mb-0">Tổng doanh số</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <h2><?= number_format(array_sum(array_column($data, 'total_san_luong')), 0) ?></h2>
                        <p class="mb-0">Tổng sản lượng</p>
                    </div>
                </div>
            </div>

            <div class="data-card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-users me-2"></i>Danh sách khách hàng (Ngày = NULL)
                    </h5>
                    <div class="table-responsive">
                        <table id="customerTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Mã KH</th>
                                    <th>Tên khách hàng</th>
                                    <th>Địa chỉ</th>
                                    <th>Tỉnh/TP</th>
                                    <th>Phân loại</th>
                                    <th>Kênh</th>
                                    <th class="text-end">Doanh số</th>
                                    <th class="text-end">Sản lượng</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($row['ma_khach_hang']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['ten_khach_hang']) ?></td>
                                        <td><?= htmlspecialchars($row['dia_chi_khach_hang']) ?></td>
                                        <td><?= htmlspecialchars($row['ma_tinh_tp']) ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($row['phan_loai_khach_hang']) ?></span></td>
                                        <td><span class="badge bg-success"><?= htmlspecialchars($row['kenh']) ?></span></td>
                                        <td class="text-end"><?= number_format($row['total_doanh_so'], 0) ?></td>
                                        <td class="text-end"><?= number_format($row['total_san_luong'], 2) ?></td>
                                        <td class="text-center">
                                            <a href="report.php?action=detail&ma_khach_hang=<?= urlencode($row['ma_khach_hang']) ?>&thang_nam=<?= urlencode($thangNam) ?>" 
                                               class="btn btn-detail btn-sm">
                                                <i class="fas fa-eye me-1"></i>Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif (!empty($thangNam)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-2"></i>Không tìm thấy dữ liệu phù hợp với bộ lọc.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Vui lòng chọn tháng/năm để xem báo cáo.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#customerTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                },
                pageLength: 25,
                order: [[7, 'desc']]
            });
        });
    </script>
</body>
</html>