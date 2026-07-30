<template>
    <div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- ヘッダーエリア -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div class="flex items-center gap-3">

                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                        {{ institution?.name || '医療機関詳細' }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">ID: #{{ institution?.id }} の基本情報および代表者設定</p>
                </div>
            </div>

            <!-- アクションボタン -->
            <div v-if="data" class="flex items-center gap-2.5">
                <router-link
                    to="/admin/medical-institutions"
                    class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-colors text-sm"
                    title="一覧に戻る"
                >
                    ‹ 医療機関一覧に戻る
                </router-link>
                <!-- 関連ユーザー一覧 -->
                <router-link
                    v-if="data.users_url"
                    :to="data.users_url"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all active:scale-95"
                >
                    <span>👥</span>
                    <span>所属ユーザー</span>
                </router-link>

                <!-- 編集ボタン -->
                <router-link
                    v-if="data.edit_url"
                    :to="data.edit_url"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-md shadow-indigo-500/20 transition-all active:scale-95"
                >
                    <span>✏️</span>
                    <span>編集</span>
                </router-link>

                <!-- 削除ボタン -->
                <button
                    v-if="data.delete_url"
                    @click="handleDelete"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs sm:text-sm font-semibold rounded-xl transition-all active:scale-95 cursor-pointer"
                >
                    <span>🗑️</span>
                    <span>削除</span>
                </button>
            </div>
        </div>

        <!-- 削除失敗時のエラーメッセージエリア -->
        <div
            v-if="deleteError"
            class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl shadow-2xs space-y-2 animate-fade-in"
        >
            <div class="flex items-center gap-2 font-bold text-sm sm:text-base">
                <span class="text-lg">🚫</span>
                <span>{{ deleteError }}</span>
            </div>

            <!-- 所属ユーザー一覧のリスト表示（バックエンドから users が返ってきた場合） -->
            <div v-if="existingUsers.length > 0" class="pl-7 text-xs text-rose-700 space-y-1">
                <p class="font-semibold">【所属しているユーザー】</p>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <span
                        v-for="(username, index) in existingUsers"
                        :key="index"
                        class="px-2 py-1 bg-white/80 border border-rose-200 rounded-lg font-medium text-rose-900"
                    >
                        👤 {{ username }}
                    </span>
                </div>
                <p class="text-[11px] text-rose-500 pt-1">※ 医療機関を削除するには、先にこれらのユーザーの所属解除またはアカウント削除を行ってください。</p>
            </div>
        </div>

        <!-- ローディング表示 -->
        <div v-if="loading" class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500 shadow-2xs">
            <div class="inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-3"></div>
            <p class="text-sm font-semibold">医療機関データを読み込み中...</p>
        </div>

        <!-- エラー表示 -->
        <div v-else-if="error" class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ error }}</span>
        </div>

        <!-- メインコンテンツ -->
        <div v-else-if="institution" class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- 左側（2列分）: 医療機関基本情報 -->
            <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <span>🏥</span>
                        <span>医療機関 基本情報</span>
                    </h2>
                </div>

                <div class="p-6 space-y-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs sm:text-sm">
                        <!-- ID -->
                        <div class="sm:col-span-1 text-slate-500 font-medium">医療機関ID</div>
                        <div class="sm:col-span-2 text-slate-800 font-mono font-bold">#{{ institution.id }}</div>

                        <hr class="sm:col-span-3 border-slate-100" />

                        <!-- 医療機関名 -->
                        <div class="sm:col-span-1 text-slate-500 font-medium">医療機関名</div>
                        <div class="sm:col-span-2 text-slate-900 font-bold text-base">{{ institution.name }}</div>

                        <hr class="sm:col-span-3 border-slate-100" />

                        <!-- 郵便番号 -->
                        <div class="sm:col-span-1 text-slate-500 font-medium">郵便番号</div>
                        <div class="sm:col-span-2 text-slate-800 font-mono">〒{{ formatPostcode(institution.postcode) }}</div>

                        <hr class="sm:col-span-3 border-slate-100" />

                        <!-- 住所 -->
                        <div class="sm:col-span-1 text-slate-500 font-medium">住所</div>
                        <div class="sm:col-span-2 text-slate-800 leading-relaxed">{{ institution.address }}</div>

                        <hr class="sm:col-span-3 border-slate-100" />

                        <!-- 電話番号 -->
                        <div class="sm:col-span-1 text-slate-500 font-medium">電話番号</div>
                        <div class="sm:col-span-2 text-slate-800 font-mono font-semibold">{{ institution.phone }}</div>

                        <hr class="sm:col-span-3 border-slate-100" />

                        <!-- 登録日時 -->
                        <div class="sm:col-span-1 text-slate-500 font-medium">登録日時</div>
                        <div class="sm:col-span-2 text-slate-600 font-mono text-xs">{{ formatDate(institution.created_at) }}</div>

                        <!-- 更新日時 -->
                        <div class="sm:col-span-1 text-slate-500 font-medium">最終更新日時</div>
                        <div class="sm:col-span-2 text-slate-600 font-mono text-xs">{{ formatDate(institution.updated_at) }}</div>
                    </dl>
                </div>
            </div>

            <!-- 右側（1列分）: 代表者情報カード -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <span>👤</span>
                            <span>代表者情報</span>
                        </h2>
                    </div>

                    <div class="p-6">
                        <div v-if="institution.representative" class="space-y-4">
                            <!-- ユーザー名＆ロール -->
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-lg font-bold text-slate-900">{{ institution.representative.name }}</span>
                                    <span
                                        :class="getRoleBadgeClass(institution.representative.role?.name)"
                                        class="px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase border"
                                    >
                                        {{ institution.representative.role?.name || 'Unassigned' }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 font-mono break-all">
                                    {{ institution.representative.email }}
                                </div>
                            </div>

                            <hr class="border-slate-100" />

                            <!-- 属性情報一覧 -->
                            <dl class="space-y-2.5 text-xs">
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">ユーザーID</dt>
                                    <dd class="font-mono text-slate-800 font-bold">#{{ institution.representative.id }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">ステータス</dt>
                                    <dd>
                                        <span v-if="institution.representative.status === 1" class="text-emerald-600 font-bold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 有効
                                        </span>
                                        <span v-else class="text-slate-400">無効</span>
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">メール確認日</dt>
                                    <dd class="text-slate-700 font-mono text-[11px]">{{ formatDate(institution.representative.email_verified_at) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">承認日時</dt>
                                    <dd class="text-slate-700 font-mono text-[11px]">{{ formatDate(institution.representative.approved_at) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- 代表者未設定時 -->
                        <div v-else class="text-center py-8 text-slate-400 text-xs">
                            代表者情報が設定されていません。
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api' // 作成済みのAxiosインスタンスのパスに合わせて調整してください

const route = useRoute()
const router = useRouter()

const data = ref(null)
const loading = ref(true)
const error = ref(null)

// 削除時のエラーメッセージ＆所属ユーザー用
const deleteError = ref(null)
const existingUsers = ref([])

// 医療機関データオブジェクトのショートカット
const institution = computed(() => data.value?.institution || null)

// 詳細データの取得
const fetchDetail = async () => {
    loading.value = true
    error.value = null

    try {
        const id = route.params.id
        const response = await api.get(`/admin/medical-institutions/${id}`)
        data.value = response.data
    } catch (err) {
        console.error('医療機関詳細の取得失敗:', err)
        error.value = '医療機関詳細データの取得に失敗しました。'
    } finally {
        loading.value = false
    }
}

// 削除処理のハンドラ
const handleDelete = async () => {
    if (!data.value?.delete_url) return

    // エラー表示のリセット
    deleteError.value = null
    existingUsers.value = []

    if (confirm(`医療機関「${institution.value?.name}」を削除してもよろしいですか？`)) {
        try {
            await api.delete(data.value.delete_url)
            alert('削除が完了しました。')
            router.push('/admin/medical-institutions')
        } catch (err) {
            console.error('削除失敗:', err)

            // 💡 422エラー（所属メンバーが存在する場合等）のハンドリング
            if (err.response && err.response.status === 422) {
                deleteError.value = err.response.data.message || 'この医療機関は削除できません。'
                existingUsers.value = err.response.data.users || []
            } else {
                deleteError.value = '削除処理に失敗しました。サーバーエラーが発生した可能性があります。'
            }
        }
    }
}

// 郵便番号フォーマット（例: 8608078 -> 860-8078）
const formatPostcode = (code) => {
    if (!code) return ''
    const str = String(code).replace(/-/g, '')
    if (str.length === 7) {
        return `${str.slice(0, 3)}-${str.slice(3)}`
    }
    return code
}

// 日時フォーマット (例: 2026-01-30T05:31:33.000000Z -> 2026/01/30 14:31)
const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    const d = new Date(dateStr)
    if (isNaN(d.getTime())) return dateStr

    const yyyy = d.getFullYear()
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    const hh = String(d.getHours()).padStart(2, '0')
    const mi = String(d.getMinutes()).padStart(2, '0')

    return `${yyyy}/${mm}/${dd} ${hh}:${mi}`
}

// ロールバッジのクラス指定
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

onMounted(() => {
    fetchDetail()
})
</script>