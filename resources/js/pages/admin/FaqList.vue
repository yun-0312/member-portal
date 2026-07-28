<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-6xl mx-auto space-y-6">

        <!-- 1. ページヘッダー -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-4 gap-4">
            <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <span>❓</span> FAQ・よくある質問 (管理画面)
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">FAQデータの閲覧・追加・編集・インポート・エクスポート管理</p>
            </div>

            <div class="flex items-center gap-2 flex-wrap self-start md:self-auto">
            <!-- ➕ 新規データ登録ボタン -->
            <router-link
                v-if="storeUrl"
                :to="getCreateUrl(storeUrl)"
                class="inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl shadow-sm hover:shadow transition-all active:scale-95"
            >
                <span>➕</span>
                <span>新規FAQ登録</span>
            </router-link>

            <!-- 📤 CSVインポートボタン -->
            <label
                v-if="importUrl"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 hover:text-emerald-600 bg-white border border-slate-200 hover:border-emerald-200 px-3 py-2 rounded-xl shadow-sm cursor-pointer transition-all active:scale-95"
                :class="{ 'opacity-50 pointer-events-none': importing }"
            >
                <span>📤</span>
                <span>{{ importing ? '取り込み中…' : 'CSV取り込み' }}</span>
                <input
                type="file"
                accept=".csv"
                class="hidden"
                @change="handleImport"
                :disabled="importing"
                />
            </label>

            <!-- 📥 CSVダウンロードボタン -->
            <button
                v-if="exportUrl"
                type="button"
                @click="handleExport"
                :disabled="exporting"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 hover:text-blue-600 bg-white border border-slate-200 hover:border-blue-200 px-3 py-2 rounded-xl shadow-sm transition-all active:scale-95 disabled:opacity-50"
            >
                <span>📥</span>
                <span>{{ exporting ? '出力中…' : 'CSV出力' }}</span>
            </button>

            <router-link
                to="/admin/dashboard"
                class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm transition-all active:scale-95"
            >
                <span>ダッシュボード</span>
            </router-link>
            </div>
        </div>

        <!-- 🔍 検索フォーム (カテゴリ選択 + キーワード検索) -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row items-center gap-2">

            <!-- カテゴリ選択ドロップダウン -->
            <div class="w-full sm:w-48 shrink-0">
                <select
                v-model="categoryInput"
                class="w-full px-3 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-700"
                >
                <option value="">すべてのカテゴリ</option>
                <option
                    v-for="cat in categoryOptions"
                    :key="cat.id"
                    :value="cat.id"
                >
                    {{ cat.name }}
                </option>
                </select>
            </div>

            <!-- キーワード入力 -->
            <div class="relative flex-1 w-full">
                <input
                v-model="keywordInput"
                type="text"
                placeholder="キーワードで検索 (質問、回答)..."
                class="w-full pl-9 pr-3 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                />
                <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                <button
                type="submit"
                class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs md:text-sm py-2 px-4 rounded-xl shadow-sm transition-all active:scale-95"
                >
                検索
                </button>

                <button
                v-if="route.query.keyword || route.query.category"
                type="button"
                @click="clearFilters"
                class="flex-1 sm:flex-none bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium text-xs py-2 px-3 rounded-xl transition-all"
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
            <p class="text-sm font-medium">FAQを読み込み中…</p>
        </div>

        <!-- 3. メインコンテンツ -->
        <div v-else-if="faqList.length > 0" class="space-y-4">

            <!-- FAQ一覧 (常時表示) -->
            <div class="space-y-4">
            <article
                v-for="faq in faqList"
                :key="faq.id"
                class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-5 md:p-6 space-y-4 transition-all duration-200 hover:border-slate-300"
            >
                <!-- 質問エリア -->
                <div class="space-y-2 border-b border-slate-100 pb-4">
                <div class="flex items-center flex-wrap gap-2 text-xs">
                    <!-- 診療区分 (カテゴリバッジ) -->
                    <span
                    v-if="faq.category?.name"
                    class="bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-md font-bold"
                    >
                    {{ faq.category.name }}
                    </span>

                    <!-- 受付日（作成日） -->
                    <time class="text-slate-400 font-mono ml-auto">
                    📅 受付日: {{ formatDate(faq.received_at) }}
                    </time>

                    <!-- ✏️ 管理者用 アクションボタンエリア (詳細 / 編集) -->
                    <div class="flex items-center gap-1.5 ml-2">
                    <router-link
                        v-if="faq.update_url"
                        :to="faq.update_url"
                        class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-700 hover:text-blue-600 bg-slate-100 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 px-2.5 py-1 rounded-lg transition-all active:scale-95"
                    >
                        <span>✏️</span>
                        <span>編集</span>
                    </router-link>

                    <button
                        v-if="faq.delete_url || faq.id"
                        type="button"
                        @click="handleDelete(faq)"
                        class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-700 hover:text-blue-600 bg-slate-100 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 px-2.5 py-1 rounded-lg transition-all active:scale-95"
                    >
                        <span>×</span>
                        <span>削除</span>
                </button>
                    </div>
                </div>

                <!-- 質問テキスト (Q.) -->
                <div class="flex items-start gap-2.5 pt-1">
                    <span class="text-blue-600 font-black text-lg md:text-xl leading-none shrink-0">Q.</span>
                    <h2 class="text-base md:text-lg font-bold text-slate-800 leading-snug">
                    <router-link v-if="faq.show_url" :to="faq.show_url" class="hover:underline">
                        {{ faq.question }}
                    </router-link>
                    <span v-else>{{ faq.question }}</span>
                    </h2>
                </div>
                </div>

                <!-- 回答エリア (A.) -->
                <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                <div class="flex items-start gap-2.5">
                    <span class="text-rose-500 font-black text-lg md:text-xl leading-none shrink-0">A.</span>
                    <p
                    class="text-xs md:text-sm text-slate-700 leading-relaxed whitespace-pre-wrap flex-1"
                    v-html="formatBodyWithLinks(faq.answer)"
                    ></p>
                </div>
                </div>
            </article>
            </div>

            <!-- 4. ページネーション -->
            <div v-if="paginationLinks.length > 0 && lastPage > 1" class="flex items-center justify-center gap-1.5 pt-6">
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

        <!-- 5. 件数ゼロの時 -->
        <div v-else class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400">
            <span class="text-3xl block mb-2">📭</span>
            <p class="text-sm font-medium">条件に一致するFAQはありません</p>
        </div>

        </div>

        <!-- 6. インポート結果表示モーダル -->
        <div
            v-if="importResult"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6 space-y-4 max-h-[85vh] flex flex-col">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>📊</span> CSVインポート結果
                    </h3>
                    <button
                        @click="importResult = null"
                        type="button"
                        class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer"
                    >
                        ×
                    </button>
                </div>

                <!-- 概要バッジ -->
                <div class="flex items-center gap-3 text-xs font-bold">
                    <div class="flex-1 bg-emerald-50 text-emerald-700 border border-emerald-200 p-3 rounded-xl text-center">
                        <span class="block text-lg font-black">{{ importResult.success_count }}</span>
                        <span>正常取り込み件数</span>
                    </div>
                    <div class="flex-1 bg-rose-50 text-rose-700 border border-rose-200 p-3 rounded-xl text-center">
                        <span class="block text-lg font-black">{{ importResult.error_count }}</span>
                        <span>エラー件数</span>
                    </div>
                </div>

                <!-- エラー詳細リスト (エラーが存在する場合) -->
                <div v-if="importResult.errors && importResult.errors.length > 0" class="space-y-2 flex-1 overflow-y-auto pr-1">
                    <h4 class="text-xs font-bold text-rose-600 flex items-center gap-1">
                        ⚠️ エラー発生行 ({{ importResult.errors.length }}件)
                    </h4>
                    <div class="bg-rose-50/50 border border-rose-100 rounded-xl p-3 space-y-2 text-xs">
                        <div
                            v-for="(err, idx) in importResult.errors"
                            :key="idx"
                            class="flex items-start gap-2 border-b border-rose-100/60 pb-1.5 last:border-0 last:pb-0"
                        >
                            <span class="font-mono font-bold bg-rose-200/80 text-rose-800 px-1.5 py-0.5 rounded text-[10px] shrink-0">
                                {{ err.line }}行目
                            </span>
                            <span class="text-slate-700 font-medium break-all leading-tight">
                                {{ err.reason }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- フッター閉じるボタン -->
                <div class="pt-2 flex justify-end">
                    <button
                        @click="importResult = null"
                        type="button"
                        class="px-4 py-2 text-xs font-bold bg-slate-800 hover:bg-slate-900 text-white rounded-xl shadow-xs cursor-pointer transition-all active:scale-95"
                    >
                        閉じる
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api.js'

const apiEndpoint = '/admin/faqs'

const route = useRoute()
const router = useRouter()

const faqsData = ref(null)
const categoryOptions = ref([]) // カテゴリ一覧
const loading = ref(true)
const exporting = ref(false)
const importing = ref(false)

// インポート結果保持用 state
const importResult = ref(null)

const keywordInput = ref(route.query.keyword || '')
const categoryInput = ref(route.query.category || '')

// データの安全な算出プロパティ
const faqList = computed(() => faqsData.value?.data || [])
const paginationLinks = computed(() => faqsData.value?.links || [])
const lastPage = computed(() => faqsData.value?.last_page || 1)
const exportUrl = computed(() => faqsData.value?.export_url || null)
const storeUrl = computed(() => faqsData.value?.store_url || '/admin/faqs')
const importUrl = computed(() => faqsData.value?.import_url || null)

// 新規作成URLの補正
const getCreateUrl = (url) => {
    if (!url) return '/admin/faqs/create'
    return url.endsWith('/create') ? url : `${url}/create`
}

// CSVエクスポート処理
const handleExport = async () => {
    if (!exportUrl.value || exporting.value) return

    exporting.value = true
    try {
        const response = await api.get(exportUrl.value, {
            responseType: 'blob'
        })

        const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')

        link.href = url
        link.setAttribute('download', 'faq_admin.csv')
        document.body.appendChild(link)
        link.click()

        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('CSVのダウンロードに失敗しました:', error)
        alert('CSVのダウンロードに失敗しました。')
    } finally {
        exporting.value = false
    }
}

// CSVインポート処理
const handleImport = async (event) => {
    const file = event.target.files?.[0]
    if (!file || !importUrl.value || importing.value) return

    if (!confirm(`「${file.name}」を取り込みますか？`)) {
        event.target.value = ''
        return
    }

    importing.value = true
    const formData = new FormData()
    formData.append('file', file)

    try {
        const res = await api.post(importUrl.value, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        // バックエンドから返却されたJSONデータをモーダル用stateに保持
        importResult.value = res.data

        // 一覧の再取得
        fetchFaqs(route.query.page || 1)
    } catch (error) {
        console.error('CSVのインポートに失敗しました:', error)
        alert('CSVのインポート処理でシステムエラーが発生しました。ファイルフォーマットを確認してください。')
    } finally {
        importing.value = false
        event.target.value = ''
    }
}

const handleDelete = async (faq) => {
    // 削除確認アラート
    if (!confirm(`「${faq.question}」を削除してもよろしいですか？`)) {
        return
    }

    // エンドポイントの設定（delete_url があればそれを優先、無ければ apiEndpoint + id）
    const deleteTargetUrl = faq.delete_url || `${apiEndpoint}/${faq.id}`

    try {
        await api.delete(deleteTargetUrl)
        alert('FAQを削除しました。')

        // 削除後に現在のページ（または1ページ目）のリストを再取得
        fetchFaqs(route.query.page || 1)
    } catch (error) {
        console.error('FAQの削除に失敗しました:', error)
        alert('FAQの削除に失敗しました。')
    }
}

// 検索実行処理
const handleSearch = () => {
    const query = { ...route.query }

    if (keywordInput.value.trim()) {
        query.keyword = keywordInput.value.trim()
    } else {
        delete query.keyword
    }

    if (categoryInput.value) {
        query.category = categoryInput.value
    } else {
        delete query.category
    }

    delete query.page
    router.push({ query })
}

// 検索条件クリア
const clearFilters = () => {
    keywordInput.value = ''
    categoryInput.value = ''
    const query = { ...route.query }
    delete query.keyword
    delete query.category
    delete query.page
    router.push({ query })
}

// FAQ一覧データの取得
const fetchFaqs = async (page = 1) => {
    loading.value = true
    try {
        const params = {
            page: page,
            ...route.query
        }
        const res = await api.get(apiEndpoint, { params })

        const data = res.data?.data ? res.data : res

        faqsData.value = data

        if (data.categories) {
            categoryOptions.value = data.categories
        }
    } catch (error) {
        console.error('管理者用FAQの取得に失敗しました:', error)
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

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return dateString
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const formatPaginationLabel = (label) => {
    if (!label) return ''
    if (label.includes('previous') || label.includes('Previous') || label.includes('&laquo;')) return '&laquo; 前へ'
    if (label.includes('next') || label.includes('Next') || label.includes('&raquo;')) return '次へ &raquo;'
    return label
}

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
        return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline font-medium break-all" onclick="event.stopPropagation()">${url}</a>`
    })
}

// URLクエリ監視
watch(
    () => route.query,
    (newQuery) => {
        keywordInput.value = newQuery.keyword || ''
        categoryInput.value = newQuery.category || ''
        fetchFaqs(newQuery.page || 1)
    }
)

onMounted(() => {
    fetchFaqs(route.query.page || 1)
})
</script>