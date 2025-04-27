<?php
// webfinal/app/Views/partials/product_specs_table.php
// Biến $product được truyền từ product_detail.php

if (!isset($product) || !is_array($product) || empty($product)) {
    echo "<p class='text-muted'>Không có dữ liệu thông số kỹ thuật.</p>";
    return; // Dừng nếu không có dữ liệu sản phẩm
}

// Danh sách các thông số muốn hiển thị và nhãn tương ứng (CÓ THỂ TÙY CHỈNH)
$specsToShow = [
    'brand' => 'Thương hiệu', // Thêm brand nếu muốn
    'screen_size' => 'Kích thước màn hình',
    'screen_tech' => 'Công nghệ màn hình',
    'cpu' => 'CPU',
    'ram' => 'RAM',
    'storage' => 'Bộ nhớ trong',
    'rear_camera' => 'Camera sau',
    'front_camera' => 'Camera trước',
    'battery_capacity' => 'Dung lượng pin',
    'os' => 'Hệ điều hành',
    'dimensions' => 'Kích thước',
    'weight' => 'Trọng lượng',
    // Thêm các thông số khác từ bảng products nếu cần
];

// Lọc ra các thông số có giá trị từ $product
$validSpecs = [];
foreach ($specsToShow as $key => $label) {
    if (isset($product[$key]) && $product[$key] !== null && $product[$key] !== '') {
        $validSpecs[$key] = [
            'label' => $label,
            'value' => htmlspecialchars($product[$key]) // Chuyển đổi HTML entities ở đây
        ];
    }
}

?>

<?php if (!empty($validSpecs)): ?>
<div class="table-responsive">
    <table class="table table-sm table-striped table-bordered specs-table mb-0"> <?php // Added mb-0 ?>
        <tbody>
            <?php foreach ($validSpecs as $specData): ?>
                <tr>
                    <th scope="row"><?= htmlspecialchars($specData['label']) ?></th>
                    <td><?= $specData['value'] // Đã được escape ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <p class="text-muted">Thông tin thông số kỹ thuật chi tiết chưa được cập nhật cho sản phẩm này.</p>
<?php endif; ?>
