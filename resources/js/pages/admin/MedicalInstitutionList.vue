<template>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- ヘッダーエリア -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">医療機関一覧</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">登録されている医療機関および代表者情報の管理</p>
            </div>

            <!-- アクションボタンエリア -->
            <div class="flex items-center gap-3">
                <router-link
                    to="/admin/management"
                    class="inline-flex items-center gap-1 px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-all active:scale-95 shadow-2xs cursor-pointer shrink-0"
                    title="管理トップに戻る"
                >
                    <span>‹</span>
                    <span>管理画面へ戻る</span>
                </router-link>
                <!-- エクスポートボタン -->
                <button
                    v-if="paginationData?.export_url"
                    @click="exportData"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all active:scale-95 cursor-pointer"
                >
                    <span>📥</span>
                    <span>CSV出力</span>
                </button>

                <!-- 新規登録ボタン -->
                <router-link
                    v-if="paginationData?.store_url"
                    :to="paginationData.store_url"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-md shadow-indigo-500/20 transition-all active:scale-95"
                >
                    <span>➕</span>
                    <span>新規医療機関登録</span>
                </router-link>
            </div>
        </div>

        <!--  成功メッセージ表示エリア -->
        <div
            v-if="successMessage"
            class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs sm:text-sm font-medium flex items-center justify-between shadow-2xs"
        >
            <div class="flex items-center gap-2.5">
                <span class="text-base">✅</span>
                <span>{{ successMessage }}</span>
            </div>
            <!-- 閉じるボタン -->
            <button
                @click="successMessage = ''"
                class="text-emerald-500 hover:text-emerald-700 font-bold p-1 rounded-lg transition-colors cursor-pointer"
            >
                ✕
            </button>
        </div>

        <!-- 🔍 検索フォームエリア -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        🔍
                    </div>
                    <input
                        v-model="searchKeyword"
                        type="text"
                        placeholder="医療機関名、郵便番号、住所、電話番号、代表者名で検索..."
                        class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 text-xs sm:text-sm rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                    />
                    <!-- 入力クリアボタン -->
                    <button
                        v-if="searchKeyword"
                        type="button"
                        @click="clearSearch"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs cursor-pointer"
                    >
                        ✖
                    </button>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button
                        type="submit"
                        class="w-full sm:w-auto px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs sm:text-sm font-semibold rounded-xl transition-all active:scale-95 cursor-pointer"
                    >
                        検索
                    </button>
                    <button
                        v-if="searchKeyword"
                        type="button"
                        @click="clearSearch"
                        class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs sm:text-sm font-semibold rounded-xl transition-all cursor-pointer whitespace-nowrap"
                    >
                        リセット
                    </button>
                </div>
            </form>
        </div>

        <!-- ローディング状態 -->
        <div v-if="loading" class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500 shadow-2xs">
            <div class="inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-3"></div>
            <p class="text-sm font-semibold">医療機関データを読み込み中...</p>
        </div>

        <!-- エラー状態 -->
        <div v-else-if="error" class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ error }}</span>
        </div>

        <!-- メインコンテンツ -->
        <div v-else class="space-y-4">
            <!-- 📱 スマホ表示: カード型レイアウト (sm未満で表示) -->
            <div class="block sm:hidden space-y-3">
                <div
                    v-for="item in institutions"
                    :key="item.id"
                    class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs space-y-3"
                >
                    <!-- ヘッダー: 医療機関名・ID -->
                    <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-2.5">
                        <div class="space-y-0.5">
                            <span class="font-mono text-xs text-slate-400">#{{ item.id }}</span>
                            <h3 class="font-bold text-slate-900 text-base leading-snug">{{ item.name }}</h3>
                        </div>
                    </div>

                    <!-- ボディ: 住所・連絡先 -->
                    <div class="space-y-1.5 text-xs text-slate-600">
                        <div class="flex items-start gap-1.5">
                            <span class="shrink-0">📍</span>
                            <div>
                                <span class="font-mono text-slate-400 mr-1">〒{{ formatPostcode(item.postcode) }}</span>
                                <span>{{ item.address }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="shrink-0">📞</span>
                            <span class="font-mono text-slate-700 font-medium">{{ item.phone }}</span>
                        </div>
                    </div>

                    <!-- 代表者情報エリア -->
                    <div class="bg-slate-50 p-2.5 rounded-xl text-xs space-y-1 border border-slate-100">
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block">代表者</span>
                        <div v-if="item.representative" class="space-y-1">
                            <div class="font-semibold text-slate-800 flex items-center justify-between gap-2">
                                <span>👤 {{ item.representative.name }}</span>
                                <!-- ロールバッジ -->
                                <span
                                    :class="getRoleBadgeClass(item.representative.role?.name)"
                                    class="px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase border shrink-0"
                                >
                                    {{ item.representative.role?.name || 'Unassigned' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 font-mono break-all">
                                {{ item.representative.email }}
                            </p>
                        </div>
                        <span v-else class="text-slate-400 italic block">未設定</span>
                    </div>

                    <!-- フッター: 操作アクション -->
                    <div v-if="item.show_url" class="pt-2 border-t border-slate-100 flex justify-end">
                        <router-link
                            :to="item.show_url"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors"
                        >
                            <span>詳細を見る</span>
                            <span>→</span>
                        </router-link>
                    </div>
                </div>

                <!-- 件数ゼロ時 -->
                <div v-if="institutions.length === 0" class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-sm">
                    該当する医療機関が見つかりませんでした。
                </div>
            </div>

            <!-- 💻 PC表示: テーブルレイアウト (sm以上で表示) -->
            <div class="hidden sm:block bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold tracking-wider uppercase">
                                <th class="py-3.5 px-4">ID</th>
                                <th class="py-3.5 px-4">医療機関名</th>
                                <th class="py-3.5 px-4">郵便番号 / 住所</th>
                                <th class="py-3.5 px-4">電話番号</th>
                                <th class="py-3.5 px-4">代表者</th>
                                <th class="py-3.5 px-4 text-right">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs sm:text-sm text-slate-700">
                            <tr
                                v-for="item in institutions"
                                :key="item.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <!-- ID -->
                                <td class="py-4 px-4 font-mono font-medium text-slate-400">
                                    #{{ item.id }}
                                </td>

                                <!-- 医療機関名 -->
                                <td class="py-4 px-4">
                                    <span class="font-bold text-slate-900 block">{{ item.name }}</span>
                                </td>

                                <!-- 郵便番号 / 住所 -->
                                <td class="py-4 px-4 max-w-xs">
                                    <div class="text-xs text-slate-400 font-mono">〒{{ formatPostcode(item.postcode) }}</div>
                                    <div class="text-slate-800 line-clamp-1 truncate" :title="item.address">{{ item.address }}</div>
                                </td>

                                <!-- 電話番号 -->
                                <td class="py-4 px-4 font-mono text-slate-600">
                                    {{ item.phone }}
                                </td>

                                <!-- 代表者情報 -->
                                <td class="py-4 px-4">
                                    <div v-if="item.representative" class="space-y-1">
                                        <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                            <span>👤 {{ item.representative.name }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <!-- ロールバッジ -->
                                            <span
                                                :class="getRoleBadgeClass(item.representative.role?.name)"
                                                class="px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase border"
                                            >
                                                {{ item.representative.role?.name || 'Unassigned' }}
                                            </span>
                                            <span class="text-[11px] text-slate-400 truncate max-w-[140px]" :title="item.representative.email">
                                                {{ item.representative.email }}
                                            </span>
                                        </div>
                                    </div>
                                    <span v-else class="text-xs text-slate-400 italic">未設定</span>
                                </td>

                                <!-- 詳細アクション -->
                                <td class="py-4 px-4 text-right">
                                    <router-link
                                        v-if="item.show_url"
                                        :to="item.show_url"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 px-2.5 py-1.5 rounded-lg transition-colors"
                                    >
                                        <span>詳細</span>
                                        <span>→</span>
                                    </router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 件数ゼロ時の表示 -->
                <div v-if="institutions.length === 0" class="p-12 text-center text-slate-400 text-sm">
                    該当する医療機関が見つかりませんでした。
                </div>
            </div>

            <!-- 📄 レスポンシブ対応ペジネーション -->
            <div v-if="paginationData && paginationData.links" class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                <!-- 件数情報 -->
                <div class="text-xs text-slate-500 text-center sm:text-left">
                    全 <span class="font-bold text-slate-800">{{ paginationData.total }}</span> 件中
                    <span class="font-bold text-slate-800">{{ paginationData.from || 0 }}</span> -
                    <span class="font-bold text-slate-800">{{ paginationData.to || 0 }}</span> 件を表示
                </div>

                <!--  スマホ表示: 「前へ」「次へ」だけのシンプルナビゲーション -->
                <div class="flex sm:hidden items-center justify-between gap-3 w-full">
                    <button
                        @click="fetchData(getPrevPageUrl())"
                        :disabled="!getPrevPageUrl()"
                        :class="[
                            'flex-1 py-2 px-4 text-xs font-semibold rounded-xl border transition-all text-center',
                            getPrevPageUrl()
                                ? 'bg-white text-slate-700 border-slate-300 active:bg-slate-100 cursor-pointer shadow-2xs'
                                : 'bg-slate-50 text-slate-300 border-slate-200 cursor-not-allowed'
                        ]"
                    >
                        ‹ 前のページ
                    </button>

                    <button
                        @click="fetchData(getNextPageUrl())"
                        :disabled="!getNextPageUrl()"
                        :class="[
                            'flex-1 py-2 px-4 text-xs font-semibold rounded-xl border transition-all text-center',
                            getNextPageUrl()
                                ? 'bg-white text-slate-700 border-slate-300 active:bg-slate-100 cursor-pointer shadow-2xs'
                                : 'bg-slate-50 text-slate-300 border-slate-200 cursor-not-allowed'
                        ]"
                    >
                        次のページ ›
                    </button>
                </div>

                <!--  PC表示: 数字つきのフルナビゲーション (sm以上で表示) -->
                <nav class="hidden sm:flex items-center gap-1 flex-wrap justify-end">
                    <button
                        v-for="(link, index) in paginationData.links"
                        :key="index"
                        @click="fetchData(link.url)"
                        :disabled="!link.url || link.active"
                        :class="[
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all',
                            link.active
                                ? 'bg-indigo-600 text-white shadow-sm'
                                : link.url
                                    ? 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 cursor-pointer'
                                    : 'bg-slate-50 text-slate-300 border border-slate-100 cursor-not-allowed'
                        ]"
                    >
                        {{ formatPaginationLabel(link.label) }}
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../api'

const route = useRoute()

const institutions = ref([])
const paginationData = ref(null)
const loading = ref(true)
const error = ref(null)

const successMessage = ref('')

// 検索キーワード用の状態
const searchKeyword = ref('')

// データの取得処理
const fetchData = async (targetUrl = '/admin/medical-institutions') => {
    loading.value = true
    error.value = null

    try {
        // パラメータの設定（すでにURLにクエリパラメータが含まれている場合に対応）
        const config = {}
        if (searchKeyword.value.trim() && !targetUrl.includes('keyword=')) {
            config.params = { keyword: searchKeyword.value.trim() }
        }

        const response = await api.get(targetUrl, config)

        // 配列データの抽出
        institutions.value = response.data.data || []

        // ペジネーションメタデータの保持
        paginationData.value = response.data
    } catch (err) {
        console.error('医療機関一覧の取得失敗:', err)
        error.value = '医療機関データの取得に失敗しました。時間をおいて再試行してください。'
    } finally {
        loading.value = false
    }
}

// 検索ボタンクリック時のハンドラ
const handleSearch = () => {
    // 検索時は1ページ目から取得するため初期URLで発火
    fetchData('/admin/medical-institutions')
}

// 検索クリア（リセット）
const clearSearch = () => {
    searchKeyword.value = ''
    fetchData('/admin/medical-institutions')
}

// 郵便番号フォーマット（例: 4001736 -> 400-1736）
const formatPostcode = (code) => {
    if (!code) return ''
    const str = String(code).replace(/-/g, '')
    if (str.length === 7) {
        return `${str.slice(0, 3)}-${str.slice(3)}`
    }
    return code
}

// ペジネーションラベルの日本語化
const formatPaginationLabel = (label) => {
    if (!label) return ''
    if (label.includes('previous') || label.includes('前')) return '‹ 前へ'
    if (label.includes('next') || label.includes('次')) return '次へ ›'
    return label
}

// スマホ用: 「前へ」ボタンのURL取得
const getPrevPageUrl = () => {
    if (!paginationData.value?.links) return null
    const prevLink = paginationData.value.links.find(
        link => link.label.includes('previous') || link.label.includes('前')
    )
    return prevLink ? prevLink.url : null
}

// スマホ用: 「次へ」ボタンのURL取得
const getNextPageUrl = () => {
    if (!paginationData.value?.links) return null
    const nextLink = paginationData.value.links.find(
        link => link.label.includes('next') || link.label.includes('次')
    )
    return nextLink ? nextLink.url : null
}

// 代表者のロールに応じたバッジスタイルの適用
const getRoleBadgeClass = (roleName) => {
    switch (roleName?.toLowerCase()) {
        case 'director':
            return 'bg-purple-50 text-purple-700 border-purple-200'
        case 'medical_staff':
            return 'bg-blue-50 text-blue-700 border-blue-200'
        case 'member':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200'
        default:
            return 'bg-slate-100 text-slate-600 border-slate-200'
    }
}

// CSVエクスポート処理
const exportData = () => {
    const exportUrl = paginationData.value?.export_url
    if (!exportUrl) return

    // フルURLの組み立て (/api プレフィックスの補正)
    const fullUrl = exportUrl.startsWith('http')
        ? exportUrl
        : `/api${exportUrl.startsWith('/') ? '' : '/'}${exportUrl}`

    // 画面遷移（window.open）させず、非表示の<a>タグを作ってダウンロード発火
    const link = document.createElement('a')
    link.href = fullUrl
    link.setAttribute('download', '') // バックエンド側の Content-Disposition ヘッダー（ファイル名）を優先
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

onMounted(() => {
    if (route.query.message) {
        successMessage.value = route.query.message
        window.history.replaceState({}, '', window.location.pathname)
    }

    fetchData()
})
</script>