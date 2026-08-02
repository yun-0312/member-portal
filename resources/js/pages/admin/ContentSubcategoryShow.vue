<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- メッセージ表示エリア -->
            <div
                v-if="message"
                class="p-4 rounded-xl text-sm font-medium flex items-center justify-between"
                :class="messageType === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-rose-50 border border-rose-200 text-rose-700'"
            >
                <span>{{ message }}</span>
                <button @click="message = ''" class="text-xs font-bold p-1 cursor-pointer hover:opacity-75">✕</button>
            </div>

            <!-- ヘッダー -->
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">サブカテゴリー詳細</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800">
                        📁 {{ item.name }}
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    <router-link
                        v-if="data.index_url"
                        :to="data.index_url"
                        class="px-3.5 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition-all"
                    >
                        一覧へ戻る
                    </router-link>
                    <router-link
                        v-if="data.update_url"
                        :to="data.update_url"
                        class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-all"
                    >
                        編集
                    </router-link>
                    <!-- 🗑️ 削除ボタン -->
                    <button
                        v-if="data.delete_url"
                        @click="deleteSubcategory"
                        :disabled="processing"
                        class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-all disabled:opacity-50 cursor-pointer"
                    >
                        削除
                    </button>
                </div>
            </div>

            <!-- 基本情報 -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h2 class="text-sm font-bold text-slate-500 uppercase mb-4">基本属性</h2>
                <dl class="divide-y divide-slate-100 text-sm">
                    <div class="py-2.5 grid grid-cols-3">
                        <dt class="text-slate-400">ID / スラッグ</dt>
                        <dd class="col-span-2 font-mono text-slate-700">#{{ item.id }} ({{ item.slug }})</dd>
                    </div>
                    <div class="py-2.5 grid grid-cols-3">
                        <dt class="text-slate-400">親カテゴリー</dt>
                        <dd class="col-span-2 font-medium text-slate-700">
                            {{ item.category?.name || item.content_category?.name || '未設定' }}
                        </dd>
                    </div>
                    <div class="py-2.5 grid grid-cols-3">
                        <dt class="text-slate-400">表示タイプ / 並び順</dt>
                        <dd class="col-span-2 space-x-2">
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-xs font-mono">{{ item.display_type || 'default' }}</span>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-xs font-mono">順序: {{ item.sort_order ?? 0 }}</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- 閲覧許可ロール (Roles) の管理エリア -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-500 uppercase">閲覧対象ロール ({{ assignedRoles.length }})</h2>
                    <p class="text-xs text-slate-400 mt-0.5">このサブカテゴリーにアクセス権限を与えるロールを設定します</p>
                </div>

                <!-- 設定中ロールバッジ一覧 -->
                <div v-if="assignedRoles.length > 0" class="flex flex-wrap gap-2">
                    <span
                        v-for="role in assignedRoles"
                        :key="role.id"
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-lg"
                    >
                        <span>👥 {{ role.name }}</span>
                        <!-- ✕ 削除ボタン -->
                        <button
                            @click="removeRole(role.id)"
                            :disabled="processing"
                            class="text-emerald-400 hover:text-rose-600 font-bold ml-1 rounded transition-colors cursor-pointer disabled:opacity-50"
                            title="ロールを解除"
                        >
                            ✕
                        </button>
                    </span>
                </div>
                <div v-else class="text-slate-400 text-xs py-3 border border-dashed border-slate-200 rounded-lg text-center">
                    割り当てられているロールはありません
                </div>

                <!-- ロール追加エリア -->
                <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
                    <select
                        v-model="selectedRoleId"
                        :disabled="processing || availableRoles.length === 0"
                        class="text-xs border border-slate-200 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:outline-indigo-500 flex-1"
                    >
                        <option value="">-- 追加するロールを選択 --</option>
                        <option v-for="role in availableRoles" :key="role.id" :value="role.id">
                            {{ role.name }}
                        </option>
                    </select>

                    <button
                        @click="addRole"
                        :disabled="!selectedRoleId || processing"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-all disabled:opacity-50 cursor-pointer"
                    >
                        {{ processing ? '処理中…' : '追加' }}
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api.js'

