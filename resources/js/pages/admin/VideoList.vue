<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-6xl mx-auto space-y-6">

        <!-- 1. ページヘッダー -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-4 gap-4">
            <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <span>🎬</span> 研修会・講演会動画一覧
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">研修会・講演会動画の追加・編集・管理</p>
            </div>

            <!-- アクションボタンエリア（新規動画登録 ＆ ダッシュボード） -->
            <div class="flex items-center gap-2 self-start md:self-auto">
            <router-link
                v-if="storeUrl"
                :to="storeUrl"
                class="inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl shadow-sm hover:shadow transition-all active:scale-95"
            >
                <span>➕</span>
                <span>新規動画を登録</span>
            </router-link>

            <router-link
                to="/admin/dashboard"
                class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm transition-all active:scale-95"
            >
                <span>← ダッシュボード</span>
            </router-link>
            </div>
        </div>

        <!-- 🔍 検索フォーム -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <form @submit.prevent="handleSearch" class="flex items-center gap-2">
            <!-- キーワード入力 -->
            <div class="relative flex-1 w-full">
                <input
                v-model="keywordInput"
                type="text"
                placeholder="キーワードで検索 (タイトル、説明文)..."
                class="w-full pl-9 pr-3 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                />
                <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs md:text-sm py-2 px-4 rounded-xl shadow-sm transition-all active:scale-95"
                >
                検索
                </button>

                <button
                v-if="route.query.keyword"
                type="button"
                @click="clearFilters"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium text-xs py-2 px-3 rounded-xl transition-all"
                title="検索条件をクリア"
                >
                クリア
                </button>
            </div>
            </form>
        </div>

        <!-- 2. ローディング表示 -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
            <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-medium">動画一覧を読み込み中…</p>
        </div>

        <!-- 3. メインコンテンツ -->
        <div v-else-if="videoList.length > 0" class="space-y-6">

            <!-- 動画カードグリッド -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <article
                v-for="video in videoList"
                :key="video.id"
                class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col transition-all duration-200 hover:shadow-md hover:border-slate-300"
            >
                <!-- 動画サムネイルエリア -->
                <div
                class="relative aspect-video bg-slate-900 group cursor-pointer overflow-hidden"
                @click="openModal(video)"
                >
                <img
                    v-if="thumbnails[video.id]"
                    :src="thumbnails[video.id]"
                    :alt="video.title"
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-slate-500 text-xs bg-slate-800">
                    <span>📹 {{ getProviderBadge(video.external_url).label }}</span>
                </div>

                <!-- プラットフォーム種別バッジ -->
                <span
                    :class="[
                    'absolute top-3 left-3 px-2 py-0.5 text-[10px] font-bold rounded-md shadow-sm border text-white',
                    getProviderBadge(video.external_url).bgClass
                    ]"
                >
                    {{ getProviderBadge(video.external_url).label }}
                </span>

                <!-- 再生ボタンオーバーレイ -->
                <div class="absolute inset-0 bg-slate-900/30 group-hover:bg-slate-900/10 transition-all flex items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-red-600/90 text-white flex items-center justify-center shadow-lg transition-transform group-hover:scale-110">
                    <span class="ml-1 text-xl">▶</span>
                    </div>
                </div>
                </div>

                <!-- カード本文 -->
                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <!-- タイトル ＆ 管理者アクション（詳細・編集ボタン） -->
                    <div class="flex items-start justify-between gap-2">
                    <h2
                        @click="openModal(video)"
                        class="text-base font-bold text-slate-800 leading-snug cursor-pointer hover:text-blue-600 transition-colors line-clamp-2 flex-1"
                    >
                        {{ video.title }}
                    </h2>

                    <router-link
                        v-if="video.show_url"
                        :to="video.show_url"
                        class="shrink-0 inline-flex items-center gap-1 text-[11px] font-bold text-slate-700 hover:text-blue-600 bg-slate-100 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 px-2.5 py-1 rounded-lg transition-all active:scale-95"
                        title="詳細・編集"
                    >
                        <span>✏️</span>
                        <span>詳細・編集</span>
                    </router-link>
                    </div>

                    <!-- 説明文 -->
                    <div
                        class="prose prose-slate prose-xs max-w-none text-slate-600 leading-relaxed line-clamp-3"
                        v-html="formatDescription(video.description)"
                    ></div>
                </div>

                <div class="space-y-3 pt-3 border-t border-slate-100 text-xs">
                    <!-- 添付ファイル一覧 -->
                    <div v-if="video.files && video.files.length > 0" class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">資料・添付ファイル</span>
                    <div class="flex flex-wrap gap-1">
                        <a
                        v-for="file in video.files"
                        :key="file.id"
                        :href="getFileUrl(file.path)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-600 border border-slate-200 rounded-lg text-xs transition-colors"
                        >
                        <span>📎</span>
                        <span class="truncate max-w-[150px]">{{ file.name }}</span>
                        </a>
                    </div>
                    </div>

                    <!-- 期間情報 -->
                    <div class="flex flex-col gap-1 text-[11px] text-slate-400 font-mono bg-slate-50 p-2 rounded-lg">
                    <div>📅 公開日: {{ formatDate(video.published_at) }}</div>
                    <div v-if="video.expired_at">⏳ 掲載終了: {{ formatDate(video.expired_at) }}</div>
                    </div>
                </div>
                </div>
            </article>
            </div>

            <!-- 4. ページネーション -->
            <div v-if="paginationLinks.length > 0 && lastPage > 1" class="pt-6">
                <!-- 📱 スマホ表示 (md未満) -->
                <div class="flex md:hidden items-center justify-between gap-2 bg-white p-3 rounded-2xl border border-slate-200 shadow-xs">
                    <button
                        @click="changePage(getPrevPageUrl())"
                        :disabled="currentPage === 1"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-white text-slate-700 border-slate-200 hover:bg-slate-50 disabled:bg-slate-100 disabled:text-slate-300 disabled:border-transparent disabled:cursor-not-allowed active:scale-95"
                    >
                        ← 前へ
                    </button>

                    <span class="text-xs font-bold text-slate-600 font-mono">
                        {{ currentPage }} / {{ lastPage }}
                    </span>

                    <button
                        @click="changePage(getNextPageUrl())"
                        :disabled="currentPage === lastPage"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-white text-slate-700 border-slate-200 hover:bg-slate-50 disabled:bg-slate-100 disabled:text-slate-300 disabled:border-transparent disabled:cursor-not-allowed active:scale-95"
                    >
                        次へ →
                    </button>
                </div>

                <!-- 💻 PC表示 (md以上) -->
                <div class="hidden md:flex items-center justify-center gap-1.5">
                    <button
                        v-for="(link, idx) in paginationLinks"
                        :key="idx"
                        @click="changePage(link.url)"
                        :disabled="!link.url || link.active"
                        v-html="formatPaginationLabel(link.label)"
                        :class="[
                        'px-3.5 py-2 rounded-xl text-xs font-bold transition-all border',
                        link.active
                            ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                            : link.url
                            ? 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'
                            : 'bg-slate-100 text-slate-300 border-transparent cursor-not-allowed'
                        ]"
                    />
                </div>
            </div>

        </div>

        <!-- 5. 件数ゼロの時 -->
        <div v-else class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400">
            <span class="text-3xl block mb-2">🎬</span>
            <p class="text-sm font-medium">条件に一致する研修動画はありません</p>
        </div>

        </div>

        <!-- 🎬 動画再生モーダル -->
        <div
        v-if="selectedVideo"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm animate-fade-in"
        @click.self="closeModal"
        >
        <div class="bg-white rounded-2xl max-w-4xl w-full overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <!-- モーダルヘッダー -->
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span
                :class="[
                    'px-2 py-0.5 text-[10px] font-bold rounded-md text-white',
                    getProviderBadge(selectedVideo.external_url).bgClass
                ]"
                >
                {{ getProviderBadge(selectedVideo.external_url).label }}
                </span>
                <h3 class="font-bold text-slate-800 text-sm md:text-base line-clamp-1">
                {{ selectedVideo.title }}
                </h3>
            </div>

            <div class="flex items-center gap-2">
                <!-- モーダル内の詳細・編集ボタン -->
                <router-link
                v-if="selectedVideo.show_url"
                :to="selectedVideo.show_url"
                class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-600 border border-slate-200 hover:border-blue-200 rounded-lg text-xs font-bold transition-all"
                >
                <span>✏️</span>
                <span>詳細・編集</span>
                </router-link>

                <button
                @click="closeModal"
                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors text-lg"
                >
                ✕
                </button>
            </div>
            </div>

            <!-- プレイヤー部分 (プロバイダーで分岐) -->
            <div class="relative aspect-video bg-black shrink-0 flex items-center justify-center">
            <!-- iframe表示（YouTube / Vimeo） -->
            <iframe
                v-if="getEmbedUrl(selectedVideo.external_url)"
                :src="getEmbedUrl(selectedVideo.external_url)"
                class="w-full h-full border-0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
            ></iframe>

            <!-- 外部リンク誘導表示（Zoomなどの埋め込み不可リンク） -->
            <div v-else class="p-8 text-center text-white space-y-4 max-w-md">
                <div class="text-4xl">🔒</div>
                <p class="text-sm font-medium leading-relaxed">
                この動画（Zoom録画等）はセキュリティ保護のため、外部サイトで直接視聴してください。
                </p>
                <a
                :href="selectedVideo.external_url"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs md:text-sm rounded-xl shadow-md transition-all active:scale-95"
                >
                <span>🎥 Zoomで動画を視聴する</span>
                <span>↗</span>
                </a>
            </div>
            </div>

            <div class="p-5 overflow-y-auto space-y-4 text-xs md:text-sm">


                <!-- モーダル内の資料ダウンロード -->
                <div v-if="selectedVideo.files && selectedVideo.files.length > 0" class="pt-3 border-t border-slate-100">
                    <h4 class="font-bold text-slate-700 mb-2">添付資料・配布ファイル</h4>
                    <div class="flex flex-wrap gap-2">
                        <a
                            v-for="file in selectedVideo.files"
                            :key="file.id"
                            :href="getFileUrl(file.path)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-600 border border-slate-200 rounded-xl text-xs font-medium transition-colors"
                        >
                            <span>📎</span>
                            <span>{{ file.name }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api.js'

const route = useRoute()
const router = useRouter()

const videosData = ref(null)
const loading = ref(true)
const keywordInput = ref(route.query.keyword || '')
const selectedVideo = ref(null)
const thumbnails = ref({})
const storeUrl = ref('/admin/videos/create') // 新規作成用URL (フォールバック初期値)

// データの算出プロパティ
const videoList = computed(() => videosData.value?.data || [])
const paginationLinks = computed(() => videosData.value?.links || [])
const currentPage = computed(() => videosData.value?.current_page || 1)
const lastPage = computed(() => videosData.value?.last_page || 1)

// ==========================================
// 🎥 プロバイダー判定 & URL解析ロジック
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
        return { label: 'YouTube', bgClass: 'bg-red-600 border-red-700' }
        case 'vimeo':
        return { label: 'Vimeo', bgClass: 'bg-sky-500 border-sky-600' }
        case 'zoom':
        return { label: 'Zoom 録画', bgClass: 'bg-blue-600 border-blue-700' }
        default:
        return { label: '動画', bgClass: 'bg-slate-600 border-slate-700' }
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
        return videoId ? `https://www.youtube.com/embed/${videoId}?autoplay=1` : null
    }
    if (provider === 'vimeo') {
        const videoId = getVimeoId(url)
        return videoId ? `https://player.vimeo.com/video/${videoId}?autoplay=1` : null
    }
    return null
}

