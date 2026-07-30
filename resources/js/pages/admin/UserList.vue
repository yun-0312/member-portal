<template>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- ヘッダーエリア -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <!-- 🔙 タイトル ＆ 戻るボタンエリア -->
            <div class="flex items-center gap-3">

                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">ユーザー一覧</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">システム利用ユーザーの参照および権限・所属の管理</p>
                </div>
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

                <!-- 未承認一覧ボタン -->
                <router-link
                    v-if="paginationData?.pending_url"
                    to="/admin/users/pending"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all active:scale-95 cursor-pointer"
                >
                    <span>⏳</span>
                    <span>未承認一覧</span>
                </router-link>

                <!-- CSV出力ボタン -->
                <button
                    v-if="paginationData?.export_url"
                    @click="exportData"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all active:scale-95 cursor-pointer"
                >
                    <span>📥</span>
                    <span>CSV出力</span>
                </button>

                <!-- ➕ 新規登録ボタン (追加) -->
                <router-link
                    v-if="paginationData?.store_url"
                    :to="getRelativePath(paginationData.store_url)"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all active:scale-95 cursor-pointer"
                >
                    <span>＋</span>
                    <span>新規登録</span>
                </router-link>
            </div>
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
                        placeholder="氏名、メールアドレス、所属医療機関名で検索..."
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
            <p class="text-sm font-semibold">ユーザーデータを読み込み中...</p>
        </div>

        <!-- エラー状態 -->
        <div v-else-if="error" class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ error }}</span>
        </div>

        <!-- メインコンテンツ -->
        <div v-else class="space-y-4">
            <!-- テーブルカード -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold tracking-wider uppercase">
                                <th class="py-3.5 px-4">ID</th>
                                <th class="py-3.5 px-4">氏名 / メールアドレス</th>
                                <th class="py-3.5 px-4">権限 (Role)</th>
                                <th class="py-3.5 px-4">所属医療機関</th>
                                <th class="py-3.5 px-4">ステータス</th>
                                <th class="py-3.5 px-4 text-right">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs sm:text-sm text-slate-700">
                            <tr
                                v-for="user in users"
                                :key="user.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <!-- ID -->
                                <td class="py-4 px-4 font-mono font-medium text-slate-400">
                                    #{{ user.id }}
                                </td>

                                <!-- 氏名 / メールアドレス -->
                                <td class="py-4 px-4">
                                    <span class="font-bold text-slate-900 block">{{ user.name }}</span>
                                    <span class="text-xs text-slate-400 font-mono">{{ user.email }}</span>
                                </td>

                                <!-- 権限 (Role) -->
                                <td class="py-4 px-4">
                                    <span
                                        :class="getRoleBadgeClass(user.role?.name)"
                                        class="px-2.5 py-1 rounded-md text-xs font-bold tracking-wide uppercase border inline-block"
                                    >
                                        {{ user.role?.name || 'Unassigned' }}
                                    </span>
                                </td>

                                <!-- 所属医療機関 -->
                                <td class="py-4 px-4">
                                    <div v-if="user.medical_institution" class="space-y-0.5">
                                        <div class="font-semibold text-slate-800 flex items-center gap-1">
                                            <span>🏥 {{ user.medical_institution.name }}</span>
                                        </div>
                                        <div class="text-xs text-slate-400 line-clamp-1 truncate" :title="user.medical_institution.address">
                                            {{ user.medical_institution.address }}
                                        </div>
                                    </div>
                                    <span v-else class="text-xs text-slate-400 italic">未所属</span>
                                </td>

                                <!-- ステータス -->
                                <td class="py-4 px-4">
                                    <span
                                        :class="getStatusBadgeClass(user.status)"
                                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold inline-flex items-center gap-1"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        <span>{{ getStatusLabel(user.status) }}</span>
                                    </span>
                                </td>

                                <!-- 詳細アクション -->
                                <td class="py-4 px-4 text-right">
                                    <router-link
                                        v-if="user.show_url"
                                        :to="getRelativePath(user.show_url)"
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
                <div v-if="users.length === 0" class="p-12 text-center text-slate-400 text-sm">
                    該当するユーザーが見つかりませんでした。
                </div>
            </div>

            <!-- ペジネーション -->
            <div v-if="paginationData && paginationData.links" class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                <!-- 件数情報 -->
                <div class="text-xs text-slate-500">
                    全 <span class="font-bold text-slate-800">{{ paginationData.total }}</span> 件中
                    <span class="font-bold text-slate-800">{{ paginationData.from || 0 }}</span> -
                    <span class="font-bold text-slate-800">{{ paginationData.to || 0 }}</span> 件を表示
                </div>

                <!-- ページ移動ボタン -->
                <nav class="flex items-center gap-1">
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
import api from '../../api' // Axiosインスタンス

