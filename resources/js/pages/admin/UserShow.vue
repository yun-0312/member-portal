<template>
    <div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- ヘッダーエリア -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <!-- 🔙 戻るボタン ＆ タイトル -->
            <div class="flex items-center gap-3">

                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                            {{ user?.name || 'ユーザー詳細' }}
                        </h1>
                        <span
                            v-if="user"
                            :class="getStatusBadgeClass(user.status)"
                            class="px-2.5 py-0.5 rounded-full text-xs font-semibold inline-flex items-center gap-1 ml-2"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>{{ getStatusLabel(user.status) }}</span>
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">ユーザーの基本情報・権限・所属医療機関の確認</p>
                </div>
            </div>

            <!-- アクションボタンエリア -->
            <div v-if="!loading && !error && user" class="flex items-center gap-3">
                <router-link
                    to="/admin/users"
                    class="inline-flex items-center gap-1 px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-all active:scale-95 shadow-2xs cursor-pointer shrink-0"
                    title="ユーザー一覧に戻る"
                >
                    <span>‹</span>
                    <span>ユーザー一覧へ戻る</span>
                </router-link>
                <!-- 編集ボタン -->
                <router-link
                    v-if="updateUrl"
                    :to="getRelativePath(updateUrl)"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-md shadow-indigo-500/20 transition-all active:scale-95 cursor-pointer"
                >
                    <span>✏️</span>
                    <span>編集</span>
                </router-link>

                <!-- 削除ボタン -->
                <button
                    v-if="deleteUrl"
                    @click="handleDelete"
                    :disabled="deleting"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-rose-50 border border-rose-200 text-rose-600 hover:text-rose-700 text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all active:scale-95 cursor-pointer disabled:opacity-50"
                >
                    <span>🗑️</span>
                    <span>{{ deleting ? '削除中...' : '削除' }}</span>
                </button>
            </div>
        </div>
        <!-- ⚠️ 削除エラー表示領域 -->
        <div
            v-if="deleteError"
            class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-medium flex items-center justify-between gap-2 shadow-2xs"
        >
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ deleteError }}</span>
            </div>
            <button
                @click="deleteError = null"
                class="text-rose-400 hover:text-rose-600 text-xs px-2 py-1 rounded-lg hover:bg-rose-100 transition-colors cursor-pointer"
            >
                ✖
            </button>
        </div>

        <!-- ローディング状態 -->
        <div v-if="loading" class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500 shadow-2xs">
            <div class="inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-3"></div>
            <p class="text-sm font-semibold">ユーザー詳細情報を読み込み中...</p>
        </div>

        <!-- エラー状態 -->
        <div v-else-if="error" class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ error }}</span>
        </div>

        <!-- メインコンテンツ -->
        <div v-else-if="user" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 左側 / メインカラム (2列) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 👤 基本情報カード -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6 space-y-4">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span>👤</span>
                        <span>基本情報</span>
                    </h2>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                        <div>
                            <dt class="text-slate-400 font-medium mb-1">ユーザーID</dt>
                            <dd class="text-slate-800 font-mono font-semibold">#{{ user.id }}</dd>
                        </div>

                        <div>
                            <dt class="text-slate-400 font-medium mb-1">権限 (Role)</dt>
                            <dd>
                                <span
                                    :class="getRoleBadgeClass(user.role?.name)"
                                    class="px-2.5 py-1 rounded-md text-xs font-bold tracking-wide uppercase border inline-block"
                                >
                                    {{ user.role?.name || '未割り当て' }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-400 font-medium mb-1">氏名</dt>
                            <dd class="text-slate-900 font-bold text-base">{{ user.name }}</dd>
                        </div>

                        <div>
                            <dt class="text-slate-400 font-medium mb-1">メールアドレス</dt>
                            <dd class="text-slate-800 font-mono break-all">{{ user.email }}</dd>
                        </div>

                        <div>
                            <dt class="text-slate-400 font-medium mb-1">メール確認日時</dt>
                            <dd class="text-slate-700 font-mono">
                                {{ formatDate(user.email_verified_at) || '未確認' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-400 font-medium mb-1">アカウント作成日時</dt>
                            <dd class="text-slate-700 font-mono">{{ formatDate(user.created_at) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- 🏥 所属医療機関情報カード -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6 space-y-4">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span>🏥</span>
                        <span>所属医療機関</span>
                    </h2>

                    <div v-if="user.medical_institution" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">{{ user.medical_institution.name }}</h3>
                                <p class="text-xs text-slate-400 font-mono mt-0.5">ID: #{{ user.medical_institution.id }}</p>
                            </div>
                            <router-link
                                :to="`/admin/medical-institutions/${user.medical_institution.id}`"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 px-2.5 py-1.5 rounded-lg transition-colors"
                            >
                                <span>医療機関詳細</span>
                                <span>→</span>
                            </router-link>
                        </div>

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm pt-2 border-t border-slate-50">
                            <div>
                                <dt class="text-slate-400 font-medium mb-1">郵便番号</dt>
                                <dd class="text-slate-800 font-mono">〒{{ formatPostcode(user.medical_institution.postcode) }}</dd>
                            </div>

                            <div>
                                <dt class="text-slate-400 font-medium mb-1">電話番号</dt>
                                <dd class="text-slate-800 font-mono">{{ user.medical_institution.phone }}</dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-slate-400 font-medium mb-1">所在地</dt>
                                <dd class="text-slate-800">{{ user.medical_institution.address }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div v-else class="text-xs sm:text-sm text-slate-400 italic py-4 text-center">
                        医療機関には所属していません
                    </div>
                </div>
            </div>

            <!-- 右側 / サブカラム (1列) -->
            <div class="space-y-6">
                <!-- 🛡️ 承認情報カード -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6 space-y-4">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span>🛡️</span>
                        <span>承認・ステータス情報</span>
                    </h2>

                    <dl class="space-y-4 text-xs sm:text-sm">
                        <div>
                            <dt class="text-slate-400 font-medium mb-1">ステータス</dt>
                            <dd>
                                <span
                                    :class="getStatusBadgeClass(user.status)"
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span>{{ getStatusLabel(user.status) }}</span>
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-400 font-medium mb-1">承認日時</dt>
                            <dd class="text-slate-800 font-mono font-medium">
                                {{ formatDate(user.approved_at) || '未承認' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-400 font-medium mb-1">承認実行者</dt>
                            <dd v-if="user.approved_by && typeof user.approved_by === 'object'" class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>👤 {{ user.approved_by.name }}</span>
                                    <span
                                        :class="getRoleBadgeClass(user.approved_by.role?.name)"
                                        class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase"
                                    >
                                        {{ user.approved_by.role?.name || 'admin' }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-400 font-mono break-all">{{ user.approved_by.email }}</div>
                            </dd>
                            <dd v-else-if="user.approved_by" class="text-slate-800 font-mono">
                                User ID: #{{ user.approved_by }}
                            </dd>
                            <dd v-else class="text-slate-400 italic">
                                情報なし
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- ⏱️ システム管理情報カード -->
                <div class="bg-slate-50 rounded-2xl border border-slate-200/80 p-5 space-y-3">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">更新履歴</h2>
                    <dl class="space-y-2 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <dt class="text-slate-400">作成日時:</dt>
                            <dd class="font-mono">{{ formatDate(user.created_at) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400">最終更新:</dt>
                            <dd class="font-mono">{{ formatDate(user.updated_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api' // Axiosインスタンス

const route = useRoute()
const router = useRouter()

const user = ref(null)
const updateUrl = ref('')
const deleteUrl = ref('')

const loading = ref(true)
const error = ref(null)
const deleting = ref(false)
const deleteError = ref(null)

// 詳細データの取得
const fetchUserDetail = async () => {
    loading.value = true
    error.value = null

    try {
        const userId = route.params.id
        const response = await api.get(`/admin/users/${userId}`)

        user.value = response.data.user
        updateUrl.value = response.data.update_url || ''
        deleteUrl.value = response.data.delete_url || ''
    } catch (err) {
        console.error('ユーザー詳細の取得失敗:', err)
        error.value = 'ユーザーデータの取得に失敗しました。ユーザーが存在しないか、アクセス権限がありません。'
    } finally {
        loading.value = false
    }
}

// ユーザー削除処理
const handleDelete = async () => {
    if (!deleteUrl.value) return
    deleteError.value = null

    if (!confirm(`「${user.value?.name}」を本当に削除しますか？この操作は取り消せません。`)) {
        return
    }

    deleting.value = true
    try {
        const relativeUrl = getRelativePath(deleteUrl.value)
        await api.delete(relativeUrl)
        alert('ユーザーを削除しました。')
        router.push('/admin/users')
    } catch (err) {
        console.error('ユーザー削除失敗:', err)

        const serverMessage = err.response?.data?.message
        deleteError.value = serverMessage || '削除処理に失敗しました。時間をおいて再度お試しください。'
    } finally {
        deleting.value = false
    }
}

// フルURLから相対パスを抽出
const getRelativePath = (fullUrl) => {
    if (!fullUrl) return ''
    return fullUrl.replace(/^https?:\/\/[^\/]+(\/api)?/, '')
}

// 日時フォーマット (YYYY-MM-DD HH:mm)
const formatDate = (dateStr) => {
    if (!dateStr) return ''
    const d = new Date(dateStr)
    if (isNaN(d.getTime())) return dateStr

    const yyyy = d.getFullYear()
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    const hh = String(d.getHours()).padStart(2, '0')
    const min = String(d.getMinutes()).padStart(2, '0')

    return `${yyyy}-${mm}-${dd} ${hh}:${min}`
}

// 郵便番号フォーマット
const formatPostcode = (code) => {
    if (!code) return ''
    const str = String(code).replace(/-/g, '')
    if (str.length === 7) {
        return `${str.slice(0, 3)}-${str.slice(3)}`
    }
    return code
}

// ロールバッジのスタイル定義
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

// ステータス表示ラベル
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

// ステータスバッジのスタイル定義
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 1:
            return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
        case 0:
            return 'bg-amber-50 text-amber-700 border border-amber-200'
        case -1:
            return 'bg-rose-50 text-rose-700 border border-rose-200'
        default:
            return 'bg-slate-100 text-slate-600 border-slate-200'
    }
}

onMounted(() => {
    fetchUserDetail()
})
</script>