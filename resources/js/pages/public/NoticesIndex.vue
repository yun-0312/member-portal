<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-6xl mx-auto space-y-6">

        <!-- 1. ページヘッダー -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-4 gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                    <span>📢</span> お知らせ・回覧一覧
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-1">会員向けのお知らせおよび各種回覧情報</p>
            </div>

            <router-link
            to="/dashboard"
            class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm transition-all active:scale-95 self-start md:self-auto"
            >
                <span>← ダッシュボードへ戻る</span>
            </router-link>
        </div>

        <!-- 🔍 キーワード検索フォーム (現在のクエリパラメータを保持) -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <form @submit.prevent="handleSearch" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input
                        v-model="keywordInput"
                        type="text"
                        placeholder="キーワードで検索 (タイトル、委員会名、本文)..."
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
            <p class="text-sm font-medium">お知らせを読み込み中…</p>
        </div>

        <!-- 3. メインコンテンツ -->
        <div v-else-if="noticesData && noticesData.data?.length" class="space-y-4">

            <!-- お知らせカード一覧 -->
            <div class="space-y-4">
                <article
                    v-for="notice in noticesData.data"
                    :key="notice.id"
                    class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:shadow-md hover:border-slate-300 transition-all duration-200 flex flex-col gap-4 group"
                >
                    <div class="space-y-3 flex-1">
                    <!-- バッジ・日付エリア -->
                        <div class="flex items-center flex-wrap gap-2 text-xs">
                            <!-- カテゴリバッジ -->
                            <span
                            :class="[
                                'px-2.5 py-0.5 rounded-md font-bold border',
                                notice.category?.slug === 'letter'
                                ? 'bg-blue-50 text-blue-700 border-blue-200'
                                : 'bg-indigo-50 text-indigo-700 border-indigo-200'
                            ]"
                            >
                            {{ notice.category?.name || 'お知らせ' }}
                            </span>

                            <!-- 委員会バッジ -->
                            <span
                            v-if="notice.committee_name"
                            class="bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded-md font-medium"
                            >
                            {{ notice.committee_name }}
                            </span>

                            <!-- 公開日時 -->
                            <time class="text-slate-400 font-mono ml-auto">
                            📅 {{ formatDate(notice.published_at) }}
                            </time>
                        </div>

                        <!-- タイトル -->
                        <h2 class="text-base md:text-lg font-bold text-slate-800 group-hover:text-blue-600 transition-colors leading-snug">
                            {{ notice.title }}
                        </h2>

                        <!-- 本文 -->
                        <p
                        class="text-xs md:text-sm text-slate-600 leading-relaxed whitespace-pre-wrap"
                        v-html="formatBodyWithLinks(notice.body)"
                        ></p>

                        <!-- 📎 添付ファイルエリア -->
                        <div v-if="notice.files && notice.files.length" class="pt-2 flex flex-wrap gap-2">
                            <a
                                v-for="file in notice.files"
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
            </div>

            <!-- 4. ページネーション -->
            <div v-if="noticesData.last_page > 1" class="pt-6">
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
                        {{ currentPage }} / {{ noticesData.last_page }}
                    </span>

                    <button
                        @click="changePage(getNextPageUrl())"
                        :disabled="currentPage === noticesData.last_page"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-white text-slate-700 border-slate-200 hover:bg-slate-50 disabled:bg-slate-100 disabled:text-slate-300 disabled:border-transparent disabled:cursor-not-allowed active:scale-95"
                    >
                        次へ →
                    </button>
                </div>

                <!-- 💻 PC表示 (md以上) -->
                <div class="hidden md:flex items-center justify-center gap-1.5">
                    <button
                        v-for="(link, idx) in noticesData.links"
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
            <span class="text-3xl block mb-2">📭</span>
            <p class="text-sm font-medium">条件に一致するお知らせはありません</p>
        </div>

        </div>
    </div>
</template>

<script setup>
    import { ref, computed, watch, onMounted, reactive } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import api from '../../api.js'

    const route = useRoute()
    const router = useRouter()

    const noticesData = ref(null)
    const loading = ref(true)
    const keywordInput = ref(route.query.keyword || '')

    // 検索実行処理 (現在のクエリパラメータをそのまま保持しつつ keyword を更新)
    const handleSearch = () => {
        const query = { ...route.query }

        if (keywordInput.value.trim()) {
            query.keyword = keywordInput.value.trim()
        } else {
            delete query.keyword
        }

        // 検索時はページ指定をID指定をリセット
        delete query.page
        delete query.id

        router.push({ query })
    }

    // キーワード検索のみクリア (category 等の既存クエリは維持)
    const clearKeyword = () => {
        keywordInput.value = ''
        const query = { ...route.query }
        delete query.keyword
        delete query.page
        delete query.id
        router.push({ query })
    }

    // APIデータ取得
    const fetchNotices = async (page = 1) => {
        loading.value = true
        try {
            const params = {
                page: page,
                ...route.query
            }
            const res = await api.get('/notices', { params })
            noticesData.value = res.data
        } catch (error) {
            console.error('お知らせの取得に失敗しました:', error)
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

    const currentPage = computed(() => {
        return noticesData.value?.current_page || 1
    })

    //  スマホ用: 前のページのURL取得
    const getPrevPageUrl = () => {
        if (currentPage.value <= 1) return null
        const links = noticesData.value?.links || []
        const prevLink = links.find(link =>
            link.label.includes('Previous') ||
            link.label.includes('previous') ||
            link.label.includes('&laquo;') ||
            link.label.includes('前へ')
        )
        return prevLink ? prevLink.url : null
    }

    //  スマホ用: 次のページのURL取得
    const getNextPageUrl = () => {
        const lastPage = noticesData.value?.last_page || 1
        if (currentPage.value >= lastPage) return null
        const links = noticesData.value?.links || []
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
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    }

    const formatPaginationLabel = (label) => {
        if (label.includes('Previous') || label.includes('previous')) return '前へ'
        if (label.includes('Next') || label.includes('next')) return '次へ'
        return label
    }

    // URLクエリの変化を監視（直接URL移動・戻る進む等）
    watch(
        () => route.query,
        (newQuery) => {
            keywordInput.value = newQuery.keyword || ''
            fetchNotices(newQuery.page || 1)
        }
    )

    onMounted(() => {
        fetchNotices(route.query.page || 1)
    })

    const formatBodyWithLinks = (text) => {
        if (!text) return ''

        const escapedText = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')

        const urlRegex = /(https?:\/\/[^\s<]+)/g

        return escapedText.replace(urlRegex, (url) => {
            const isImage = /\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i.test(url)

            if (isImage) {
                return `<img src="${url}" alt="挿入画像" class="my-3 max-h-96 border border-slate-200 shadow-sm object-cover" />`
            } else {
                return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline font-medium break-all" onclick="event.stopPropagation()">${url}</a>`
            }
        })
    }

    const getFileUrl = (filePath) => {
        if (!filePath) return '#'
        if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
            return filePath
        }
        const storageBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost'
        return `${storageBaseUrl}/storage/${filePath}`
    }
</script>