const loadThumbnails = async (list) => {
    for (const video of list) {
        const provider = getProvider(video.external_url)

        if (provider === 'youtube') {
        const id = getYoutubeId(video.external_url)
        if (id) {
            thumbnails.value[video.id] = `https://img.youtube.com/vi/${id}/hqdefault.jpg`
        }
        } else if (provider === 'vimeo') {
        try {
            const res = await fetch(`https://vimeo.com/api/oembed.json?url=${encodeURIComponent(video.external_url)}`)
            const data = await res.json()
            if (data.thumbnail_url) {
            thumbnails.value[video.id] = data.thumbnail_url
            }
        } catch (e) {
            console.warn('Vimeo サムネイルの取得に失敗しました:', e)
        }
        }
    }
}

// ==========================================
// 🛠️ 共通処理・API取得
// ==========================================

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

const openModal = (video) => {
    selectedVideo.value = video
}

const closeModal = () => {
    selectedVideo.value = null
}

const handleSearch = () => {
    const query = { ...route.query }

    if (keywordInput.value.trim()) {
        query.keyword = keywordInput.value.trim()
    } else {
        delete query.keyword
    }

    delete query.page
    router.push({ query })
}

const clearFilters = () => {
    keywordInput.value = ''
    const query = { ...route.query }
    delete query.keyword
    delete query.page
    router.push({ query })
}

