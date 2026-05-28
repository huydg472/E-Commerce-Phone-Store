<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Test API Products</title>

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

        img.thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
    </style>
</head>

<body>

    <h2>Test API Products bằng Blade</h2>

    <div id="message"></div>

    <div class="box">
        <h3>Form thêm / sửa sản phẩm</h3>

        <input type="hidden" id="product_id">

        <label>Brand</label>
        <select id="brand_id">
            <option value="">-- Chọn thương hiệu --</option>
        </select>
        <div id="error_brand_id" class="error"></div>

        <label>Category</label>
        <select id="category_id">
            <option value="">-- Chọn danh mục --</option>
        </select>
        <div id="error_category_id" class="error"></div>

        <label>Tên sản phẩm</label>
        <input type="text" id="name" placeholder="Ví dụ: iPhone 15">
        <div id="error_name" class="error"></div>

        <label>Slug</label>
        <input type="text" id="slug" placeholder="Ví dụ: iphone-15">
        <div id="error_slug" class="error"></div>

        <label>Thumbnail URL</label>
        <input type="text" id="thumbnail_url" placeholder="Link ảnh sản phẩm">
        <div id="error_thumbnail_url" class="error"></div>

        <label>Mô tả ngắn</label>
        <textarea id="short_description" rows="2"></textarea>
        <div id="error_short_description" class="error"></div>

        <label>Mô tả chi tiết</label>
        <textarea id="description" rows="4"></textarea>
        <div id="error_description" class="error"></div>

        <label>Nổi bật</label>
        <select id="is_featured">
            <option value="0">Không</option>
            <option value="1">Có</option>
        </select>
        <div id="error_is_featured" class="error"></div>

        <label>Trạng thái</label>
        <select id="status">
            <option value="active">Đang bán</option>
            <option value="inactive">Ẩn</option>
        </select>
        <div id="error_status" class="error"></div>

        <button onclick="submitProduct()">Lưu sản phẩm</button>
        <button onclick="resetForm()">Reset form</button>
    </div>

    <div class="box">
        <h3>Danh sách sản phẩm</h3>

        <button onclick="reloadProducts()">Tải lại danh sách</button>

        <table style="margin-top: 12px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tên</th>
                    <th>Slug</th>
                    <th>Brand ID</th>
                    <th>Category ID</th>
                    <th>Nổi bật</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody id="productTable">
                <tr>
                    <td colspan="9">Đang tải dữ liệu...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>JSON API trả về</h3>
    <pre id="rawJson"></pre>

    <script>
        const API_URL = '/api/products';
        const BRAND_API_URL = '/api/brands';
        const CATEGORY_API_URL = '/api/categories';
        const TEST_PAGE_URL = '/api-test/products';
        const INITIAL_SLUG = @json($initialSlug ?? null);

        function showMessage(type, text) {
            document.getElementById('message').innerHTML =
                `<div class="${type}">${text}</div>`;
        }

        function clearErrors() {
            const fields = [
                'brand_id',
                'category_id',
                'name',
                'slug',
                'thumbnail_url',
                'short_description',
                'description',
                'is_featured',
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

        function resetProductUrl() {
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
                brand_id: document.getElementById('brand_id').value,
                category_id: document.getElementById('category_id').value,
                name: document.getElementById('name').value,
                slug: document.getElementById('slug').value,
                thumbnail_url: document.getElementById('thumbnail_url').value,
                short_description: document.getElementById('short_description').value,
                description: document.getElementById('description').value,
                is_featured: document.getElementById('is_featured').value === '1',
                status: document.getElementById('status').value
            };
        }

        async function loadBrandsForSelect() {
            try {
                const response = await fetch(BRAND_API_URL, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                const brands = extractList(result);
                const select = document.getElementById('brand_id');

                select.innerHTML = `<option value="">-- Chọn thương hiệu --</option>`;

                brands.forEach(brand => {
                    select.innerHTML += `
                    <option value="${brand.id}">
                        ${brand.name ?? ''}
                    </option>
                `;
                });

            } catch (error) {
                showMessage('danger', 'Không tải được danh sách brands: ' + error.message);
            }
        }

        async function loadCategoriesForSelect() {
            try {
                const response = await fetch(CATEGORY_API_URL, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                const categories = extractList(result);
                const select = document.getElementById('category_id');

                select.innerHTML = `<option value="">-- Chọn danh mục --</option>`;

                categories.forEach(category => {
                    select.innerHTML += `
                    <option value="${category.id}">
                        ${category.name ?? ''}
                    </option>
                `;
                });

            } catch (error) {
                showMessage('danger', 'Không tải được danh sách categories: ' + error.message);
            }
        }

        async function loadProducts() {
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

                const products = extractList(result);
                const tbody = document.getElementById('productTable');

                if (products.length === 0) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="9">Không có sản phẩm nào</td>
                    </tr>
                `;
                    return;
                }

                tbody.innerHTML = products.map(product => `
                <tr>
                    <td>${product.id}</td>
                    <td>
                        ${
                            product.thumbnail_url
                                ? `<img class="thumb" src="${product.thumbnail_url}" alt="thumbnail">`
                                : ''
                        }
                    </td>
                    <td>${product.name ?? ''}</td>
                    <td>${product.slug ?? ''}</td>
                    <td>${product.brand_id ?? ''}</td>
                    <td>${product.category_id ?? ''}</td>
                    <td>${product.is_featured ? 'Có' : 'Không'}</td>
                    <td>${product.status ?? ''}</td>
                    <td>
                        <button onclick="openProductBySlug('${encodeURIComponent(product.slug ?? '')}')">
                            Sửa
                        </button>

                        <button onclick="toggleProductStatus(${product.id})">
                            ${product.status === 'active' ? 'Ẩn' : 'Hiện'}
                        </button>

                        <button onclick="deleteProduct(${product.id})">
                            Xóa
                        </button>
                    </td>
                </tr>
            `).join('');

            } catch (error) {
                showMessage('danger', 'Không gọi được API products: ' + error.message);
            }
        }

        async function openProductBySlug(encodedSlug) {
            const slug = decodeURIComponent(encodedSlug);

            if (!slug) {
                showMessage('danger', 'Sản phẩm này chưa có slug.');
                return;
            }

            history.pushState({}, '', `${TEST_PAGE_URL}/${encodeURIComponent(slug)}`);

            await loadProductBySlug(slug);
        }

        async function loadProductBySlug(slug) {
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
                    showMessage('danger', result.message || 'Không lấy được sản phẩm theo slug.');
                    return;
                }

                fillProductForm(result.data);

            } catch (error) {
                showMessage('danger', 'Lỗi khi lấy sản phẩm theo slug: ' + error.message);
            }
        }

        function fillProductForm(product) {
            document.getElementById('product_id').value = product.id ?? '';
            document.getElementById('brand_id').value = product.brand_id ?? '';
            document.getElementById('category_id').value = product.category_id ?? '';
            document.getElementById('name').value = product.name ?? '';
            document.getElementById('slug').value = product.slug ?? '';
            document.getElementById('thumbnail_url').value = product.thumbnail_url ?? '';
            document.getElementById('short_description').value = product.short_description ?? '';
            document.getElementById('description').value = product.description ?? '';
            document.getElementById('is_featured').value = product.is_featured ? '1' : '0';
            document.getElementById('status').value = product.status ?? 'active';

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function submitProduct() {
            clearErrors();

            const productId = document.getElementById('product_id').value;
            const isEdit = productId !== '';

            const url = isEdit ? `${API_URL}/${productId}` : API_URL;
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
                    isEdit ? 'Cập nhật sản phẩm thành công.' : 'Thêm sản phẩm thành công.'
                );

                resetForm();
                loadProducts();

            } catch (error) {
                showMessage('danger', 'Lỗi khi gửi request: ' + error.message);
            }
        }

        async function toggleProductStatus(id) {
            if (!confirm('Bạn có chắc muốn đổi trạng thái sản phẩm này không?')) {
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

                loadProducts();

            } catch (error) {
                showMessage('danger', 'Lỗi khi cập nhật trạng thái: ' + error.message);
            }
        }

        async function deleteProduct(id) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này không?')) {
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
                    showMessage('danger', result.message || 'Xóa sản phẩm thất bại.');
                    return;
                }

                showMessage('success', 'Xóa sản phẩm thành công.');

                resetForm();
                loadProducts();

            } catch (error) {
                showMessage('danger', 'Lỗi khi xóa sản phẩm: ' + error.message);
            }
        }

        function resetForm() {
            document.getElementById('product_id').value = '';
            document.getElementById('brand_id').value = '';
            document.getElementById('category_id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('slug').value = '';
            document.getElementById('thumbnail_url').value = '';
            document.getElementById('short_description').value = '';
            document.getElementById('description').value = '';
            document.getElementById('is_featured').value = '0';
            document.getElementById('status').value = 'active';

            clearErrors();
            resetProductUrl();
        }

        function reloadProducts() {
            resetForm();
            loadProducts();
        }

        async function initPage() {
            await loadBrandsForSelect();
            await loadCategoriesForSelect();
            await loadProducts();

            if (INITIAL_SLUG) {
                await loadProductBySlug(INITIAL_SLUG);
            }
        }

        initPage();
    </script>

</body>

</html>
