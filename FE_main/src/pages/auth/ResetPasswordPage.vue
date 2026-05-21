<script setup>
</script>

<template>
    <div class="reset-page min-vh-100 d-flex align-items-center justify-content-center">
        <div class="reset-card">
            <div class="text-center">
                <div class="reset-icon mx-auto mb-4">
                    <i class="bi bi-lock"></i>
                    <i class="bi bi-arrow-clockwise refresh-icon"></i>
                </div>

                <h1 class="reset-title fw-bold mb-3">
                    Đặt lại mật khẩu
                </h1>

                <p class="reset-desc mx-auto mb-5">
                    Nhập mật khẩu mới cho tài khoản của bạn để hoàn tất việc đặt lại mật khẩu.
                </p>
            </div>

            <form>
                <div class="mb-4">
                    <label class="form-label fw-bold">Mật khẩu mới</label>

                    <div class="input-group auth-input">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>

                        <input type="password" class="form-control" placeholder="Nhập mật khẩu mới" />

                        <button type="button" class="input-group-text eye-btn">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Xác nhận mật khẩu</label>

                    <div class="input-group auth-input">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>

                        <input type="password" class="form-control" placeholder="Nhập lại mật khẩu mới" />

                        <button type="button" class="input-group-text eye-btn">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="button" class="btn btn-primary w-100 auth-btn">
                    Đặt lại mật khẩu
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.reset-page {
    background:
        radial-gradient(circle at top left, rgba(0, 102, 255, 0.08), transparent 34%),
        radial-gradient(circle at bottom right, rgba(0, 102, 255, 0.08), transparent 34%),
        #f4f8ff;
    padding: 24px;
}

.reset-card {
    width: 100%;
    max-width: 760px;
    padding: 72px 86px;
    background: #ffffff;
    border: 1px solid #dfe8f5;
    border-radius: 18px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
}

.reset-icon {
    width: 112px;
    height: 112px;
    border-radius: 50%;
    background: #edf4ff;
    color: #0066ff;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    font-size: 52px;
}

.refresh-icon {
    position: absolute;
    right: 28px;
    bottom: 30px;
    font-size: 26px;
}

.reset-title {
    color: #061c46;
    font-size: 36px;
}

.reset-desc {
    max-width: 440px;
    color: #52627a;
    font-size: 18px;
    line-height: 1.6;
}

.form-label {
    font-size: 16px;
    color: #111827;
}

.auth-input {
    height: 58px;
    border: 1px solid #d8e1ee;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
}

.auth-input .input-group-text {
    background: #ffffff;
    border: none;
    color: #66789f;
    font-size: 20px;
    padding: 0 16px;
}

.auth-input .form-control {
    border: none;
    box-shadow: none;
    font-size: 16px;
}

.eye-btn {
    cursor: pointer;
}

.auth-btn {
    height: 64px;
    border-radius: 10px;
    background: #0066ff;
    border-color: #0066ff;
    font-size: 20px;
    font-weight: 700;
}

@media (max-width: 768px) {
    .reset-card {
        padding: 44px 24px;
    }
}
</style>