<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .filter-card, .data-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 25px;
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
    </style>
</head>
<body>
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="fas fa-users me-2"></i>Danh sách Khách hàng</span>
            <a href="dskh.php" class="btn btn-light">
                <i class="fas fa-upload me-2"></i>Import
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="filter-card">
            <h5><i class="fas fa-filter me-2"></i>Bộ lọc</h5>
            <form method="GET" action="dskh.php">
                <input type="hidden" name="action" value="list">
                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <select name="tinh" class="form-select">
                            <option value="">-- Tất cả tỉnh --</option>
                            <?php foreach ($provinces as $p): ?>
                                <option value="<?= $p ?>" <?= $filters['tinh'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="quan_huyen" class="form-select">
                            <option value="">-- Tất cả quận/huyện --</option>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?= $d ?>" <?= $filters['quan_huyen'] === $d ? 'selected' : '' ?>><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="loai_kh" class="form-select">
                            <option value="">-- Loại KH --</option>
                            <?php foreach ($customerTypes as $t): ?>
                                <option value="<?= $t ?>" <?= $filters['loai_kh'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="ma_kh" class="form-control" placeholder="Mã KH" value="<?= $filters['ma_kh'] ?>">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="stat-box mb-4">
            <h2><?= number_format($totalCount) ?></h2>
            <p class="mb-0">Tổng số khách hàng</p>
        </div>

        <div class="data-card">
            <table id="dskhTable" class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã KH</th>
                        <th>Tên KH</th>
                        <th>Loại</th>
                        <th>Địa chỉ</th>
                        <th>Quận/Huyện</th>
                        <th>Tỉnh</th>
                        <th>NVBH</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= $row['ma_kh'] ?></strong></td>
                            <td><?= $row['ten_kh'] ?></td>
                            <td><span class="badge bg-info"><?= $row['loai_kh'] ?></span></td>
                            <td><?= $row['dia_chi'] ?></td>
                            <td><?= $row['quan_huyen'] ?></td>
                            <td><?= $row['tinh'] ?></td>
                            <td><?= $row['ten_nvbh'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $('#dskhTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json' },
            pageLength: 50
        });
    </script>
</body>
</html>