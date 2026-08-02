<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

        <!-- 1. ヘッダー & ナビゲーション -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-4 gap-4">
            <div>
            <div class="flex items-center gap-2 mb-1">
                <span
                v-if="video"
                :class="[
                    'px-2.5 py-0.5 text-xs font-bold rounded-md text-white shadow-xs',
                    getProviderBadge(video.external_url).bgClass
                ]"
                >
                {{ getProviderBadge(video.external_url).label }}
                </span>
                <span class="text-xs text-slate-400 font-mono" v-if="video">
                ID: #{{ video.id }}
                </span>
            </div>
            <h1 class="text-xl md:text-2xl font-extrabold text-slate-800 tracking-tight">
                {{ video?.title || '動画詳細' }}
            </h1>
            </div>

            <div class="shrink-0">
            <button
                @click="goBack"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition-all active:scale-95"
            >
                <span>← 一覧へ戻る</span>
            </button>
            </div>
        </div>

        <!-- 2. ローディング表示 -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
            <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-medium">動画情報を読み込み中…</p>
        </div>

        <!-- 3. エラー表示 -->
        <div v-else-if="error" class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-500 space-y-4">
            <span class="text-4xl block">⚠️</span>
            <p class="text-sm font-medium">{{ error }}</p>
            <button
            @click="goBack"
            class="inline-flex items-center gap-1.5 text-xs font-bold bg-slate-800 text-white px-4 py-2 rounded-xl transition-all active:scale-95"
            >
            一覧ページへ戻る
            </button>
        </div>

        <!-- 4. メインコンテンツ -->
        <div v-else-if="video" class="space-y-6">

            <!-- 🎥 動画プレイヤーエリア -->
            <div class="bg-black rounded-2xl overflow-hidden shadow-lg border border-slate-200">
            <div class="relative aspect-video flex items-center justify-center">
                <!-- iframe表示（YouTube / Vimeo） -->
                <iframe
                v-if="getEmbedUrl(video.external_url)"
                :src="getEmbedUrl(video.external_url)"
                class="w-full h-full border-0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                ></iframe>

                <!-- 外部リンク誘導表示（Zoomなどの埋め込み不可リンク） -->
                <div v-else class="p-8 text-center text-white space-y-4 max-w-md">
                <div class="text-5xl">🔒</div>
                <p class="text-sm font-medium leading-relaxed text-slate-200">
                    この動画（Zoom録画等）はセキュリティ保護のため、直接埋め込み表示ができません。外部サイトにてご視聴ください。
                </p>
                <a
                    :href="video.external_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl shadow-md transition-all active:scale-95"
                >
                    <span>🎥 動画を外部サイトで視聴する</span>
                    <span>↗</span>
                </a>
                </div>
            </div>
            </div>

            <!-- 📄 詳細・概要カード -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">

            <!-- メタ情報（公開日・掲載終了日） -->
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-100 font-mono">
                <div class="flex items-center gap-1.5">
                <span>📅</span>
                <span>公開日: <strong>{{ formatDate(video.published_at) }}</strong></span>
                </div>
                <div v-if="video.expired_at" class="flex items-center gap-1.5">
                <span>⏳</span>
                <span>掲載終了: <strong>{{ formatDate(video.expired_at) }}</strong></span>
                </div>
            </div>

            <!-- 概要本文（長文・HTML対応） -->
            <div class="space-y-2">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider">概要</h2>
                <div
                class="prose prose-slate prose-sm max-w-none text-slate-700 leading-relaxed [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-xl [&_a]:text-blue-600 [&_a]:underline"
                v-html="formatDescription(video.description)"
                ></div>
            </div>

            <!-- 📎 添付ファイル・配布資料一覧 -->
            <div v-if="video.files && video.files.length > 0" class="pt-6 border-t border-slate-100 space-y-3">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider">添付資料・配布ファイル</h2>
                <div class="flex flex-wrap gap-2">
                <a
                    v-for="file in video.files"
                    :key="file.id"
                    :href="getFileUrl(file.path)"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-600 border border-slate-200 rounded-xl text-xs font-semibold transition-all active:scale-95"
                >
                    <span>📎</span>
                    <span>{{ file.name }}</span>
                    <span class="text-slate-400 text-[10px]">↗</span>
                </a>
                </div>
            </div>

            </div>

        </div>

        </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api.js'

