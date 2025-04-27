<?php

// Check if product data exists
if (!isset($product) || !is_array($product) || empty($product)) {
    // Display message if no data
    echo "<p class='text-muted'>Không có dữ liệu thông số kỹ thuật.</p>";
    return;
}

// List of specs to show
$specsToShow = [
    'brand' => 'Thương hiệu',
    'screen_size' => 'Kích thước màn hình',
    'screen_tech' => 'Công nghệ màn hình',
    'cpu' => 'CPU',
    'ram' => 'RAM',
    'storage' => 'Bộ nhớ',
    'rear_camera' => 'Camera sau',
    'front_camera' => 'Camera trước',
    'battery_capacity' => 'Dung lượng pin',
    'os' => 'Hệ điều hành',
    'dimensions' => 'Kích thước',
    'weight' => 'Trọng lượng',
];

// Array to store valid specs
$validSpecs = [];
foreach ($specsToShow as $key => $label) {
    // Check if spec exists and is not empty
    if (isset($product[$key]) && $product[$key] !== null && $product[$key] !== '') {
        $validSpecs[$key] = [
            'label' => $label,
            'value' => htmlspecialchars($product[$key]) // Convert HTML entities for security
        ];
    }
}

?>

<?php if (!empty($validSpecs)): ?>
<div class="table-responsive">
    <table class="table table-sm table-striped table-bordered specs-table mb-0">
        <tbody>
          <!-- show specs -->
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