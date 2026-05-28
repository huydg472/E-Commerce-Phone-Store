<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Test API Categories</title>

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

    <h2>Test API Categories bằng Blade</h2>

    <div id="message"></div>

    <div class="box">
        <h3>Form thêm / sửa danh mục</h3>

        <input type="hidden" id="category_id">

        <label>Tên danh mục</label>
        <input type="text" id="name" placeholder="Ví dụ: Điện thoại, Laptop, Phụ kiện">
        <div id="error_name" class="error"></div>

        <label>Slug</label>
        <input type="text" id="slug" placeholder="Ví dụ: dien-thoai, laptop">
        <div id="error_slug" class="error"></div>

        <label>Mô tả</label>
        <textarea id="description" rows="3" placeholder="Mô tả danh mục"></textarea>
        <div id="error_description" class="error"></div>

        <label>Trạng thái</label>
        <select id="status">
            <option value="active">Đang hoạt động</option>
            <option value="inactive">Ẩn</option>
        </select>
        <div id="error_status" class="error"></div>

        <button onclick="submitCategory()">Lưu danh mục</button>
        <button onclick="resetForm()">Reset form</button>
    </div>

    <div class="box">
        <h3>Danh sách danh mục</h3>

        <button onclick="loadCategories()">Tải lại danh sách</button>

        <table style="margin-top: 12px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Slug</th>
                    <th>Mô tả</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody id="categoryTable">
                <tr>
                    <td colspan="6">Đang tải dữ liệu...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>JSON API trả về</h3>
    <pre id="rawJson"></pre>

    <script>
        const API_URL = '/api/categories';
        const TEST_PAGE_URL = '/api-test/categories';
        const INITIAL_SLUG = @json($initialSlug);

        function showMessage(type, text) {
            document.getElementById('message').innerHTML =
                `<div class="${type}">${text}</div>`;
        }

        function fillCategoryForm(category) {
            document.getElementById('category_id').value = category.id ?? '';
            document.getElementById('name').value = category.name ?? '';
            document.getElementById('slug').value = category.slug ?? '';
            document.getElementById('description').value = category.description ?? '';
            document.getElementById('status').value = category.status ?? 'active';

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        async function loadCategoryBySlug(slug) {
            try {
                const response = await fetch(`${API_URL}/by-slug/${encodeURIComponent(slug)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                document.getElementById('rawJson').innerText =
                    JSON.stringify(result, null, 2);

                if (!response.ok) {
                    showMessage('danger', result.message || 'Không lấy được danh mục theo slug.');
                    return;
                }

                const category = result.data;

                fillCategoryForm(category);

            } catch (error) {
                showMessage('danger', 'Lỗi khi lấy danh mục theo slug: ' + error.message);
            }
        }

        function clearErrors() {
            const fields = ['name', 'slug', 'description', 'status'];

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
                description: document.getElementById('description').value,
                status: document.getElementById('status').value
            };
        }

        async function loadCategories() {
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

                const categories = result.data || [];
                const tbody = document.getElementById('categoryTable');

                if (categories.length === 0) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="6">Không có danh mục nào</td>
                    </tr>
                `;
                    return;
                }

                tbody.innerHTML = categories.map(category => `
                <tr>
                    <td>${category.id}</td>
                    <td>${category.name ?? ''}</td>
                    <td>${category.slug ?? ''}</td>
                    <td>${category.description ?? ''}</td>
                    <td>${category.status ?? ''}</td>
                    <td>
                      <button onclick="openCategoryBySlug('${encodeURIComponent(category.slug)}')">
    Sửa
</button>

                        <button onclick="toggleCategoryStatus(${category.id})">
                            ${category.status === 'active' ? 'Ẩn' : 'Hiện'}
                        </button>

                        <button onclick="deleteCategory(${category.id})">Xóa</button>
                    </td>
                </tr>
            `).join('');

            } catch (error) {
                showMessage('danger', 'Không gọi được API categories: ' + error.message);
            }
        }
        async function openCategoryBySlug(encodedSlug) {
            const slug = decodeURIComponent(encodedSlug);

            history.pushState({}, '', `${TEST_PAGE_URL}/${encodeURIComponent(slug)}`);

            await loadCategoryBySlug(slug);
        }
        async function loadCategoryForEdit(id) {
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

                const category = result.data;

                document.getElementById('category_id').value = category.id ?? '';
                document.getElementById('name').value = category.name ?? '';
                document.getElementById('slug').value = category.slug ?? '';
                document.getElementById('description').value = category.description ?? '';
                document.getElementById('status').value = category.status ?? 'active';

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            } catch (error) {
                showMessage('danger', 'Không lấy được chi tiết danh mục: ' + error.message);
            }
        }

        async function submitCategory() {
            clearErrors();

            const categoryId = document.getElementById('category_id').value;
            const isEdit = categoryId !== '';

            const url = isEdit ? `${API_URL}/${categoryId}` : API_URL;
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
                    isEdit ? 'Cập nhật danh mục thành công.' : 'Thêm danh mục thành công.'
                );

                resetForm();
                loadCategories();

            } catch (error) {
                showMessage('danger', 'Lỗi khi gửi request: ' + error.message);
            }
        }

        async function toggleCategoryStatus(id) {
            if (!confirm('Bạn có chắc muốn đổi trạng thái danh mục này không?')) {
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

                loadCategories();

            } catch (error) {
                showMessage('danger', 'Lỗi khi cập nhật trạng thái: ' + error.message);
            }
        }

        async function deleteCategory(id) {
            if (!confirm('Bạn có chắc muốn xóa danh mục này không?')) {
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
                    showMessage('danger', result.message || 'Xóa danh mục thất bại.');
                    return;
                }

                showMessage('success', 'Xóa danh mục thành công.');

                resetForm();
                resetCategoryUrl();
                loadCategories();

            } catch (error) {
                showMessage('danger', 'Lỗi khi xóa danh mục: ' + error.message);
            }
        }

        function resetForm() {
            document.getElementById('category_id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('slug').value = '';
            document.getElementById('description').value = '';
            document.getElementById('status').value = 'active';

            clearErrors();

            resetCategoryUrl();
        }

        loadCategories();

        function resetCategoryUrl() {
            history.replaceState({}, '', TEST_PAGE_URL);
        }
        if (INITIAL_SLUG) {
            loadCategoryBySlug(INITIAL_SLUG);
        }
    </script>

</body>

</html>