const users = ref([])
const paginationData = ref(null)
const loading = ref(true)
const error = ref(null)

// 検索キーワード用の状態
const searchKeyword = ref('')

// データの取得処理
const fetchData = async (targetUrl = '/admin/users') => {
    loading.value = true
    error.value = null

    try {
        const config = {}
        if (searchKeyword.value.trim() && !targetUrl.includes('keyword=')) {
            config.params = { keyword: searchKeyword.value.trim() }
        }

        // URLがフルパスで来る場合に対応 (例: http://localhost/api/admin/users?page=2)
        const relativeUrl = targetUrl.replace(/^https?:\/\/[^\/]+(\/api)?/, '')

        const response = await api.get(relativeUrl, config)

        users.value = response.data.data || []
        paginationData.value = response.data
    } catch (err) {
        console.error('ユーザー一覧の取得失敗:', err)
        error.value = 'ユーザーデータの取得に失敗しました。時間をおいて再試行してください。'
    } finally {
        loading.value = false
    }
}

// 検索ボタンクリック時のハンドラ
const handleSearch = () => {
    fetchData('/admin/users')
}

// 検索クリア（リセット）
const clearSearch = () => {
    searchKeyword.value = ''
    fetchData('/admin/users')
}

// show_url などのフルURLからVue Router用の相対パスを抽出
const getRelativePath = (fullUrl) => {
    if (!fullUrl) return ''
    return fullUrl.replace(/^https?:\/\/[^\/]+(\/api)?/, '')
}

// ペジネーションラベルの日本語化
const formatPaginationLabel = (label) => {
    if (!label) return ''
    if (label.includes('previous') || label.includes('前')) return '‹ 前へ'
    if (label.includes('next') || label.includes('次')) return '次へ ›'
    return label
}

// ロールに応じたバッジスタイル
const getRoleBadgeClass = (roleName) => {
    switch (roleName?.toLowerCase()) {
        case 'admin':
        case 'administrator':
            return 'bg-rose-50 text-rose-700 border-rose-200'
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

// ステータスに応じたラベル表記
const getStatusLabel = (status) => {
    switch (status) {
        case 1:
            return '有効'
        case 0:
            return '保留・未承認'
        case -1:
            return '停止中'
        default:
            return '不明'
    }
}

// ステータスに応じたバッジスタイル
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 1:
            return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
        case 0:
            return 'bg-amber-50 text-amber-700 border border-amber-200'
        case -1:
            return 'bg-rose-50 text-rose-700 border border-rose-200'
        default:
            return 'bg-slate-100 text-slate-600 border border-slate-200'
    }
}

// CSVエクスポート処理
const exportData = () => {
    const exportUrl = paginationData.value?.export_url
    if (!exportUrl) return

    const fullUrl = exportUrl.startsWith('http')
        ? exportUrl
        : `/api${exportUrl.startsWith('/') ? '' : '/'}${exportUrl}`

    const link = document.createElement('a')
    link.href = fullUrl
    link.setAttribute('download', '')
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

onMounted(() => {
    fetchData()
})
</script>