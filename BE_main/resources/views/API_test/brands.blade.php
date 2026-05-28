<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Test API Brands</title>

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

        input, textarea, select {
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

        th, td {
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

<h2>Test API Brands bằng Blade</h2>

<div id="message"></div>

<div class="box">
    <h3>Form thêm / sửa thương hiệu</h3>

    <input type="hidden" id="brand_id">

    <label>Tên thương hiệu</label>
    <input type="text" id="name" placeholder="Ví dụ: Apple, Samsung, Xiaomi">
    <div id="error_name" class="error"></div>

    <label>Slug</label>
    <input type="text" id="slug" placeholder="Ví dụ: apple, samsung">
    <div id="error_slug" class="error"></div>

    <label>Logo URL</label>
    <input type="text" id="logo_url" placeholder="Link ảnh logo nếu có">
    <div id="error_logo_url" class="error"></div>

    <label>Mô tả</label>
    <textarea id="description" rows="3" placeholder="Mô tả thương hiệu"></textarea>
    <div id="error_description" class="error"></div>

    <label>Trạng thái</label>
    <select id="status">
        <option value="active">Đang hoạt động</option>
        <option value="inactive">Ẩn</option>
    </select>
    <div id="error_status" class="error"></div>

    <button onclick="submitBrand()">Lưu thương hiệu</button>
    <button onclick="resetForm()">Reset form</button>
</div>

<div class="box">
    <h3>Danh sách thương hiệu</h3>

    <button onclick="loadBrands()">Tải lại danh sách</button>

    <table style="margin-top: 12px;">
        <thead>
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Slug</th>
            <th>Logo</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr>
        </thead>

        <tbody id="brandTable">
        <tr>
            <td colspan="6">Đang tải dữ liệu...</td>
        </tr>
        </tbody>
    </table>
</div>

<h3>JSON API trả về</h3>
<pre id="rawJson"></pre>

<script>
    const API_URL = '/api/brands';

    function showMessage(type, text) {
        document.getElementById('message').innerHTML =
            `<div class="${type}">${text}</div>`;
    }

    function clearErrors() {
        const fields = [
            'name',
            'slug',
            'logo_url',
            'description',
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

    function getFormData() {
        return {
            name: document.getElementById('name').value,
            slug: document.getElementById('slug').value,
            logo_url: document.getElementById('logo_url').value,
            description: document.getElementById('description').value,
            status: document.getElementById('status').value
        };
    }

    async function loadBrands() {
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

            const brands = result.data || [];
            const tbody = document.getElementById('brandTable');

            if (brands.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6">Không có thương hiệu nào</td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = brands.map(brand => `
                <tr>
                    <td>${brand.id}</td>
                    <td>${brand.name ?? ''}</td>
                    <td>${brand.slug ?? ''}</td>
                    <td>
                        ${
                            brand.logo_url
                                ? `<img src="${brand.logo_url}" alt="logo" style="height: 40px;">`
                                : ''
                        }
                    </td>
                    <td>${brand.status ?? ''}</td>
                    <td>
                        <button onclick="loadBrandForEdit(${brand.id})">Sửa</button>
                       
<button onclick="toggleBrandStatus(${brand.id})">
    ${brand.status === 'active' ? 'Ẩn' : 'Hiện'}
</button>
                        <button onclick="deleteBrand(${brand.id})">Xóa</button>
                    </td>
                </tr>
            `).join('');

        } catch (error) {
            showMessage('danger', 'Không gọi được API brands: ' + error.message);
        }
    }

    async function loadBrandForEdit(id) {
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

            const brand = result.data;

            document.getElementById('brand_id').value = brand.id ?? '';
            document.getElementById('name').value = brand.name ?? '';
            document.getElementById('slug').value = brand.slug ?? '';
            document.getElementById('logo_url').value = brand.logo_url ?? '';
            document.getElementById('description').value = brand.description ?? '';
            document.getElementById('status').value = brand.status ?? 'active';

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

        } catch (error) {
            showMessage('danger', 'Không lấy được chi tiết thương hiệu: ' + error.message);
        }
    }

    async function submitBrand() {
        clearErrors();

        const brandId = document.getElementById('brand_id').value;
        const isEdit = brandId !== '';

        const url = isEdit ? `${API_URL}/${brandId}` : API_URL;
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

            showMessage('success', isEdit ? 'Cập nhật thương hiệu thành công.' : 'Thêm thương hiệu thành công.');

            resetForm();
            loadBrands();

        } catch (error) {
            showMessage('danger', 'Lỗi khi gửi request: ' + error.message);
        }
    }

    async function toggleBrandStatus(id) {
    if (!confirm('Bạn có chắc muốn đổi trạng thái thương hiệu này không?')) {
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

        loadBrands();

    } catch (error) {
        showMessage('danger', 'Lỗi khi cập nhật trạng thái: ' + error.message);
    }
}

    async function deleteBrand(id) {
        if (!confirm('Bạn có chắc muốn xóa thương hiệu này không?')) {
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
                showMessage('danger', result.message || 'Xóa thương hiệu thất bại.');
                return;
            }

            showMessage('success', 'Xóa thương hiệu thành công.');
            loadBrands();

        } catch (error) {
            showMessage('danger', 'Lỗi khi xóa thương hiệu: ' + error.message);
        }
    }

    function resetForm() {
        document.getElementById('brand_id').value = '';
        document.getElementById('name').value = '';
        document.getElementById('slug').value = '';
        document.getElementById('logo_url').value = '';
        document.getElementById('description').value = '';
        document.getElementById('status').value = 'active';

        clearErrors();
    }

    loadBrands();
</script>

</body>
</html>