const route = useRoute()
const router = useRouter()

const video = ref(null)
const indexUrl = ref('/videos')
const loading = ref(true)
const error = ref(null)

// ==========================================
//  プロバイダー判定 & URL解析ロジック
// ==========================================

const getProvider = (url) => {
    if (!url) return 'unknown'
    if (url.includes('youtube.com') || url.includes('youtu.be')) return 'youtube'
    if (url.includes('vimeo.com')) return 'vimeo'
    if (url.includes('zoom.us')) return 'zoom'
    return 'unknown'
}

const getProviderBadge = (url) => {
    const provider = getProvider(url)
    switch (provider) {
        case 'youtube':
        return { label: 'YouTube', bgClass: 'bg-red-600' }
        case 'vimeo':
        return { label: 'Vimeo', bgClass: 'bg-sky-500' }
        case 'zoom':
        return { label: 'Zoom 録画', bgClass: 'bg-blue-600' }
        default:
        return { label: '動画', bgClass: 'bg-slate-600' }
    }
}

const getYoutubeId = (url) => {
    if (!url) return null
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/
    const match = url.match(regExp)
    return (match && match[2].length === 11) ? match[2] : null
}

const getVimeoId = (url) => {
    if (!url) return null
    const match = url.match(/vimeo\.com\/(?:video\/)?([0-9]+)/)
    return match ? match[1] : null
    }

    const getEmbedUrl = (url) => {
    const provider = getProvider(url)
    if (provider === 'youtube') {
        const videoId = getYoutubeId(url)
        return videoId ? `https://www.youtube.com/embed/${videoId}` : null
    }
    if (provider === 'vimeo') {
        const videoId = getVimeoId(url)
        return videoId ? `https://player.vimeo.com/video/${videoId}` : null
    }
    return null
}

// ==========================================
// 🛠️ 共通処理・データ取得
// ==========================================

const fetchVideoDetail = async () => {
    loading.value = true
    error.value = null
    try {
        const videoId = route.params.id
        const res = await api.get(`/videos/${videoId}`)

        // バックエンドの返却形式 { item: { ... }, index_url: "..." } に対応
        if (res.data?.item) {
        video.value = res.data.item
        if (res.data.index_url) {
            indexUrl.value = res.data.index_url
        }
        } else {
        video.value = res.data
        }
    } catch (err) {
        console.error('動画詳細の取得に失敗しました:', err)
        error.value = '指定された動画が見つからないか、アクセス権限がありません。'
    } finally {
        loading.value = false
    }
}

const getFileUrl = (filePath) => {
    if (!filePath) return '#'
    if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
        return filePath
    }
    const baseURL = api.defaults.baseURL || '/storage'
    const cleanBase = baseURL.endsWith('/') ? baseURL.slice(0, -1) : baseURL
    const cleanPath = filePath.startsWith('/') ? filePath : `/${filePath}`

    return `${cleanBase}/${cleanPath}`
}

const formatDescription = (text) => {
    if (!text) return ''

    if (/<[a-z][\s\S]*>/i.test(text)) {
        return text
    }

    const escapedText = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')

    const urlRegex = /(https?:\/\/[^\s<]+)/g
    const linkedText = escapedText.replace(urlRegex, (url) => {
        return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline font-medium break-all">${url}</a>`
    })

    return linkedText.replace(/\n/g, '<br>')
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const goBack = () => {
    if (window.history.length > 1) {
        router.back()
    } else {
        router.push(indexUrl.value)
    }
}

onMounted(() => {
    fetchVideoDetail()
})
</script>