// 管理者用APIからデータ取得
const fetchVideos = async (page = 1) => {
    loading.value = true
    try {
        const params = {
        page: page,
        ...route.query
        }
        const res = await api.get('/admin/videos', { params })
        const data = res.data?.data ? res.data : res

        videosData.value = data

        // store_url のセット
        if (data.store_url) {
        storeUrl.value = data.store_url.includes('/create')
            ? data.store_url
            : `${data.store_url}/create`
        }

        if (data.data) {
        await loadThumbnails(data.data)
        }
    } catch (error) {
        console.error('管理者用動画一覧の取得に失敗しました:', error)
    } finally {
        loading.value = false
    }
}

const changePage = (url) => {
    if (!url) return
    const urlParams = new URLSearchParams(url.split('?')[1])
    const page = urlParams.get('page') || 1

    router.push({
        query: {
        ...route.query,
        page: page
        }
    })
}

// スマホ用: 前のページのURL取得
const getPrevPageUrl = () => {
    if (currentPage.value <= 1) return null
    const prevLink = paginationLinks.value.find(link =>
        link.label.includes('Previous') ||
        link.label.includes('previous') ||
        link.label.includes('&laquo;') ||
        link.label.includes('前へ')
    )
    return prevLink ? prevLink.url : null
}

//  スマホ用: 次のページのURL取得
const getNextPageUrl = () => {
    if (currentPage.value >= lastPage.value) return null
    const nextLink = paginationLinks.value.find(link =>
        link.label.includes('Next') ||
        link.label.includes('next') ||
        link.label.includes('&raquo;') ||
        link.label.includes('次へ')
    )
    return nextLink ? nextLink.url : null
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const formatPaginationLabel = (label) => {
    if (label.includes('previous') || label.includes('Previous')) return '前へ'
    if (label.includes('next') || label.includes('Next')) return '次へ'
    return label
}

// 説明文描画用ヘルパー関数
const formatDescription = (text) => {
    if (!text) return ''

    // すでに HTML タグ（<p> や <img> など）が含まれている場合はそのまま描画
    if (/<[a-z][\s\S]*>/i.test(text)) {
        return text
    }

    // プレーンテキストデータの場合は改行を <br> に変換＆URL を自動リンク化
    const escapedText = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')

    const urlRegex = /(https?:\/\/[^\s<]+)/g
    const linkedText = escapedText.replace(urlRegex, (url) => {
        return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline font-medium break-all" onclick="event.stopPropagation()">${url}</a>`
    })

    return linkedText.replace(/\n/g, '<br>')
}

watch(
    () => route.query,
    (newQuery) => {
        keywordInput.value = newQuery.keyword || ''
        fetchVideos(newQuery.page || 1)
    }
)

onMounted(() => {
    fetchVideos(route.query.page || 1)
})
</script>