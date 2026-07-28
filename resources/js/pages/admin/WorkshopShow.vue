<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-5xl mx-auto space-y-6">

        <!-- 1. ページヘッダー＆アクションエリア -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-4 gap-4">
            <div class="flex items-center gap-3">
            <!-- index_url を活用した一覧に戻るボタン -->
            <router-link
                :to="workshopData?.index_url || '/admin/workshops'"
                class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-2xs hover:bg-slate-50 transition-all active:scale-95"
            >
                <span>← 一覧へ戻る</span>
            </router-link>
            <h1 class="text-xl md:text-2xl font-extrabold text-slate-800 tracking-tight">
                研修会詳細
            </h1>
            </div>

            <!-- 管理操作（編集・削除）ボタン -->
            <div v-if="workshopData?.item" class="flex items-center gap-2 self-end sm:self-auto">
            <!-- update_url を活用した編集ボタン -->
            <router-link
                :to="`${workshopData.update_url}/edit`"
                class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 px-3.5 py-2 rounded-xl shadow-2xs transition-all active:scale-95"
            >
                ✏️ 編集
            </router-link>

            <!-- delete_url を活用した削除ボタン -->
            <button
                @click="handleDelete"
                :disabled="deleting"
                class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-100 px-3.5 py-2 rounded-xl shadow-2xs transition-all active:scale-95 disabled:opacity-50 cursor-pointer"
            >
                <span v-if="deleting">削除中…</span>
                <span v-else>🗑️ 削除</span>
            </button>
            </div>
        </div>

        <!-- 2. ローディング表示 -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
            <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-medium">研修会データを読み込み中…</p>
        </div>

        <!-- 3. メインコンテンツ -->
        <div v-else-if="workshopData?.item" class="space-y-6">

            <!-- メインカード -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">

            <!-- タイトル領域 -->
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 space-y-2">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    研修会 / 講座
                </span>
                <h2 class="text-2xl font-extrabold text-slate-800 leading-snug">
                    {{ workshopData.item.title }}
                </h2>
            </div>

            <!-- 開催要項メタインフォメーション -->
            <div class="p-6 bg-indigo-50/30 border-b border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <!-- 開催日時 -->
                <div class="space-y-1">
                    <span class="font-bold text-slate-500 uppercase tracking-wider block">📅 開催日時</span>
                    <p class="font-semibold text-slate-800">
                        {{ formatDate(workshopData.item.start_at) }} <br class="hidden sm:inline" />〜 {{ formatDate(workshopData.item.end_at) }}
                    </p>
                </div>

                <!-- 開催場所 -->
                <div class="space-y-1">
                    <span class="font-bold text-slate-500 uppercase tracking-wider block">📍 開催場所</span>
                    <p class="font-semibold text-slate-800">
                        {{ workshopData.item.location || '未定・未設定' }}
                    </p>
                </div>

                <!-- 講師情報 -->
                <div class="space-y-1">
                    <span class="font-bold text-slate-500 uppercase tracking-wider block">👨‍🏫 講師</span>
                    <p class="font-semibold text-slate-800">
                        {{ workshopData.item.lecture || '未設定' }}
                    </p>
                </div>
            </div>

            <!-- 詳細説明本文 -->
            <div class="p-6 md:p-8 space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">概要・説明</h3>
                <p class="text-sm md:text-base text-slate-700 leading-relaxed whitespace-pre-wrap">
                    {{ workshopData.item.description || '概要説明はありません。' }}
                </p>
            </div>

            </div>

            <!-- サイド情報・管理用メタデータ（閲覧許可ロール・作成更新日時） -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- 閲覧許可ロール（追加・削除機能付き） -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                        👥 閲覧対象ロール
                    </h3>
                    <button
                        @click="showAddRoleModal = true"
                        type="button"
                        class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded-md transition-all cursor-pointer"
                    >
                        ＋ ロール追加
                    </button>
                </div>

                <!-- 設定中ロールのバッジ一覧 -->
                <div v-if="(workshopData.roles || workshopData.item.roles)?.length" class="flex flex-wrap gap-1.5 pt-1">
                    <div
                        v-for="role in (workshopData.roles || workshopData.item.roles)"
                        :key="role.id"
                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200 group"
                    >
                        <span>{{ role.name }}</span>
                        <button
                            v-if="role.destroy_url"
                            @click="removeRole(role)"
                            :disabled="processingRoleId === role.id"
                            type="button"
                            title="このロールを削除"
                            class="text-slate-400 hover:text-rose-600 font-bold ml-0.5 cursor-pointer disabled:opacity-50"
                        >
                            ×
                        </button>
                    </div>
                </div>
                <p v-else class="text-xs text-slate-400 pt-1">指定された対象ロールはありません（全体公開など）</p>
            </div>

            <!-- 作成・更新メタ情報 -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80 space-y-2 text-xs text-slate-500">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                    ℹ️ システム管理情報
                </h3>
                <div class="space-y-1 font-mono pt-1">
                    <div class="flex justify-between">
                        <span>作成者ID:</span>
                        <span class="text-slate-700 font-bold">{{ workshopData.item.created_by ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>作成日時:</span>
                        <span class="text-slate-700 font-bold">{{ formatDate(workshopData.item.created_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>更新日時:</span>
                        <span class="text-slate-700 font-bold">{{ formatDate(workshopData.item.updated_at) }}</span>
                    </div>
                </div>
            </div>

            </div>

        </div>

        </div>

        <!-- ロール追加モーダル -->
        <div v-if="showAddRoleModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-sm w-full p-6 space-y-4">
                <h3 class="text-base font-bold text-slate-800">閲覧許可ロールの追加</h3>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">追加するロール</label>
                    <select
                        v-model="selectedRoleId"
                        class="w-full text-xs p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-hidden focus:border-blue-500"
                    >
                        <option value="" disabled>ロールを選択してください</option>
                        <option v-for="r in availableRoles" :key="r.id" :value="r.id">
                            {{ r.name }} (ID: {{ r.id }})
                        </option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button
                        @click="showAddRoleModal = false"
                        type="button"
                        class="text-xs font-bold text-slate-600 hover:bg-slate-100 px-3 py-2 rounded-xl border border-slate-200 cursor-pointer"
                    >
                        キャンセル
                    </button>
                    <button
                        @click="addRole"
                        :disabled="!selectedRoleId || addingRole"
                        type="button"
                        class="text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 px-4 py-2 rounded-xl shadow-2xs cursor-pointer"
                    >
                        <span v-if="addingRole">追加中…</span>
                        <span v-else>追加する</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api.js'

const route = useRoute()
const router = useRouter()

const workshopData = ref(null)
const loading = ref(true)
const deleting = ref(false)

// ロール操作用 state
const showAddRoleModal = ref(false)
const selectedRoleId = ref('')
const addingRole = ref(false)
const processingRoleId = ref(null)

// APIから動的に取得するシステム全体のロールマスタ
const masterRoles = ref([])

// まだ割り当てられていないロールのみ抽出
const availableRoles = computed(() => {
    if (!workshopData.value) return []
    const currentRoles = workshopData.value.roles || workshopData.value.item?.roles || []
    const currentRoleIds = currentRoles.map(r => r.id)
    return masterRoles.value.filter(r => !currentRoleIds.includes(r.id))
})

// システム全体のロールマスタ取得 API
const fetchMasterRoles = async () => {
    try {
        const res = await api.get('/admin/roles')
        masterRoles.value = res.data?.data || res.data || []
    } catch (error) {
        console.error('ロールマスタ一覧の取得に失敗しました:', error)
    }
}

// 研修会詳細の取得 API
const fetchWorkshopDetail = async () => {
    loading.value = true
    try {
        const id = route.params.id
        const res = await api.get(`/admin/workshops/${id}`)
        workshopData.value = res.data
    } catch (error) {
        console.error('研修会詳細の取得に失敗しました:', error)
    } finally {
        loading.value = false
    }
}

// ロールの追加処理
const addRole = async () => {
    if (!selectedRoleId.value) return

    addingRole.value = true
    try {
        const targetUrl = workshopData.value.role_targetable_url || `/admin/workshops/${workshopData.value.item.id}/roles`

        await api.post(targetUrl, {
            targetable_id: workshopData.value.item.id,
            targetable_type: 'App\\Models\\Workshop',
            role_id: Number(selectedRoleId.value)
        })

        alert('ロールを追加しました。')
        showAddRoleModal.value = false
        selectedRoleId.value = ''

        // 最新状態を取得して再描画
        await fetchWorkshopDetail()
    } catch (error) {
        console.error('ロールの追加に失敗しました:', error)
        alert('ロールの追加に失敗しました。')
    } finally {
        addingRole.value = false
    }
}

// ロールの削除処理
const removeRole = async (role) => {
    if (!confirm(`ロール「${role.name}」を除外しますか？`)) return

    processingRoleId.value = role.id
    try {
        const deleteUrl = role.destroy_url || `/admin/workshops/${workshopData.value.item.id}/roles/${role.id}`
        await api.delete(deleteUrl)

        alert('ロールを削除しました。')
        await fetchWorkshopDetail()
    } catch (error) {
        console.error('ロールの削除に失敗しました:', error)
        alert('ロールの削除に失敗しました。')
    } finally {
        processingRoleId.value = null
    }
}

// 研修会の削除処理（delete_url を活用）
const handleDelete = async () => {
    if (!confirm('この研修会情報を削除してもよろしいですか？')) return

    deleting.value = true
    try {
        const deleteEndpoint = workshopData.value.delete_url
        await api.delete(deleteEndpoint)

        alert('削除が完了しました。')
        router.push(workshopData.value.index_url || '/admin/workshops')
    } catch (error) {
        console.error('削除に失敗しました:', error)
        alert('削除に失敗しました。')
    } finally {
        deleting.value = false
    }
}

// 日時整形用ヘルパー
const formatDate = (isoString) => {
    if (!isoString) return '-'
    const date = new Date(isoString)
    return date.toLocaleString('ja-JP', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

onMounted(() => {
    fetchMasterRoles()
    fetchWorkshopDetail()
})
</script>