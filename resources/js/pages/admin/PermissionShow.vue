<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

        <!-- ローディング -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
            <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-medium">権限情報を読み込み中…</p>
        </div>

        <template v-else>
            <!-- 1. ヘッダーエリア -->
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">パーミッション詳細</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 flex items-center gap-2.5 font-mono mt-0.5">
                    <span>🔑</span> {{ permission.name }}
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    <!-- 一覧へ戻る -->
                    <router-link
                    v-if="permission.index_url"
                    :to="permission.index_url"
                    class="px-3.5 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition-all"
                    >
                    一覧へ戻る
                    </router-link>

                    <!-- 編集 -->
                    <router-link
                    v-if="permission.update_url"
                    :to="permission.update_url"
                    class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-all"
                    >
                    編集
                    </router-link>

                    <!-- 削除 -->
                    <button
                    v-if="permission.destroy_url"
                    @click="deletePermission"
                    class="px-3.5 py-2 bg-white hover:bg-rose-50 border border-rose-200 text-rose-600 text-xs font-semibold rounded-xl transition-all cursor-pointer"
                    >
                    削除
                    </button>
                </div>
            </div>

            <!-- 2. 基本情報 -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">基本属性</h2>
                <dl class="divide-y divide-slate-100 text-sm">
                    <div class="py-3 grid grid-cols-3">
                        <dt class="text-slate-400 font-medium">ID</dt>
                        <dd class="col-span-2 font-mono font-bold text-slate-700">#{{ permission.id }}</dd>
                    </div>
                    <div class="py-3 grid grid-cols-3">
                        <dt class="text-slate-400 font-medium">権限名 (Name)</dt>
                        <dd class="col-span-2 font-mono font-semibold text-indigo-600">{{ permission.name }}</dd>
                    </div>
                </dl>
            </div>

            <!-- 3. 割り当て済みロール一覧 -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        割り当て済みロール ({{ permission.roles?.length || 0 }})
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">この権限が適用されているシステムロール一覧です</p>
                </div>

                <!-- ロール追加フォーム (add_role_url がある場合) -->
                <div v-if="permission.add_role_url" class="flex items-center gap-2">
                <select
                    v-model="selectedRoleId"
                    class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                >
                    <option value="" disabled>追加するロールを選択</option>
                    <option
                    v-for="role in availableRoles"
                    :key="role.id"
                    :value="role.id"
                    >
                    {{ role.name }}
                    </option>
                </select>

                <button
                    @click="addRole"
                    :disabled="!selectedRoleId || processing"
                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-semibold rounded-xl shadow-2xs transition-all active:scale-95 cursor-pointer"
                >
                    追加
                </button>
                </div>
            </div>

            <!-- ロールバッジリスト -->
            <div v-if="permission.roles && permission.roles.length > 0" class="flex flex-wrap gap-2 pt-2">
                <div
                v-for="role in permission.roles"
                :key="role.id"
                class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-xs font-semibold"
                >
                <span>👥 {{ role.name }}</span>
                <button
                    v-if="role.remove_url"
                    @click="removeRole(role)"
                    :disabled="processing"
                    class="text-slate-400 hover:text-rose-600 font-bold ml-1 transition-colors cursor-pointer"
                    title="ロールから解除"
                >
                    ×
                </button>
                </div>
            </div>

            <div v-else class="text-slate-400 text-xs py-6 text-center border border-dashed border-slate-200 rounded-xl">
                この権限は現在どのロールにも割り当てられていません
            </div>
            </div>
        </template>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api.js'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const processing = ref(false)
const permission = ref({})
const selectedRoleId = ref('')

const allMasterRoles = ref([])

// まだ割り当てられていないロールの絞り込み
const availableRoles = computed(() => {
    const currentRoleIds = permission.value.roles?.map(r => r.id) || []
    return allMasterRoles.value.filter(role => !currentRoleIds.includes(role.id))
})

// ロールマスター一覧の取得 API
const fetchRoles = async () => {
    try {
        const res = await api.get('/admin/roles')
        // レスポンスの形式に合わせて調整してください (例: res.data または res.data.roles など)
        allMasterRoles.value = Array.isArray(res.data) ? res.data : (res.data.roles || res.data.data || [])
    } catch (error) {
        console.error('ロール一覧の取得に失敗しました:', error)
    }
}

// パーミッション詳細の取得
const fetchPermission = async () => {
    loading.value = true
    try {
        const res = await api.get(`/admin/permissions/${route.params.id}`)
        permission.value = res.data.permission || {}
    } catch (error) {
        console.error('権限情報の取得に失敗しました:', error)
    } finally {
        loading.value = false
    }
}

// 初期化（並列取得）
const init = async () => {
    loading.value = true
    try {
        // ロール一覧と権限詳細を並列で取得
        await Promise.all([fetchRoles(), fetchPermission()])
    } finally {
        loading.value = false
    }
}

// ロールの追加（add_role_url の {role_id} を置換してPOST）
const addRole = async () => {
    if (!selectedRoleId.value || !permission.value.add_role_url) return

    const url = permission.value.add_role_url.replace('{role_id}', selectedRoleId.value)

    processing.value = true
    try {
        await api.post(url)
        // 画面を再取得して最新化
        await fetchPermission()
        selectedRoleId.value = ''
    } catch (error) {
        console.error('ロール追加に失敗しました:', error)
        alert('ロールの追加に失敗しました。')
    } finally {
        processing.value = false
    }
}

// ロールの解除（remove_url へ DELETE）
const removeRole = async (role) => {
    if (!confirm(`ロール 「${role.name}」 からこの権限を解除しますか？`)) return

    processing.value = true
    try {
        await api.delete(role.remove_url)
        permission.value.roles = permission.value.roles.filter(r => r.id !== role.id)
    } catch (error) {
        console.error('ロール解除に失敗しました:', error)
        alert('ロールの解除に失敗しました。')
    } finally {
        processing.value = false
    }
}

// パーミッション自体の削除
const deletePermission = async () => {
    if (!confirm('この権限を完全に削除してもよろしいですか？')) return

    const url = permission.value.destroy_url.startsWith('/')
        ? permission.value.destroy_url
        : `/${permission.value.destroy_url}`

    try {
        await api.delete(url)
        router.push(permission.value.index_url || '/admin/permissions')
    } catch (error) {
        console.error('削除に失敗しました:', error)
        alert('権限の削除に失敗しました。')
    }
}

onMounted(init)
</script>