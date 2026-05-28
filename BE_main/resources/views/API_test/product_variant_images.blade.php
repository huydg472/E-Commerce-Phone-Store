<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Test API Product Variant Images</title>

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

        img.preview {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>

    <h2>Test API Product Variant Images bằng Blade</h2>

    <div id="message"></div>

    <div class="box">
        <h3>Form thêm / sửa ảnh biến thể</h3>

        <input type="hidden" id="image_id">

        <label>Biến thể sản phẩm</label>
        <select id="product_variant_id">
            <option value="">-- Chọn biến thể --</option>
        </select>
        <div id="error_product_variant_id" class="error"></div>

        <label>Image URL</label>
        <input type="text" id="image_url" placeholder="https://example.com/image.jpg">
        <div id="error_image_url" class="error"></div>

        <label>Alt text</label>
        <input type="text" id="alt_text" placeholder="Ví dụ: iPhone 15 màu đen 128GB">
        <div id="error_alt_text" class="error"></div>



        <button onclick="submitImage()">Lưu ảnh</button>
        <button onclick="resetForm()">Reset form</button>
    </div>

    <div class="box">
        <h3>Danh sách ảnh biến thể</h3>

        <button onclick="reloadImages()">Tải lại danh sách</button>

        <table style="margin-top: 12px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Variant ID</th>
                    <th>Ảnh</th>
                    <th>Image URL</th>
                    <th>Alt text</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody id="imageTable">
                <tr>
                    <td colspan="7">Đang tải dữ liệu...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>JSON API trả về</h3>
    <pre id="rawJson"></pre>

    <script>
        const API_URL = '/api/product-variant-images';
        const VARIANT_API_URL = '/api/product-variants';

        function showMessage(type, text) {
            document.getElementById('message').innerHTML =
                `<div class="${type}">${text}</div>`;
        }

        function clearErrors() {
            const fields = [
                'product_variant_id',
                'image_url',
                'alt_text',
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
                product_variant_id: document.getElementById('product_variant_id').value,
                image_url: document.getElementById('image_url').value,
                alt_text: document.getElementById('alt_text').value,

            };
        }

        async function loadVariantsForSelect() {
            try {
                const response = await fetch(VARIANT_API_URL, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                const variants = extractList(result);
                const select = document.getElementById('product_variant_id');

                select.innerHTML = `<option value="">-- Chọn biến thể --</option>`;

                variants.forEach(variant => {
                    select.innerHTML += `
                    <option value="${variant.id}">
                        ID: ${variant.id} - SKU: ${variant.sku ?? ''} - ${variant.color ?? ''}
                    </option>
                `;
                });

            } catch (error) {
                showMessage('danger', 'Không tải được danh sách biến thể: ' + error.message);
            }
        }

        async function loadImages() {
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

                const images = extractList(result);
                const tbody = document.getElementById('imageTable');

                if (images.length === 0) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="7">Không có ảnh biến thể nào</td>
                    </tr>
                `;
                    return;
                }

                tbody.innerHTML = images.map(image => `
                <tr>
                    <td>${image.id}</td>
                    <td>${image.product_variant_id ?? ''}</td>
                    <td>
                        ${
                            image.image_url
                                ? `<img class="preview" src="${image.image_url}" alt="${image.alt_text ?? ''}">`
                                : ''
                        }
                    </td>
                    <td>${image.image_url ?? ''}</td>
                    <td>${image.alt_text ?? ''}</td>
                    <td>${image.sort_order ?? 0}</td>
                    <td>
                        <button onclick="loadImageForEdit(${image.id})">
                            Sửa
                        </button>

                        <button onclick="deleteImage(${image.id})">
                            Xóa
                        </button>
                    </td>
                </tr>
            `).join('');

            } catch (error) {
                showMessage('danger', 'Không gọi được API ảnh biến thể: ' + error.message);
            }
        }

        async function loadImageForEdit(id) {
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
                    showMessage('danger', result.message || 'Không lấy được chi tiết ảnh.');
                    return;
                }

                fillImageForm(result.data);

            } catch (error) {
                showMessage('danger', 'Lỗi khi lấy chi tiết ảnh: ' + error.message);
            }
        }

        function fillImageForm(image) {
            document.getElementById('image_id').value = image.id ?? '';
            document.getElementById('product_variant_id').value = image.product_variant_id ?? '';
            document.getElementById('image_url').value = image.image_url ?? '';
            document.getElementById('alt_text').value = image.alt_text ?? '';
            document.getElementById('sort_order').value = image.sort_order ?? 0;

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function submitImage() {
            clearErrors();

            const imageId = document.getElementById('image_id').value;
            const isEdit = imageId !== '';

            const url = isEdit ? `${API_URL}/${imageId}` : API_URL;
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
                    isEdit ? 'Cập nhật ảnh biến thể thành công.' : 'Thêm ảnh biến thể thành công.'
                );

                resetForm();
                loadImages();

            } catch (error) {
                showMessage('danger', 'Lỗi khi gửi request: ' + error.message);
            }
        }

        async function deleteImage(id) {
            if (!confirm('Bạn có chắc muốn xóa ảnh này không?')) {
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
                    showMessage('danger', result.message || 'Xóa ảnh thất bại.');
                    return;
                }

                showMessage('success', result.message || 'Xóa ảnh thành công.');

                resetForm();
                loadImages();

            } catch (error) {
                showMessage('danger', 'Lỗi khi xóa ảnh: ' + error.message);
            }
        }

        function resetForm() {
            document.getElementById('image_id').value = '';
            document.getElementById('product_variant_id').value = '';
            document.getElementById('image_url').value = '';
            document.getElementById('alt_text').value = '';
            document.getElementById('sort_order').value = '';

            clearErrors();
        }

        function reloadImages() {
            resetForm();
            loadImages();
        }

        async function initPage() {
            await loadVariantsForSelect();
            await loadImages();
        }

        initPage();
    </script>

</body>

</html>
