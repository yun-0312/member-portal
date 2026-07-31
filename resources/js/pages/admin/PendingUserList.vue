<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-6xl mx-auto space-y-6">

        <!-- 1. ヘッダーエリア -->
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <span>⏳</span> 承認待ちユーザー一覧
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">
                新規登録申請を行った医療従事者・会員の承認処理が行えます
            </p>
            </div>

            <!-- 戻るボタン -->
            <router-link
            to="/admin/users"
            class="inline-flex items-center gap-1 px-3.5 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-all active:scale-95 shadow-2xs cursor-pointer"
            >
            <span>‹</span>
            <span>ユーザー一覧へ戻る</span>
            </router-link>
        </div>

        <!-- 2. ローディング表示 -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
            <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-medium">承認待ちリストを読み込み中…</p>
        </div>

        <!-- 3. エラー表示 -->
        <div v-else-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ errorMessage }}</span>
        </div>

        <!-- 4. メインテーブル -->
        <div v-else class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <th class="py-3.5 px-4 w-16 text-center">ID</th>
                    <th class="py-3.5 px-4">申請者氏名 / メール</th>
                    <th class="py-3.5 px-4">ロール</th>
                    <th class="py-3.5 px-4">所属医療機関</th>
                    <th class="py-3.5 px-4">機関代表者</th>
                    <th class="py-3.5 px-4">申請日時</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm font-medium text-slate-700">
                <!-- データ件数が 0 件の場合 -->
                <tr v-if="users.length === 0">
                    <td colspan="7" class="py-12 text-center text-slate-400">
                    現在、承認待ちのユーザーはいません 🎉
                    </td>
                </tr>

                <!-- ユーザーデータ行 -->
                <tr
                    v-for="user in users"
                    :key="user.id"
                    class="hover:bg-slate-50/80 transition-colors"
                >
                    <!-- ID -->
                    <td class="py-3.5 px-4 text-center font-mono text-slate-400 text-xs">
                    #{{ user.id }}
                    </td>

                    <!-- 氏名 / メール -->
                    <td class="py-3.5 px-4">
                    <div class="font-bold text-slate-800">{{ user.name }}</div>
                    <div class="text-xs text-slate-400 font-mono mt-0.5">{{ user.email }}</div>
                    </td>

                    <!-- ロール -->
                    <td class="py-3.5 px-4">
                    <span class="px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold rounded-lg">
                        {{ user.role?.name || '-' }}
                    </span>
                    </td>

                    <!-- 所属医療機関 -->
                    <td class="py-3.5 px-4">
                    <div class="font-semibold text-slate-700">
                        {{ user.medical_institution?.name || '-' }}
                    </div>
                    <div v-if="user.medical_institution?.phone" class="text-xs text-slate-400 mt-0.5">
                        📞 {{ user.medical_institution.phone }}
                    </div>
                    </td>

                    <!-- 機関代表者（取得した representative の名前を表示） -->
                    <td class="py-3.5 px-4">
                    <div v-if="user.medical_institution?.representative" class="flex items-center gap-1.5">
                        <span class="font-medium text-slate-700">
                        {{ user.medical_institution.representative.name }}
                        </span>
                        <span class="text-[10px] px-1.5 py-0.2 bg-slate-100 text-slate-500 rounded border border-slate-200">
                        代表
                        </span>
                    </div>
                    <span v-else class="text-slate-400 text-xs">-</span>
                    </td>

                    <!-- 申請日時 (ISO表記を読みやすく整形) -->
                    <td class="py-3.5 px-4 text-xs text-slate-500 font-mono">
                    {{ formatDate(user.created_at) }}
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
import { ref, onMounted } from 'vue'
import api from '@/api.js'

const loading = ref(true)
const errorMessage = ref('')
const users = ref([])
const processingId = ref(null)

// 承認待ちリストの取得
const fetchPendingUsers = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const res = await api.get('/admin/users/pending')
        users.value = res.data.data || []
    } catch (error) {
        console.error('データの取得に失敗しました:', error)
        errorMessage.value = '承認待ちデータの読み込みに失敗しました。'
    } finally {
        loading.value = false
    }
}

// 日時フォーマット用のヘルパー関数 (2026/07/30 18:28 形式に変換)
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleString('ja-JP', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

onMounted(() => {
    fetchPendingUsers()
})
</script>