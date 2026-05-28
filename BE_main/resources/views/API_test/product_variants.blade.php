<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Test API Product Variants</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 24px;
            background: #f5f5f5;
        }

        h2 {
            margin-bottom: 16px;
        }

        .box {
            background: white;
            padding: 16px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        button {
            padding: 8px 12px;
            margin-right: 6px;
            cursor: pointer;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        th {
            background: #eee;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .success {
            color: green;
            margin-bottom: 12px;
        }

        .danger {
            color: red;
            margin-bottom: 12px;
        }

        pre {
            background: #222;
            color: white;
            padding: 12px;
            overflow-x: auto;
        }
    </style>
</head>

<body>

    <h2>Test API Product Variants bằng Blade</h2>

    <div id="message"></div>

    <div class="box">
        <h3>Form thêm / sửa biến thể sản phẩm</h3>

        <input type="hidden" id="variant_id">

        <label>Sản phẩm</label>
        <select id="product_id">
            <option value="">-- Chọn sản phẩm --</option>
        </select>
        <div id="error_product_id" class="error"></div>

        <label>Màu sắc</label>
        <input type="text" id="color" placeholder="Ví dụ: Đen, Trắng, Xanh">
        <div id="error_color" class="error"></div>

        <label>Bộ nhớ</label>
        <input type="text" id="storage" placeholder="Ví dụ: 128GB, 256GB">
        <div id="error_storage" class="error"></div>

        <label>RAM</label>
        <input type="text" id="ram" placeholder="Ví dụ: 6GB, 8GB">
        <div id="error_ram" class="error"></div>

        <label>SKU</label>
        <input type="text" id="sku" placeholder="Ví dụ: IP15-BLACK-128">
        <div id="error_sku" class="error"></div>

        <label>Giá nhập</label>
        <input type="number" id="import_price" placeholder="Ví dụ: 15000000">
        <div id="error_import_price" class="error"></div>

        <label>Giá bán</label>
        <input type="number" id="price" placeholder="Ví dụ: 20000000">
        <div id="error_price" class="error"></div>

        <label>Giá khuyến mãi</label>
        <input type="number" id="sale_price" placeholder="Có thể để trống">
        <div id="error_sale_price" class="error"></div>

        <label>Số lượng tồn kho</label>
        <input type="number" id="quantity" placeholder="Ví dụ: 50">
        <div id="error_quantity" class="error"></div>

        <label>Trạng thái</label>
        <select id="status">
            <option value="active">Đang bán</option>
            <option value="inactive">Ẩn</option>
        </select>
        <div id="error_status" class="error"></div>

        <button onclick="submitVariant()">Lưu biến thể</button>
        <button onclick="resetForm()">Reset form</button>
    </div>

    <div class="box">
        <h3>Danh sách biến thể sản phẩm</h3>

        <button onclick="reloadVariants()">Tải lại danh sách</button>

        <table style="margin-top: 12px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product ID</th>
                    <th>Màu</th>
                    <th>Bộ nhớ</th>
                    <th>RAM</th>
                    <th>SKU</th>
                    <th>Giá nhập</th>
                    <th>Giá bán</th>
                    <th>Giá KM</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody id="variantTable">
                <tr>
                    <td colspan="12">Đang tải dữ liệu...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>JSON API trả về</h3>
    <pre id="rawJson"></pre>

    <script>
        const API_URL = '/api/product-variants';
        const PRODUCT_API_URL = '/api/products';
        const TEST_PAGE_URL = '/api-test/product-variants';
        const INITIAL_SKU = @json($initialSku ?? null);

        function showMessage(type, text) {
            document.getElementById('message').innerHTML =
                `<div class="${type}">${text}</div>`;
        }

        function clearErrors() {
            const fields = [
                'product_id',
                'color',
                'storage',
                'ram',
                'sku',
                'import_price',
                'price',
                'sale_price',
                'quantity',
                'status'
            ];

            fields.forEach(field => {
                const box = document.getElementById('error_' + field);
                if (box) {
                    box.innerText = '';
                }
            });
        }

        function showValidationErrors(errors) {
            clearErrors();

            Object.keys(errors).forEach(field => {
                const box = document.getElementById('error_' + field);
                if (box) {
                    box.innerText = errors[field][0];
                }
            });
        }

        function resetVariantUrl() {
            history.replaceState({}, '', TEST_PAGE_URL);
        }

        function extractList(result) {
            if (Array.isArray(result.data)) {
                return result.data;
            }

            if (result.data && Array.isArray(result.data.data)) {
                return result.data.data;
            }

            return [];
        }

        function getFormData() {
            return {
                product_id: document.getElementById('product_id').value,
                color: document.getElementById('color').value,
                storage: document.getElementById('storage').value,
                ram: document.getElementById('ram').value,
                sku: document.getElementById('sku').value,
                import_price: document.getElementById('import_price').value,
                price: document.getElementById('price').value,
                sale_price: document.getElementById('sale_price').value || null,
                quantity: document.getElementById('quantity').value,
                status: document.getElementById('status').value
            };
        }

        async function loadProductsForSelect() {
            try {
                const response = await fetch(PRODUCT_API_URL, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                const products = extractList(result);
                const select = document.getElementById('product_id');

                select.innerHTML = `<option value="">-- Chọn sản phẩm --</option>`;

                products.forEach(product => {
                    select.innerHTML += `
                    <option value="${product.id}">
                        ${product.name ?? ''} - ID: ${product.id}
                    </option>
                `;
                });

            } catch (error) {
                showMessage('danger', 'Không tải được danh sách sản phẩm: ' + error.message);
            }
        }

        async function loadVariants() {
            try {
                const response = await fetch(API_URL, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                document.getElementById('rawJson').innerText =
                    JSON.stringify(result, null, 2);

                const variants = extractList(result);
                const tbody = document.getElementById('variantTable');

                if (variants.length === 0) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="12">Không có biến thể nào</td>
                    </tr>
                `;
                    return;
                }

                tbody.innerHTML = variants.map(variant => `
                <tr>
                    <td>${variant.id}</td>
                    <td>${variant.product_id ?? ''}</td>
                    <td>${variant.color ?? ''}</td>
                    <td>${variant.storage ?? ''}</td>
                    <td>${variant.ram ?? ''}</td>
                    <td>${variant.sku ?? ''}</td>
                    <td>${Number(variant.import_price ?? 0).toLocaleString('vi-VN')}</td>
                    <td>${Number(variant.price ?? 0).toLocaleString('vi-VN')}</td>
                    <td>${variant.sale_price ? Number(variant.sale_price).toLocaleString('vi-VN') : ''}</td>
                    <td>${variant.quantity ?? 0}</td>
                    <td>${variant.status ?? ''}</td>
                    <td>
                        <button onclick="openVariantBySku('${encodeURIComponent(variant.sku ?? '')}')">
                            Sửa
                        </button>

                        <button onclick="toggleVariantStatus(${variant.id})">
                            ${variant.status === 'active' ? 'Ẩn' : 'Hiện'}
                        </button>

                        <button onclick="deleteVariant(${variant.id})">
                            Xóa
                        </button>
                    </td>
                </tr>
            `).join('');

            } catch (error) {
                showMessage('danger', 'Không gọi được API product variants: ' + error.message);
            }
        }

        async function openVariantBySku(encodedSku) {
            const sku = decodeURIComponent(encodedSku);

            if (!sku) {
                showMessage('danger', 'Biến thể này chưa có SKU.');
                return;
            }

            history.pushState({}, '', `${TEST_PAGE_URL}/${encodeURIComponent(sku)}`);

            await loadVariantBySku(sku);
        }

        async function loadVariantBySku(sku) {
            try {
                const response = await fetch(`${API_URL}/by-sku/${encodeURIComponent(sku)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                document.getElementById('rawJson').innerText =
                    JSON.stringify(result, null, 2);

                if (!response.ok) {
                    showMessage('danger', result.message || 'Không lấy được biến thể theo SKU.');
                    return;
                }

                fillVariantForm(result.data);

            } catch (error) {
                showMessage('danger', 'Lỗi khi lấy biến thể theo SKU: ' + error.message);
            }
        }

        function fillVariantForm(variant) {
            document.getElementById('variant_id').value = variant.id ?? '';
            document.getElementById('product_id').value = variant.product_id ?? '';
            document.getElementById('color').value = variant.color ?? '';
            document.getElementById('storage').value = variant.storage ?? '';
            document.getElementById('ram').value = variant.ram ?? '';
            document.getElementById('sku').value = variant.sku ?? '';
            document.getElementById('import_price').value = variant.import_price ?? '';
            document.getElementById('price').value = variant.price ?? '';
            document.getElementById('sale_price').value = variant.sale_price ?? '';
            document.getElementById('quantity').value = variant.quantity ?? '';
            document.getElementById('status').value = variant.status ?? 'active';

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function submitVariant() {
            clearErrors();

            const variantId = document.getElementById('variant_id').value;
            const isEdit = variantId !== '';

            const url = isEdit ? `${API_URL}/${variantId}` : API_URL;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(getFormData())
                });

                const result = await response.json();

                document.getElementById('rawJson').innerText =
                    JSON.stringify(result, null, 2);

                if (response.status === 422) {
                    showMessage('danger', 'Dữ liệu không hợp lệ. Kiểm tra lỗi dưới form.');
                    showValidationErrors(result.errors || {});
                    return;
                }

                if (!response.ok) {
                    showMessage('danger', result.message || 'Có lỗi xảy ra.');
                    return;
                }

                showMessage(
                    'success',
                    isEdit ? 'Cập nhật biến thể thành công.' : 'Thêm biến thể thành công.'
                );

                resetForm();
                loadVariants();

            } catch (error) {
                showMessage('danger', 'Lỗi khi gửi request: ' + error.message);
            }
        }

        async function toggleVariantStatus(id) {
            if (!confirm('Bạn có chắc muốn đổi trạng thái biến thể này không?')) {
                return;
            }

            try {
                const response = await fetch(`${API_URL}/${id}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                document.getElementById('rawJson').innerText =
                    JSON.stringify(result, null, 2);

                if (!response.ok) {
                    showMessage('danger', result.message || 'Cập nhật trạng thái thất bại.');
                    return;
                }

                showMessage('success', result.message || 'Cập nhật trạng thái thành công.');

                loadVariants();

            } catch (error) {
                showMessage('danger', 'Lỗi khi cập nhật trạng thái: ' + error.message);
            }
        }

        async function deleteVariant(id) {
            if (!confirm('Bạn có chắc muốn xóa biến thể này không?')) {
                return;
            }

            try {
                const response = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                document.getElementById('rawJson').innerText =
                    JSON.stringify(result, null, 2);

                if (!response.ok) {
                    showMessage('danger', result.message || 'Xóa biến thể thất bại.');
                    return;
                }

                showMessage('success', 'Xóa biến thể thành công.');

                resetForm();
                loadVariants();

            } catch (error) {
                showMessage('danger', 'Lỗi khi xóa biến thể: ' + error.message);
            }
        }

        function resetForm() {
            document.getElementById('variant_id').value = '';
            document.getElementById('product_id').value = '';
            document.getElementById('color').value = '';
            document.getElementById('storage').value = '';
            document.getElementById('ram').value = '';
            document.getElementById('sku').value = '';
            document.getElementById('import_price').value = '';
            document.getElementById('price').value = '';
            document.getElementById('sale_price').value = '';
            document.getElementById('quantity').value = '';
            document.getElementById('status').value = 'active';

            clearErrors();
            resetVariantUrl();
        }

        function reloadVariants() {
            resetForm();
            loadVariants();
        }

        async function initPage() {
            await loadProductsForSelect();
            await loadVariants();

            if (INITIAL_SKU) {
                await loadVariantBySku(INITIAL_SKU);
            }
        }

        initPage();
    </script>

</body>

</html>
