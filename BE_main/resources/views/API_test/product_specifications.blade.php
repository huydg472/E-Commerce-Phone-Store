<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Test API Product Specifications</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 24px;
            background: #f5f5f5;
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

    <h2>Test API Product Specifications bằng Blade</h2>

    <div id="message"></div>

    <div class="box">
        <h3>Form thêm / sửa thông số sản phẩm</h3>

        <input type="hidden" id="specification_id">

        <label>Sản phẩm</label>
        <select id="product_id">
            <option value="">-- Chọn sản phẩm --</option>
        </select>
        <div id="error_product_id" class="error"></div>

        <label>Tên thông số</label>
        <input type="text" id="spec_name" placeholder="Ví dụ: Màn hình, Chip, RAM, Pin">
        <div id="error_spec_name" class="error"></div>

        <label>Giá trị thông số</label>
        <input type="text" id="spec_value" placeholder="Ví dụ: OLED 6.1 inch, Apple A16, 8GB">
        <div id="error_spec_value" class="error"></div>

        <button onclick="submitSpecification()">Lưu thông số</button>
        <button onclick="resetForm()">Reset form</button>
    </div>

    <div class="box">
        <h3>Danh sách thông số sản phẩm</h3>

        <button onclick="reloadSpecifications()">Tải lại danh sách</button>

        <table style="margin-top: 12px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product ID</th>
                    <th>Tên thông số</th>
                    <th>Giá trị</th>
                    <th>Sort order</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody id="specificationTable">
                <tr>
                    <td colspan="6">Đang tải dữ liệu...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>JSON API trả về</h3>
    <pre id="rawJson"></pre>

    <script>
        const API_URL = '/api/product-specifications';
        const PRODUCT_API_URL = '/api/products';

        function showMessage(type, text) {
            document.getElementById('message').innerHTML =
                `<div class="${type}">${text}</div>`;
        }

        function clearErrors() {
            const fields = [
                'product_id',
                'spec_name',
                'spec_value',
                'sort_order'
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
                spec_name: document.getElementById('spec_name').value,
                spec_value: document.getElementById('spec_value').value
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
                        ID: ${product.id} - ${product.name ?? ''}
                    </option>
                `;
                });

            } catch (error) {
                showMessage('danger', 'Không tải được danh sách sản phẩm: ' + error.message);
            }
        }

        async function loadSpecifications() {
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

                const specifications = extractList(result);
                const tbody = document.getElementById('specificationTable');

                if (specifications.length === 0) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="6">Không có thông số nào</td>
                    </tr>
                `;
                    return;
                }

                tbody.innerHTML = specifications.map(spec => `
                <tr>
                    <td>${spec.id}</td>
                    <td>${spec.product_id ?? ''}</td>
                    <td>${spec.spec_name ?? ''}</td>
                    <td>${spec.spec_value ?? ''}</td>
                    <td>${spec.sort_order ?? 0}</td>
                    <td>
                        <button onclick="loadSpecificationForEdit(${spec.id})">
                            Sửa
                        </button>

                        <button onclick="deleteSpecification(${spec.id})">
                            Xóa
                        </button>
                    </td>
                </tr>
            `).join('');

            } catch (error) {
                showMessage('danger', 'Không gọi được API thông số sản phẩm: ' + error.message);
            }
        }

        async function loadSpecificationForEdit(id) {
            try {
                const response = await fetch(`${API_URL}/${id}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                document.getElementById('rawJson').innerText =
                    JSON.stringify(result, null, 2);

                if (!response.ok) {
                    showMessage('danger', result.message || 'Không lấy được chi tiết thông số.');
                    return;
                }

                fillSpecificationForm(result.data);

            } catch (error) {
                showMessage('danger', 'Lỗi khi lấy chi tiết thông số: ' + error.message);
            }
        }

        function fillSpecificationForm(spec) {
            document.getElementById('specification_id').value = spec.id ?? '';
            document.getElementById('product_id').value = spec.product_id ?? '';
            document.getElementById('spec_name').value = spec.spec_name ?? '';
            document.getElementById('spec_value').value = spec.spec_value ?? '';

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function submitSpecification() {
            clearErrors();

            const specificationId = document.getElementById('specification_id').value;
            const isEdit = specificationId !== '';

            const url = isEdit ? `${API_URL}/${specificationId}` : API_URL;
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
                    isEdit ? 'Cập nhật thông số thành công.' : 'Thêm thông số thành công.'
                );

                resetForm();
                loadSpecifications();

            } catch (error) {
                showMessage('danger', 'Lỗi khi gửi request: ' + error.message);
            }
        }

        async function deleteSpecification(id) {
            if (!confirm('Bạn có chắc muốn xóa thông số này không?')) {
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
                    showMessage('danger', result.message || 'Xóa thông số thất bại.');
                    return;
                }

                showMessage('success', result.message || 'Xóa thông số thành công.');

                resetForm();
                loadSpecifications();

            } catch (error) {
                showMessage('danger', 'Lỗi khi xóa thông số: ' + error.message);
            }
        }

        function resetForm() {
            document.getElementById('specification_id').value = '';
            document.getElementById('product_id').value = '';
            document.getElementById('spec_name').value = '';
            document.getElementById('spec_value').value = '';

            clearErrors();
        }

        function reloadSpecifications() {
            resetForm();
            loadSpecifications();
        }

        async function initPage() {
            await loadProductsForSelect();
            await loadSpecifications();
        }

        initPage();
    </script>

</body>

</html>
