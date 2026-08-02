<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-6xl mx-auto space-y-6">

        <!-- 1. ページヘッダー -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-4 gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                    <span>📁</span> {{ dynamicTitle }}
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-1">{{ dynamicDescription }}</p>
            </div>

            <div class="flex items-center gap-2">
            <!-- category より上の階層（subcategory または year がある時のみ）がある場合のみボタンを表示 -->
            <button
                v-if="hasUpperLevel"
                @click="goBackUpper"
                class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm transition-all active:scale-95"
            >
                <span>← 上位階層へ</span>
            </button>

            <router-link
                to="/dashboard"
                class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm transition-all active:scale-95"
            >
                <span>ダッシュボード</span>
            </router-link>
            </div>
        </div>

        <!-- キーワード検索フォーム -->
        <div
            v-if="itemsData?.display_mode === 'list'"
            class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm"
        >
            <form @submit.prevent="handleSearch" class="flex items-center gap-2">
            <div class="relative flex-1">
                <input
                v-model="keywordInput"
                type="text"
                placeholder="キーワードで検索 (タイトル、本文)..."
                class="w-full pl-9 pr-3 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                />
                <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
            </div>

            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs md:text-sm py-2 px-4 rounded-xl shadow-sm transition-all active:scale-95 shrink-0"
            >
                検索
            </button>

            <button
                v-if="route.query.keyword"
                type="button"
                @click="clearKeyword"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium text-xs py-2 px-3 rounded-xl transition-all shrink-0"
                title="キーワードをクリア"
            >
                クリア
            </button>
            </form>
        </div>

        <!-- 2. ローディング表示 -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
            <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-medium">データを読み込み中…</p>
        </div>

        <!-- 3. メインコンテンツ -->
        <div v-else-if="itemsData">

            <!-- モード A: サブカテゴリー選択カード一覧 (display_mode === 'subcategory') -->
            <div v-if="itemsData.display_mode === 'subcategory'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <button
                v-for="sub in itemsData.subcategories"
                :key="sub.id"
                @click="selectSubcategory(sub)"
                class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-blue-400 hover:bg-blue-50/30 transition-all text-left flex items-center justify-between group"
            >
                <div>
                <p class="font-bold text-slate-800 text-sm md:text-base group-hover:text-blue-600 transition-colors">
                    {{ sub.name }}
                </p>
                <p v-if="sub.description" class="text-xs text-slate-500 mt-1 line-clamp-2">
                    {{ sub.description }}
                </p>
                </div>
                <span class="text-slate-300 group-hover:text-blue-500 text-lg group-hover:translate-x-1 transition-transform">→</span>
            </button>
            </div>

            <!-- モード B: 年度一覧カード (display_mode === 'year_archive') -->
            <div v-else-if="itemsData.display_mode === 'year_archive'" class="space-y-4">
            <div v-if="itemsData.years && itemsData.years.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <button
                v-for="year in itemsData.years"
                :key="year"
                @click="selectYear(year)"
                class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-blue-500 hover:bg-blue-600 hover:text-white transition-all text-center font-bold text-base text-slate-700 group flex flex-col items-center justify-center gap-1"
                >
                <span class="text-xs font-normal opacity-70 group-hover:opacity-90">年度</span>
                <span>{{ year }}年</span>
                </button>
            </div>
            <div v-else class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400">
                <span class="text-3xl block mb-2">📁</span>
                <p class="text-sm font-medium">該当する年度データが見つかりませんでした</p>
            </div>
            </div>

            <!-- モード C: 通常の記事一覧 (display_mode === 'list') -->
            <div v-else-if="itemsData.display_mode === 'list'" class="space-y-4">
            <div v-if="itemsData.contents?.data?.length" class="space-y-4">
                <article
                v-for="item in itemsData.contents.data"
                :key="item.id"
                class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:shadow-md hover:border-slate-300 transition-all duration-200 flex flex-col gap-4 group"
                >
                <div class="space-y-3 flex-1">
                    <!-- バッジ・日付エリア -->
                    <div class="flex items-center flex-wrap gap-2 text-xs">
                    <span
                        v-if="item.category"
                        class="px-2.5 py-0.5 rounded-md font-bold border bg-blue-50 text-blue-700 border-blue-200"
                    >
                        {{ item.category?.name }}
                    </span>

                    <span
                        v-if="item.subcategory"
                        class="bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded-md font-medium"
                    >
                        {{ item.subcategory?.name }}
                    </span>

                    <time class="text-slate-400 font-mono ml-auto">
                        📅 {{ formatDate(item.published_at || item.created_at) }}
                    </time>
                    </div>

                    <!-- タイトル -->
                    <h2 class="text-base md:text-lg font-bold text-slate-800 group-hover:text-blue-600 transition-colors leading-snug">
                    {{ item.title }}
                    </h2>

                    <!-- 本文 -->
                    <div
                        class="prose prose-slate prose-sm max-w-none text-slate-600 leading-relaxed"
                        v-html="formatBodyWithLinks(item.body)"
                    ></div>

                    <!-- 📎 添付ファイルエリア -->
                    <div v-if="item.files && item.files.length" class="pt-2 flex flex-wrap gap-2">
                    <a
                        v-for="file in item.files"
                        :key="file.id"
                        :href="getFileUrl(file.path)"
                        :download="file.name"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-600 border border-slate-200/80 hover:border-blue-200 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all group/file"
                    >
                        <span>{{ file.type?.startsWith('image/') ? '🖼️' : '📎' }}</span>
                        <span class="truncate max-w-[200px] group-hover/file:underline">{{ file.name }}</span>
                        <span class="text-slate-400 group-hover/file:text-blue-500 text-[10px] ml-0.5">⬇️</span>
                    </a>
                    </div>
                </div>
                </article>

                <!-- ページネーション -->
                <div v-if="itemsData.contents.last_page > 1" class="pt-6">
                    <!--  スマホ表示 (md未満) -->
                    <div class="flex md:hidden items-center justify-between gap-2 bg-white p-3 rounded-2xl border border-slate-200 shadow-xs">
                        <button
                            @click="changePage(getPrevPageUrl())"
                            :disabled="currentPage === 1"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-white text-slate-700 border-slate-200 hover:bg-slate-50 disabled:bg-slate-100 disabled:text-slate-300 disabled:border-transparent disabled:cursor-not-allowed active:scale-95"
                        >
                            ← 前へ
                        </button>

                        <span class="text-xs font-bold text-slate-600 font-mono">
                            {{ currentPage }} / {{ itemsData.contents.last_page }}
                        </span>

                        <button
                            @click="changePage(getNextPageUrl())"
                            :disabled="currentPage === itemsData.contents.last_page"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-white text-slate-700 border-slate-200 hover:bg-slate-50 disabled:bg-slate-100 disabled:text-slate-300 disabled:border-transparent disabled:cursor-not-allowed active:scale-95"
                        >
                            次へ →
                        </button>
                    </div>

                    <!--  PC表示 (md以上) -->
                    <div class="hidden md:flex items-center justify-center gap-1.5">
                        <button
                            v-for="(link, idx) in itemsData.contents.links"
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

            <!-- 件数ゼロ -->
            <div v-else class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400">
                <span class="text-3xl block mb-2">📭</span>
                <p class="text-sm font-medium">条件に一致するデータはありません</p>
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

    const apiEndpoint = '/contents'

    const route = useRoute()
    const router = useRouter()

    const itemsData = ref(null)
    const loading = ref(true)
    const keywordInput = ref(route.query.keyword || '')

    // Category より上位の階層（subcategory または year）が存在するか判定
    const hasUpperLevel = computed(() => {
    return !!(route.query.year || route.query.subcategory)
    })

    const dynamicTitle = computed(() => {
    if (itemsData.value?.context?.name) {
        const name = itemsData.value.context.name
        if (route.query.year) {
        return `${name} (${route.query.year}年度)`
        }
        return name
    }
    return 'コンテンツ一覧'
    })

    const dynamicDescription = computed(() => {
    const mode = itemsData.value?.display_mode
    if (mode === 'subcategory') return '表示するカテゴリーを選択してください'
    if (mode === 'year_archive') return '表示する年度を選択してください'
    return 'コンテンツ・資料の一覧です'
    })

    const selectSubcategory = (sub) => {
    const query = { ...route.query, subcategory: sub.slug || sub.id }
    delete query.page
    router.push({ query })
    }

    const selectYear = (year) => {
    const query = { ...route.query, year }
    delete query.page
    router.push({ query })
    }

    // 💡 修正4: 上位階層に戻る際、category は消さずに year / subcategory のみ順番に削る
    const goBackUpper = () => {
    const query = { ...route.query }

    if (query.year) {
        delete query.year
    } else if (query.subcategory) {
        delete query.subcategory
    }
    // ※ category は絶対に delete しない

    delete query.page
    router.push({ query })
    }

    // 💡 修正5: 検索時は現在のクエリ（category, subcategory, year 等）を保持したまま keyword を反映
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

    const clearKeyword = () => {
    keywordInput.value = ''
    const query = { ...route.query }
    delete query.keyword
    delete query.page
    router.push({ query })
    }

    const fetchItems = async (page = 1) => {
    loading.value = true
    try {
        const params = {
        page,
        ...route.query
        }
        const res = await api.get(apiEndpoint, { params })
        itemsData.value = res.data
    } catch (error) {
        console.error('データの取得に失敗しました:', error)
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
        page
        }
    })
    }

    // 現在のページ番号
    const currentPage = computed(() => {
        return itemsData.value?.contents?.current_page || 1
    })

    // スマホ用: 前のページのURL取得
    const getPrevPageUrl = () => {
        if (currentPage.value <= 1) return null
        const links = itemsData.value?.contents?.links || []
        const prevLink = links.find(link =>
            link.label.includes('Previous') ||
            link.label.includes('previous') ||
            link.label.includes('&laquo;') ||
            link.label.includes('前へ')
        )
        return prevLink ? prevLink.url : null
    }

    // スマホ用: 次のページのURL取得
    const getNextPageUrl = () => {
        const lastPage = itemsData.value?.contents?.last_page || 1
        if (currentPage.value >= lastPage) return null
        const links = itemsData.value?.contents?.links || []
        const nextLink = links.find(link =>
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
    if (isNaN(date.getTime())) return dateString
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    }

    const formatPaginationLabel = (label) => {
    if (!label) return ''
    if (label.includes('Previous') || label.includes('previous') || label.includes('&laquo;')) return '&laquo; 前へ'
    if (label.includes('Next') || label.includes('next') || label.includes('&raquo;')) return '次へ &raquo;'
    return label
    }

    watch(
    () => route.query,
    (newQuery) => {
        keywordInput.value = newQuery.keyword || ''
        fetchItems(newQuery.page || 1)
    }
    )

    onMounted(() => {
    fetchItems(route.query.page || 1)
    })

    const formatBodyWithLinks = (text) => {
        if (!text) return ''

        // すでに HTML タグ（<p> や <img>）が含まれている場合はそのまま描画
        if (/<[a-z][\s\S]*>/i.test(text)) {
            return text
        }

        // プレーンテキストデータの場合は URL を自動リンク・画像化 & 改行保持
        const escapedText = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')

        const urlRegex = /(https?:\/\/[^\s<]+)/g

        const linkedText = escapedText.replace(urlRegex, (url) => {
            const cleanUrl = url.trim()
            const isImage = /\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i.test(cleanUrl)

            if (isImage) {
                return `<img src="${cleanUrl}" alt="挿入画像" class="my-3 max-h-96 border border-slate-200 shadow-sm object-cover rounded-xl" />`
            } else {
                return `<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline font-medium break-all" onclick="event.stopPropagation()">${cleanUrl}</a>`
            }
        })

        return linkedText.replace(/\n/g, '<br>')
    }

    const getFileUrl = (filePath) => {
    if (!filePath) return '#'
    if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
        return filePath
    }
    const storageBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost'
    const normalizedBase = storageBaseUrl.replace(/\/+$/, '')
    const normalizedPath = filePath.replace(/^\/+/, '')
    return `${normalizedBase}/storage/${normalizedPath}`
    }
</script>