<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        
        /* GKHL Info Box - Same size as location */
        .gkhl-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            height: 100%;
            min-height: 200px;
        }
        .gkhl-info h6 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid rgba(255,255,255,0.3);
            padding-bottom: 10px;
        }
        .gkhl-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .gkhl-item:last-child {
            border-bottom: none;
        }
        .gkhl-label {
            font-weight: 500;
            font-size: 0.9rem;
        }
        .gkhl-value {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .gkhl-not-registered {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            color: white;
            height: 100%;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        /* Location Info Box */
        .location-info {
            background: #e7f3ff;
            padding: 20px;
            border-left: 4px solid #667eea;
            border-radius: 10px;
            height: 100%;
            min-height: 200px;
        }
        .location-info h6 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid rgba(102, 126, 234, 0.3);
            padding-bottom: 10px;
        }
        
        /* Map Container */
        #map {
            height: 400px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            margin-top: 15px;
        }
        .map-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }
        
        .status-badge-gkhl {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .status-badge-gkhl.has-gkhl {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
        .status-badge-gkhl.no-gkhl {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
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

                <!-- Location & GKHL Row -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <?php if (!empty($location)): ?>
                            <div class="location-info">
                                <h6><i class="fas fa-map-marker-alt me-2"></i>Thông tin Vị trí</h6>
                                <p class="mb-2"><strong>Location:</strong></p>
                                <p class="text-muted"><?= htmlspecialchars($location) ?></p>
                                <?php
                                    // Parse location coordinates
                                    $coords = explode(',', $location);
                                    if (count($coords) === 2) {
                                        $lat = trim($coords[0]);
                                        $lng = trim($coords[1]);
                                        echo "<p class=\"mb-0 mt-3\"><small><i class=\"fas fa-crosshairs me-1\"></i> Lat: <code>$lat</code>, Lng: <code>$lng</code></small></p>";
                                    }
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="location-info">
                                <h6><i class="fas fa-map-marker-alt me-2"></i>Thông tin Vị trí</h6>
                                <p class="text-muted text-center mt-5">
                                    <i class="fas fa-map-marked-alt fa-3x mb-3 d-block"></i>
                                    Chưa có thông tin vị trí
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <?php if (!empty($gkhlInfo)): ?>
                            <div class="gkhl-info">
                                <h6>
                                    <i class="fas fa-handshake me-2"></i>Gắn kết Hoa Linh
                                    <span class="status-badge-gkhl has-gkhl float-end">
                                        <i class="fas fa-check-circle"></i> Đã tham gia
                                    </span>
                                </h6>
                                <div class="gkhl-item">
                                    <span class="gkhl-label">📌 Tên Quầy:</span>
                                    <span class="gkhl-value"><?= htmlspecialchars($gkhlInfo['ten_quay']) ?></span>
                                </div>
                                <div class="gkhl-item">
                                    <span class="gkhl-label">📋 ĐK Chương trình:</span>
                                    <span class="gkhl-value"><?= !empty($gkhlInfo['dang_ky_chuong_trinh']) ? htmlspecialchars($gkhlInfo['dang_ky_chuong_trinh']) : 'Chưa có' ?></span>
                                </div>
                                <div class="gkhl-item">
                                    <span class="gkhl-label">💰 ĐK Mục Doanh số:</span>
                                    <span class="gkhl-value"><?= !empty($gkhlInfo['dang_ky_muc_doanh_so']) ? htmlspecialchars($gkhlInfo['dang_ky_muc_doanh_so']) : 'Chưa có' ?></span>
                                </div>
                                <div class="gkhl-item">
                                    <span class="gkhl-label">🎨 ĐK Trưng bày:</span>
                                    <span class="gkhl-value"><?= !empty($gkhlInfo['dang_ky_trung_bay']) ? htmlspecialchars($gkhlInfo['dang_ky_trung_bay']) : 'Chưa có' ?></span>
                                </div>
                                <div class="gkhl-item">
                                    <span class="gkhl-label">📱 Khớp SĐT:</span>
                                    <span class="gkhl-value">
                                        <?php if ($gkhlInfo['khop_sdt_dinh_danh'] == 1): ?>
                                            <i class="fas fa-check"></i> Đã khớp
                                        <?php elseif ($gkhlInfo['khop_sdt_dinh_danh'] == 0): ?>
                                            <i class="fas fa-times"></i> Chưa khớp
                                        <?php else: ?>
                                            Chưa rõ
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="gkhl-not-registered">
                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                <h5 class="mb-2">Chưa tham gia GKHL</h5>
                                <p class="mb-0">Khách hàng chưa đăng ký chương trình Gắn kết Hoa Linh</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Map Display -->
                <?php if (!empty($location)): ?>
                    <?php
                        $coords = explode(',', $location);
                        if (count($coords) === 2) {
                            $lat = trim($coords[0]);
                            $lng = trim($coords[1]);
                    ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="map-container">
                                <h6 class="mb-3"><i class="fas fa-map me-2"></i>Bản đồ vị trí khách hàng</h6>
                                <div id="map"></div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                <?php endif; ?>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function() {
            $('.detail-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                },
                pageLength: 50,
                order: [[1, 'desc']]
            });

            <?php if (!empty($location)): ?>
                <?php
                    $coords = explode(',', $location);
                    if (count($coords) === 2) {
                        $lat = trim($coords[0]);
                        $lng = trim($coords[1]);
                ?>
                // Initialize map
                var map = L.map('map').setView([<?= $lat ?>, <?= $lng ?>], 16);
                
                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(map);
                
                // Add marker
                var marker = L.marker([<?= $lat ?>, <?= $lng ?>]).addTo(map);
                marker.bindPopup('<b><?= htmlspecialchars($data[0]['ten_khach_hang']) ?></b><br><?= htmlspecialchars($data[0]['dia_chi_khach_hang']) ?>').openPopup();
                
                // Add circle to highlight area
                var circle = L.circle([<?= $lat ?>, <?= $lng ?>], {
                    color: '#667eea',
                    fillColor: '#667eea',
                    fillOpacity: 0.2,
                    radius: 100
                }).addTo(map);
                <?php } ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>