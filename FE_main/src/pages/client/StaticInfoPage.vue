<script setup>
import {computed, watchEffect} from 'vue'
import {usePublicSiteSettings} from '@/composables/usePublicSiteSettings'

const props = defineProps({
  pageKey: {
    type: String,
    required: true,
  },
})

const {brandName, supportPhone, supportEmail, settings} = usePublicSiteSettings()
const bankName = computed(() => settings.value?.bank_name || 'Vietcombank')
const bankAccountNumber = computed(() => settings.value?.bank_account_number || '0123456789')
const bankAccountName = computed(() => settings.value?.bank_account_name || 'CTY TNHH ZinMobile')
const cashOnDeliveryNote = computed(() => settings.value?.cash_on_delivery_note || 'Thanh toán khi nhận hàng áp dụng cho đơn đủ điều kiện.')

const pageContent = computed(() => {
  const pages = {
    about: {
      title: 'Giới thiệu',
      subtitle: `Về ${brandName.value} và hành trình phục vụ khách hàng.`,
      heroNote: 'Một điểm đến cho điện thoại chính hãng, phụ kiện chuẩn và dịch vụ hậu mãi rõ ràng.',
      sections: [
        {
          title: 'Chúng tôi là ai',
          items: [
            `${brandName.value} tập trung vào trải nghiệm mua sắm nhanh, minh bạch và đáng tin cậy.`,
            'Danh mục sản phẩm được sắp xếp rõ ràng theo thương hiệu, nhu cầu và mức giá.',
            'Đội ngũ hỗ trợ theo dõi đơn hàng, thanh toán và bảo hành xuyên suốt quá trình mua hàng.',
          ],
        },
        {
          title: 'Giá trị cốt lõi',
          items: [
            'Hàng chính hãng, thông tin rõ ràng.',
            'Mua nhanh, thanh toán gọn, giao hàng minh bạch.',
            'Hậu mãi và hỗ trợ sau bán là một phần của sản phẩm.',
          ],
        },
      ],
    },
    warranty: {
      title: 'Chính sách bảo hành',
      subtitle: 'Các nguyên tắc bảo hành áp dụng cho sản phẩm mua tại cửa hàng.',
      heroNote: 'Chính sách bảo hành được xây dựng để khách hàng dễ hiểu, dễ kiểm tra và dễ thực hiện.',
      sections: [
        {
          title: 'Phạm vi bảo hành',
          items: [
            'Sản phẩm còn trong thời hạn bảo hành do nhà sản xuất hoặc cửa hàng công bố.',
            'Lỗi phần cứng do nhà sản xuất hoặc phát sinh trong quá trình sử dụng đúng cách.',
            'Tem, số serial và phụ kiện cần giữ nguyên theo yêu cầu bảo hành của từng nhóm hàng.',
          ],
        },
        {
          title: 'Quy trình tiếp nhận',
          items: [
            'Khách hàng liên hệ hotline hoặc email để mô tả tình trạng sản phẩm.',
            'Cửa hàng xác nhận điều kiện bảo hành và hướng dẫn gửi sản phẩm.',
            'Kết quả xử lý sẽ được phản hồi sau khi kiểm tra thực tế.',
          ],
        },
      ],
    },
    returns: {
      title: 'Chính sách đổi trả',
      subtitle: 'Điều kiện và quy trình đổi trả để giảm tranh chấp khi mua hàng.',
      heroNote: 'Nội dung này nên được dùng làm khung tham chiếu nội bộ, có thể tinh chỉnh theo từng đợt kinh doanh.',
      sections: [
        {
          title: 'Điều kiện đổi trả',
          items: [
            'Sản phẩm còn nguyên trạng, không phát sinh hư hỏng do người dùng.',
            'Có đủ hóa đơn, phiếu giao hàng hoặc mã đơn hàng.',
            'Nằm trong thời gian đổi trả được công bố tại thời điểm mua hàng.',
          ],
        },
        {
          title: 'Các bước xử lý',
          items: [
            'Liên hệ hỗ trợ và cung cấp mã đơn hàng.',
            'Gửi ảnh/video tình trạng sản phẩm nếu cần.',
            'Nhận hướng dẫn trả hàng hoặc đổi sang sản phẩm phù hợp.',
          ],
        },
      ],
    },
    privacy: {
      title: 'Chính sách bảo mật',
      subtitle: 'Cách hệ thống thu thập, sử dụng và bảo vệ dữ liệu khách hàng.',
      heroNote: 'Chính sách tập trung vào việc giữ dữ liệu ở mức cần thiết và chỉ dùng cho mục đích phục vụ đơn hàng.',
      sections: [
        {
          title: 'Dữ liệu được sử dụng',
          items: [
            'Thông tin tài khoản, liên hệ và địa chỉ nhận hàng.',
            'Lịch sử đơn hàng và tương tác cần cho hỗ trợ sau bán.',
            'Dữ liệu thanh toán trong phạm vi nghiệp vụ cần thiết.',
          ],
        },
        {
          title: 'Cam kết bảo vệ',
          items: [
            'Không chia sẻ thông tin ngoài các bên liên quan đến đơn hàng.',
            'Chỉ người có thẩm quyền mới được truy cập các dữ liệu quản trị.',
            'Tăng cường bảo mật tài khoản và lưu vết thao tác khi cần.',
          ],
        },
      ],
    },
    buyGuide: {
      title: 'Hướng dẫn mua hàng',
      subtitle: 'Các bước mua hàng từ xem sản phẩm đến hoàn tất thanh toán.',
      heroNote: 'Luồng mua hàng được thiết kế ngắn, rõ và tối ưu cho thiết bị di động.',
      sections: [
        {
          title: '4 bước cơ bản',
          items: [
            'Chọn sản phẩm và phiên bản phù hợp.',
            'Thêm vào giỏ hoặc mua ngay.',
            'Nhập địa chỉ, chọn giao hàng và phương thức thanh toán.',
            'Xác nhận đơn hàng và theo dõi trạng thái trong tài khoản.',
          ],
        },
      ],
    },
    paymentGuide: {
      title: 'Hướng dẫn thanh toán',
      subtitle: 'Các phương thức thanh toán hiện có trên hệ thống.',
      heroNote: 'Thanh toán hỗ trợ COD, chuyển khoản ngân hàng và cổng điện tử tùy cấu hình.',
      sections: [
        {
          title: 'Thanh toán khi nhận hàng',
          items: [
            cashOnDeliveryNote.value,
            'Phù hợp với khách hàng muốn kiểm tra hàng trước khi thanh toán.',
          ],
        },
        {
          title: 'Chuyển khoản ngân hàng',
          items: [
            `Ngân hàng: ${bankName.value}.`,
            `Số tài khoản: ${bankAccountNumber.value}.`,
            `Chủ tài khoản: ${bankAccountName.value}.`,
          ],
        },
        {
          title: 'Hỗ trợ thanh toán',
          items: [
            `Hotline: ${supportPhone.value}.`,
            `Email: ${supportEmail.value}.`,
            'VNPay, MoMo hoặc cổng khác có thể được bật theo cấu hình hệ thống.',
          ],
        },
      ],
    },
    faq: {
      title: 'Câu hỏi thường gặp',
      subtitle: 'Một số câu hỏi hay gặp trước khi mua hàng hoặc thanh toán.',
      heroNote: 'Nếu chưa thấy câu trả lời, khách hàng có thể liên hệ trực tiếp qua hotline hoặc email.',
      sections: [
        {
          title: 'Câu hỏi phổ biến',
          items: [
            'Bao lâu thì đơn hàng được xác nhận? Thường ngay sau khi hệ thống ghi nhận thanh toán hoặc xác nhận COD.',
            'Có thể thay đổi địa chỉ sau khi đặt hàng không? Có thể, nếu đơn chưa chuyển sang giai đoạn xử lý.',
            'Làm sao để được hỗ trợ nhanh? Gọi hotline hoặc gửi email kèm mã đơn hàng.',
          ],
        },
      ],
    },
  }

  return pages[props.pageKey] || pages.about
})

