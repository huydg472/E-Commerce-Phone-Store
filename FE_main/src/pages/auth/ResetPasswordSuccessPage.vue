<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const countdown = ref(5)
let timer = null

const progressWidth = computed(() => {
    return (countdown.value / 5) * 100
})

const goToLogin = () => {
    sessionStorage.removeItem('reset_password_success')
    router.replace('/auth/login')
}

onMounted(() => {
    timer = setInterval(() => {
        countdown.value--

        if (countdown.value <= 0) {
            clearInterval(timer)
            goToLogin()
        }
    }, 1000)
})

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer)
    }
})
</script>

<template>
    <div class="success-page min-vh-100 d-flex align-items-center justify-content-center">
        <div class="success-card text-center">
            <div class="success-icon mx-auto mb-4">
                <i class="bi bi-check-lg"></i>
            </div>

            <h1 class="success-title fw-bold mb-3">
                Đặt lại mật khẩu thành công
            </h1>

            <p class="success-desc mx-auto mb-4">
                Mật khẩu của bạn đã được cập nhật thành công.
                <br />
                Vui lòng chờ trong giây lát để quay về trang đăng nhập.
            </p>

            <div class="countdown-box d-flex align-items-center justify-content-center gap-3 mx-auto mb-4">
                <i class="bi bi-clock"></i>

                <span>
                    Tự động quay về trang đăng nhập sau
                    <strong>5 giây</strong>
                </span>
            </div>

            <div class="progress reset-progress mb-4">
                <div class="progress-bar" style="width: 80%"></div>
            </div>

            <RouterLink to="/auth/login" class="btn back-login-btn mb-4">
                <i class="bi bi-arrow-counterclockwise me-2"></i>
                Quay về đăng nhập ngay
            </RouterLink>

            <hr />

            <p class="note mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Nếu không được chuyển hướng tự động,
                <br />
                vui lòng nhấn vào nút quay về đăng nhập.
            </p>
        </div>
    </div>
</template>

<style scoped>
.success-page {
    background:
        radial-gradient(circle at top left, rgba(0, 102, 255, 0.08), transparent 34%),
        radial-gradient(circle at bottom right, rgba(0, 102, 255, 0.08), transparent 34%),
        #f4f8ff;
    padding: 24px;
}

.success-card {
    width: 100%;
    max-width: 760px;
    padding: 70px 74px 48px;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid #dfe8f5;
    border-radius: 18px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
}

.success-icon {
    width: 116px;
    height: 116px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0066ff, #1d7cff);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 58px;
    box-shadow: 0 14px 30px rgba(0, 102, 255, 0.28);
}

.success-title {
    color: #061c46;
    font-size: 34px;
}

.success-desc {
    max-width: 520px;
    color: #52627a;
    font-size: 18px;
    line-height: 1.6;
}

.countdown-box {
    max-width: 520px;
    min-height: 76px;
    background: #eef5ff;
    border-radius: 12px;
    color: #2d3f61;
    font-size: 18px;
}

.countdown-box i,
.countdown-box strong {
    color: #0066ff;
}

.reset-progress {
    height: 8px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    background: #d8e7ff;
    border-radius: 999px;
    overflow: hidden;
}

.progress-bar {
    background: #0066ff;
}

.back-login-btn {
    color: #0066ff;
    font-size: 18px;
    font-weight: 700;
    border: none;
    text-decoration: none;
}

.note {
    color: #52627a;
    font-size: 17px;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .success-card {
        padding: 48px 24px 36px;
    }

    .success-title {
        font-size: 28px;
    }
}
</style>