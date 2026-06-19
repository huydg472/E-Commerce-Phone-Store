import {computed, onMounted} from 'vue'
import {storeToRefs} from 'pinia'
import {useSettingsStore} from '@/stores/settingsStore'

export function usePublicSiteSettings(options = {}) {
    const {autoFetch = true} = options

    const settingsStore = useSettingsStore()
    const {settings} = storeToRefs(settingsStore)

    const siteName = computed(() => settings.value?.site_name || settings.value?.brand_name || 'ZinMobile')
    const brandName = computed(() => settings.value?.brand_name || settings.value?.site_name || 'ZinMobile')
    const logoUrl = computed(() => String(settings.value?.logo_url ?? '').trim())
    const faviconUrl = computed(() => settings.value?.favicon_url || settings.value?.logo_url || '/favicon.ico')
    const supportPhone = computed(() => settings.value?.support_phone || '0909 000 000')
    const supportEmail = computed(() => settings.value?.support_email || 'support@zinmobile.vn')
    const contactEmail = computed(() => settings.value?.contact_email || 'contact@zinmobile.vn')
    const address = computed(() => settings.value?.address || 'TP. Hồ Chí Minh, Việt Nam')
    const description = computed(() => settings.value?.footer_description || 'Hệ thống bán lẻ điện thoại chính hãng, giá tốt, dịch vụ tận tâm.')
    const slogan = computed(() => settings.value?.slogan || 'Hệ thống bán lẻ điện thoại chính hãng')
    const socials = computed(() => [
        {label: 'Facebook', url: settings.value?.facebook_url},
        {label: 'Instagram', url: settings.value?.instagram_url},
        {label: 'TikTok', url: settings.value?.tiktok_url},
        {label: 'YouTube', url: settings.value?.youtube_url},
        {label: 'Zalo', url: settings.value?.zalo_url},
    ].filter((item) => Boolean(item.url)))

    const fetchPublic = () => settingsStore.fetchPublic()

    if (autoFetch) {
        onMounted(() => {
            settingsStore.fetchPublic().catch(() => {
                // Fall back to defaults when the API is unavailable.
            })
        })
    }

    return {
        settingsStore,
        settings,
        siteName,
        brandName,
        logoUrl,
        faviconUrl,
        supportPhone,
        supportEmail,
        contactEmail,
        address,
        description,
        slogan,
        socials,
        fetchPublic,
    }
}