const route = useRoute()
const router = useRouter()

const data = ref({})
const allRoles = ref([])
const assignedRoles = ref([])
const selectedRoleId = ref('')
const processing = ref(false)
const message = ref('')
const messageType = ref('success')

const item = computed(() => data.value.item || {})

// まだ割り当てられていないロールリスト（ドロップダウン用）
const availableRoles = computed(() => {
    const currentIds = assignedRoles.value.map(r => r.id)
    return allRoles.value.filter(r => !currentIds.includes(r.id))
})

// 対象サブカテゴリーに設定されているロール一覧を取得 (GET)
const fetchAssignedRoles = async () => {
    try {
        const res = await api.get(`/admin/content-subcategories/${route.params.id}/roles`)
        assignedRoles.value = res.data || []
    } catch (error) {
        console.error('紐づきロールの取得に失敗しました:', error)
    }
}

// 初期データの読み込み
const fetchInitialData = async () => {
    try {
        const [subcategoryRes, allRolesRes] = await Promise.all([
            api.get(`/admin/content-subcategories/${route.params.id}`),
            api.get('/admin/roles')
        ])

        data.value = subcategoryRes.data
        // 全ロール一覧のデータ格納
        allRoles.value = allRolesRes.data.roles || allRolesRes.data.data || allRolesRes.data || []

        // 紐づいているロールの最新情報を取得
        await fetchAssignedRoles()
    } catch (error) {
        console.error('データの取得に失敗しました:', error)
    }
}

// 🗑️ サブカテゴリー自体の削除処理
const deleteSubcategory = async () => {
    if (!data.value.delete_url) return

    const confirmMessage = `「${item.value.name || 'このサブカテゴリー'}」を削除してもよろしいですか？\nこの操作は取り消せません。`
    if (!confirm(confirmMessage)) return

    processing.value = true
    message.value = ''

    try {
        await api.delete(data.value.delete_url)

        // 削除成功後、一覧ページへ遷移
        const redirectUrl = data.value.index_url || '/admin/content-subcategories'
        router.push(redirectUrl)
    } catch (error) {
        console.error('サブカテゴリーの削除に失敗しました:', error)
        messageType.value = 'error'
        message.value = error.response?.data?.message || 'サブカテゴリーの削除に失敗しました'
        processing.value = false
    }
}

// ロールの追加 (POST)
const addRole = async () => {
    if (!selectedRoleId.value) return

    processing.value = true
    message.value = ''

    try {
        const res = await api.post(`/admin/content-subcategories/${route.params.id}/roles`, {
            role_id: Number(selectedRoleId.value)
        })

        messageType.value = 'success'
        message.value = res.data.message || 'ロールを追加しました'
        selectedRoleId.value = ''

        // 最新のロール一覧を再取得
        await fetchAssignedRoles()
    } catch (error) {
        console.error('ロール追加に失敗しました:', error)
        messageType.value = 'error'
        message.value = error.response?.data?.message || 'ロールの追加に失敗しました'
    } finally {
        processing.value = false
    }
}

// ロールの削除 (DELETE)
const removeRole = async (roleId) => {
    processing.value = true
    message.value = ''

    try {
        const res = await api.delete(`/admin/content-subcategories/${route.params.id}/roles/${roleId}`)

        messageType.value = 'success'
        message.value = res.data.message || 'ロールを削除しました'

        // 最新のロール一覧を再取得
        await fetchAssignedRoles()
    } catch (error) {
        console.error('ロール削除に失敗しました:', error)
        messageType.value = 'error'
        message.value = error.response?.data?.message || 'ロールの削除に失敗しました'
    } finally {
        processing.value = false
    }
}

onMounted(fetchInitialData)
</script>