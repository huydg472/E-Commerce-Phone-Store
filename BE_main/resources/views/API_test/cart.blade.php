<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Test API Cart</title>

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
            margin-bottom: 6px;
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

        .summary {
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>

<body>

    <h2>Test API Cart bằng Blade</h2>

    <div id="message"></div>

    <div class="box">
        <h3>Token đăng nhập</h3>

        <p>
            Vì cart cần biết user nào đang đăng nhập, bạn paste token lấy từ API login vào đây.
        </p>

        <label>Bearer token</label>
        <input type="text" id="token" placeholder="Dán token đăng nhập vào đây">

        <button onclick="saveToken()">Lưu token</button>
        <button onclick="clearToken()">Xóa token</button>
    </div>

    <div class="box">
        <h3>Thêm sản phẩm vào giỏ</h3>

        <label>Biến thể sản phẩm</label>
        <select id="product_variant_id">
            <option value="">-- Chọn biến thể --</option>
        </select>
        <div id="error_product_variant_id" class="error"></div>

        <label>Số lượng</label>
        <input type="number" id="quantity" value="1" min="1">
        <div id="error_quantity" class="error"></div>

        <button onclick="addToCart()">Thêm vào giỏ</button>
        <button onclick="loadCart()">Tải lại giỏ hàng</button>
        <button onclick="clearCart()">Xóa toàn bộ giỏ</button>
    </div>

    <div class="box">
        <h3>Giỏ hàng</h3>

        <table>
            <thead>
                <tr>
                    <th>Cart Item ID</th>
                    <th>Variant ID</th>
                    <th>Tên / SKU</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Tạm tính</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody id="cartTable">
                <tr>
                    <td colspan="7">Chưa tải giỏ hàng</td>
                </tr>
            </tbody>
        </table>

        <p class="summary" id="cartSummary"></p>
    </div>

    <h3>JSON API trả về</h3>
    <pre id="rawJson"></pre>

    <script>
        const CART_API_URL = '/api/cart';
        const CART_ITEM_API_URL = '/api/cart/items';
        const VARIANT_API_URL = '/api/product-variants';

        function showMessage(type, text) {
            document.getElementById('message').innerHTML =
                `<div class="${type}">${text}</div>`;
        }

        function showRawJson(result) {
            document.getElementById('rawJson').innerText =
                JSON.stringify(result, null, 2);
        }

        function getToken() {
            return document.getElementById('token').value.trim();
        }

        function saveToken() {
            const token = getToken();

            if (!token) {
                showMessage('danger', 'Bạn chưa nhập token.');
                return;
            }

            localStorage.setItem('api_test_token', token);
            showMessage('success', 'Đã lưu token.');
        }

        function clearToken() {
            localStorage.removeItem('api_test_token');
            document.getElementById('token').value = '';
            showMessage('success', 'Đã xóa token.');
        }

        function loadSavedToken() {
            const token = localStorage.getItem('api_test_token');

            if (token) {
                document.getElementById('token').value = token;
            }
        }

        function authHeaders(hasJsonBody = false) {
            const token = getToken();

            const headers = {
                'Accept': 'application/json'
            };

            if (hasJsonBody) {
                headers['Content-Type'] = 'application/json';
            }

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            return headers;
        }

        function clearErrors() {
            document.getElementById('error_product_variant_id').innerText = '';
            document.getElementById('error_quantity').innerText = '';
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

        function formatMoney(value) {
            return Number(value ?? 0).toLocaleString('vi-VN') + ' đ';
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
                        ID: ${variant.id} - SKU: ${variant.sku ?? ''} - Giá: ${formatMoney(variant.sale_price ?? variant.price)}
                    </option>
                `;
                });

            } catch (error) {
                showMessage('danger', 'Không tải được danh sách biến thể: ' + error.message);
            }
        }

        async function loadCart() {
            try {
                const response = await fetch(CART_API_URL, {
                    method: 'GET',
                    headers: authHeaders()
                });

                const result = await response.json();
                showRawJson(result);

                if (response.status === 401) {
                    showMessage('danger', 'Bạn chưa đăng nhập hoặc token không hợp lệ.');
                    return;
                }

                if (!response.ok) {
                    showMessage('danger', result.message || 'Không tải được giỏ hàng.');
                    return;
                }

                renderCart(result.data);

            } catch (error) {
                showMessage('danger', 'Lỗi khi tải giỏ hàng: ' + error.message);
            }
        }

        function renderCart(cart) {
            const tbody = document.getElementById('cartTable');
            const summary = document.getElementById('cartSummary');

            const items = cart?.items || cart?.cart_items || [];

            if (items.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="7">Giỏ hàng trống</td>
                </tr>
            `;
                summary.innerText = 'Tổng tiền: 0 đ';
                return;
            }

            tbody.innerHTML = items.map(item => {
                const variant = item.product_variant || item.variant || {};
                const price = item.price ?? variant.sale_price ?? variant.price ?? 0;
                const quantity = item.quantity ?? 0;
                const subtotal = item.subtotal ?? item.total_price ?? price * quantity;

                return `
                <tr>
                    <td>${item.id}</td>
                    <td>${item.product_variant_id ?? variant.id ?? ''}</td>
                    <td>${variant.sku ?? item.sku ?? item.product_name ?? ''}</td>
                    <td>${formatMoney(price)}</td>
                    <td>
                        <input type="number"
                               value="${quantity}"
                               min="1"
                               id="quantity_${item.id}"
                               style="width: 80px;">
                    </td>
                    <td>${formatMoney(subtotal)}</td>
                    <td>
                        <button onclick="updateCartItem(${item.id})">Cập nhật</button>
                        <button onclick="removeCartItem(${item.id})">Xóa</button>
                    </td>
                </tr>
            `;
            }).join('');

            const total = cart.total_amount ??
                cart.total ??
                cart.subtotal ??
                items.reduce((sum, item) => {
                    const variant = item.product_variant || item.variant || {};
                    const price = item.price ?? variant.sale_price ?? variant.price ?? 0;
                    return sum + price * (item.quantity ?? 0);
                }, 0);

            summary.innerText = `Tổng tiền: ${formatMoney(total)}`;
        }

        async function addToCart() {
            clearErrors();

            const productVariantId = document.getElementById('product_variant_id').value;
            const quantity = document.getElementById('quantity').value;

            try {
                const response = await fetch(CART_ITEM_API_URL, {
                    method: 'POST',
                    headers: authHeaders(true),
                    body: JSON.stringify({
                        product_variant_id: productVariantId,
                        quantity: quantity
                    })
                });

                const result = await response.json();
                showRawJson(result);

                if (response.status === 401) {
                    showMessage('danger', 'Bạn chưa đăng nhập hoặc token không hợp lệ.');
                    return;
                }

                if (response.status === 422) {
                    showMessage('danger', 'Dữ liệu không hợp lệ.');
                    showValidationErrors(result.errors || {});
                    return;
                }

                if (!response.ok) {
                    showMessage('danger', result.message || 'Thêm vào giỏ thất bại.');
                    return;
                }

                showMessage('success', result.message || 'Thêm vào giỏ thành công.');
                loadCart();

            } catch (error) {
                showMessage('danger', 'Lỗi khi thêm vào giỏ: ' + error.message);
            }
        }

        async function updateCartItem(cartItemId) {
            const quantity = document.getElementById(`quantity_${cartItemId}`).value;

            try {
                const response = await fetch(`${CART_ITEM_API_URL}/${cartItemId}`, {
                    method: 'PUT',
                    headers: authHeaders(true),
                    body: JSON.stringify({
                        quantity: quantity
                    })
                });

                const result = await response.json();
                showRawJson(result);

                if (!response.ok) {
                    showMessage('danger', result.message || 'Cập nhật giỏ hàng thất bại.');
                    return;
                }

                showMessage('success', result.message || 'Cập nhật giỏ hàng thành công.');
                loadCart();

            } catch (error) {
                showMessage('danger', 'Lỗi khi cập nhật giỏ hàng: ' + error.message);
            }
        }

        async function removeCartItem(cartItemId) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ không?')) {
                return;
            }

            try {
                const response = await fetch(`${CART_ITEM_API_URL}/${cartItemId}`, {
                    method: 'DELETE',
                    headers: authHeaders()
                });

                const result = await response.json();
                showRawJson(result);

                if (!response.ok) {
                    showMessage('danger', result.message || 'Xóa sản phẩm khỏi giỏ thất bại.');
                    return;
                }

                showMessage('success', result.message || 'Xóa sản phẩm khỏi giỏ thành công.');
                loadCart();

            } catch (error) {
                showMessage('danger', 'Lỗi khi xóa sản phẩm khỏi giỏ: ' + error.message);
            }
        }

        async function clearCart() {
            if (!confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng không?')) {
                return;
            }

            try {
                const response = await fetch(CART_API_URL, {
                    method: 'DELETE',
                    headers: authHeaders()
                });

                const result = await response.json();
                showRawJson(result);

                if (!response.ok) {
                    showMessage('danger', result.message || 'Xóa giỏ hàng thất bại.');
                    return;
                }

                showMessage('success', result.message || 'Đã xóa toàn bộ giỏ hàng.');
                loadCart();

            } catch (error) {
                showMessage('danger', 'Lỗi khi xóa giỏ hàng: ' + error.message);
            }
        }

        async function initPage() {
            loadSavedToken();
            await loadVariantsForSelect();

            if (getToken()) {
                await loadCart();
            }
        }

        initPage();
    </script>

</body>

</html>
