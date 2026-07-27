<template>
    <div class="min-h-screen bg-slate-50 p-4 md:p-8 text-slate-800">
        <div class="max-w-6xl mx-auto space-y-6">

        <!-- 1. ヘッダーエリア -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">

            <div class="space-y-1">
                <div class="flex items-center gap-2">
                <span class="text-2xl">🏥</span>
                <h1 class="text-xl md:text-2xl font-extrabold text-slate-800 tracking-tight">
                    {{ currentInstitution?.name || '医療機関ユーザー管理' }}
                </h1>
                </div>

                <div v-if="currentInstitution" class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 pt-1">
                <span v-if="currentInstitution.address" class="flex items-center gap-1">
                    📍 {{ currentInstitution.address }}
                </span>
                <span v-if="currentInstitution.phone" class="flex items-center gap-1">
                    📞 {{ currentInstitution.phone }}
                </span>
                </div>
            </div>

            <button
                @click="fetchUsers"
                :disabled="loading"
                class="self-start inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all active:scale-95 disabled:opacity-50"
            >
                <span :class="{ 'animate-spin': loading }">🔄</span>
                <span>再読み込み</span>
            </button>
            </div>
        </div>

        <!-- 2. アラートメッセージ -->
        <div v-if="successMessage" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs md:text-sm font-medium flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
            <span>✅</span>
            <span>{{ successMessage }}</span>
            </div>
            <button @click="successMessage = ''" class="text-emerald-500 hover:text-emerald-700 font-bold">✕</button>
        </div>

        <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs md:text-sm font-medium flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ errorMessage }}</span>
            </div>
            <button @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 font-bold">✕</button>
        </div>

        <!-- 3. メインコンテンツ（ユーザーテーブル） -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

            <div v-if="loading" class="p-12 text-center space-y-3">
            <div class="w-8 h-8 border-4 border-sky-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="text-xs text-slate-500 font-medium">ユーザーデータを読み込み中…</p>
            </div>

            <div v-else-if="users.length === 0" class="p-12 text-center text-slate-400 text-xs">
            所属ユーザーが見つかりません。
            </div>

            <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-bold text-slate-500 tracking-wider">
                    <th class="py-3.5 px-4">ユーザー</th>
                    <th class="py-3.5 px-4">ロール</th>
                    <th class="py-3.5 px-4">ステータス</th>
                    <th class="py-3.5 px-4">登録日時</th>
                    <th class="py-3.5 px-4 text-right">操作</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                <tr
                    v-for="user in users"
                    :key="user.id"
                    class="hover:bg-slate-50/80 transition-colors"
                    :class="{ 'bg-amber-50/40': user.is_pending }"
                >
                    <!-- ユーザー情報 -->
                    <td class="py-4 px-4">
                    <div class="font-bold text-slate-800 text-sm">{{ user.name }}</div>
                    <div class="text-slate-500 text-[11px]">{{ user.email }}</div>
                    </td>

                    <!-- ロール -->
                    <td class="py-4 px-4">
                    <span class="inline-block px-2.5 py-1 bg-slate-100 border border-slate-200 text-slate-700 font-bold text-[10px] rounded-md">
                        {{ user.role?.name || '未設定' }}
                    </span>
                    </td>

                    <!-- ステータス -->
                    <td class="py-4 px-4">
                    <span 
                        v-if="user.is_pending" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-700 font-bold text-[10px] rounded-full"
                    >
                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                        承認待ち
                    </span>
                    <span 
                        v-else 
                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-[10px] rounded-full"
                    >
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                        承認済み
                    </span>
                    </td>

                    <!-- 登録日時 -->
                    <td class="py-4 px-4 text-slate-500 text-[11px]">
                    {{ formatDate(user.created_at) }}
                    </td>

                    <!-- 操作ボタン -->
                    <td class="py-4 px-4 text-right">
                    <div class="flex items-center justify-end gap-2">

                        <!-- 承認待ちのアクション -->
                        <template v-if="user.is_pending">
                        <button
                            @click="handleApprove(user)"
                            :disabled="actionLoadingId === user.id"
                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded-lg shadow-sm transition-all active:scale-95 disabled:opacity-50"
                        >
                            承認
                        </button>

                        <button
                            @click="handleReject(user)"
                            :disabled="actionLoadingId === user.id"
                            class="px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white font-bold text-[11px] rounded-lg shadow-sm transition-all active:scale-95 disabled:opacity-50"
                        >
                            却下
                        </button>
                        </template>

                        <!-- 💡 show_url (相対パス: 例 "/users/show/113") を直接 SPA のリンクとして利用 -->
                        <router-link
                        v-if="user.show_url"
                        :to="user.show_url"
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-[11px] rounded-lg transition-colors"
                        >
                        詳細
                        </router-link>
                    </div>
                    </td>
                </tr>
                </tbody>
            </table>
            </div>

        </div>

        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../api.js'

const route = useRoute()
const institutionId = route.params.id

const users = ref([])
const loading = ref(false)
const actionLoadingId = ref(null)
const successMessage = ref('')
const errorMessage = ref('')

// 医療機関情報の自動抽出
const currentInstitution = computed(() => {
    if (users.value.length > 0 && users.value[0].medical_institution) {
        return users.value[0].medical_institution
    }
    return null
})

// 日時フォーマット
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// ユーザー一覧の取得
const fetchUsers = async () => {
    loading.value = true
    errorMessage.value = ''
    try {
        const res = await api.get(`/medical-institutions/${institutionId}/users`)
        users.value = res.data?.data || []
    } catch (error) {
        console.error('ユーザー一覧の取得に失敗しました:', error)
        errorMessage.value = error.response?.data?.message || 'データの取得に失敗しました。'
    } finally {
        loading.value = false
    }
}

// 承認処理 (approve_url がある場合はそれを使い、無ければ id からエンドポイントを生成)
const handleApprove = async (user) => {
    if (!confirm(`${user.name} 様の利用申請を承認しますか？`)) return

    actionLoadingId.value = user.id
    successMessage.value = ''
    errorMessage.value = ''

    try {
        const url = user.approve_url || `/users/${user.id}/approve`
        await api.post(url)
        successMessage.value = `${user.name} 様を承認しました。`
        await fetchUsers()
    } catch (error) {
        console.error('承認エラー:', error)
        errorMessage.value = error.response?.data?.message || '承認処理に失敗しました。'
    } finally {
        actionLoadingId.value = null
    }
}

// 却下処理 (reject_url がある場合はそれを使い、無ければ id からエンドポイントを生成)
const handleReject = async (user) => {
    if (!confirm(`${user.name} 様の利用申請を却下しますか？`)) return

    actionLoadingId.value = user.id
    successMessage.value = ''
    errorMessage.value = ''

    try {
        const url = user.reject_url || `/users/${user.id}/reject`
        await api.post(url)
        successMessage.value = `${user.name} 様の申請を却下しました。`
        await fetchUsers()
    } catch (error) {
        console.error('却下エラー:', error)
        errorMessage.value = error.response?.data?.message || '却下処理に失敗しました。'
    } finally {
        actionLoadingId.value = null
    }
}

onMounted(() => {
    fetchUsers()
})
</script>