watchEffect(() => {
  if (typeof document !== 'undefined') {
    document.title = `${pageContent.value.title} | ${brandName.value}`
  }
})
</script>

<template>
  <main class="static-page">
    <section class="hero-card">
      <div>
        <p class="eyebrow">Nội dung hệ thống</p>
        <h1>{{ pageContent.title }}</h1>
        <p class="subtitle">{{ pageContent.subtitle }}</p>
      </div>
      <div class="hero-note">
        {{ pageContent.heroNote }}
      </div>
    </section>

    <section class="content-grid">
      <article v-for="section in pageContent.sections" :key="section.title" class="content-card">
        <h2>{{ section.title }}</h2>
        <ul>
          <li v-for="item in section.items" :key="item">{{ item }}</li>
        </ul>
      </article>
    </section>
  </main>
</template>

<style scoped>
.static-page {
  padding: 18px 0 40px;
}

.hero-card {
  padding: 24px;
  border-radius: 22px;
  background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 30%),
  linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  border: 1px solid #e5edf8;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 18px;
}

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.hero-card h1 {
  margin: 0;
  color: #0f172a;
  font-size: 34px;
  font-weight: 900;
}

.subtitle {
  margin: 10px 0 0;
  color: #64748b;
  line-height: 1.7;
}

.hero-note {
  padding: 18px;
  border-radius: 18px;
  background: #0f172a;
  color: #ffffff;
  line-height: 1.7;
}

.content-grid {
  margin-top: 16px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.content-card {
  padding: 20px;
  border-radius: 20px;
  background: #ffffff;
  border: 1px solid #e5eaf3;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.content-card h2 {
  margin: 0 0 12px;
  color: #0f172a;
  font-size: 20px;
  font-weight: 900;
}

.content-card ul {
  margin: 0;
  padding-left: 18px;
  color: #475569;
  line-height: 1.8;
}

@media (max-width: 992px) {
  .hero-card,
  .content-grid {
    grid-template-columns: 1fr;
  }
}
</style>
