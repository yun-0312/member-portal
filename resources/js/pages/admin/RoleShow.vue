<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

        <!-- ヘッダー -->
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">ロール詳細</span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 flex items-center gap-3">
                <span>🛡️</span> {{ role.name }}
            </h1>
            </div>

            <div class="flex items-center gap-2">
            <router-link
                v-if="role.index_url"
                :to="role.index_url"
                class="px-3.5 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition-all"
            >
                一覧へ戻る
            </router-link>
            <router-link
                v-if="role.update_url"
                :to="role.update_url"
                class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-all"
            >
                編集する
            </router-link>
            </div>
        </div>

        <!-- 基本情報 -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-500 uppercase">基本情報</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-slate-400 text-xs block">ID</span>
                <span class="font-mono font-bold text-slate-700">#{{ role.id }}</span>
            </div>
            <div>
                <span class="text-slate-400 text-xs block">スラッグ (Slug)</span>
                <span class="font-mono text-slate-700">{{ role.slug || '-' }}</span>
            </div>
            </div>
        </div>

        <!-- 保持している権限 (Permissions) 一覧 -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-500 uppercase">割り当て済み権限 ({{ role.permissions?.length || 0 }})</h2>
            <router-link
                v-if="role.add_permission_url"
                :to="role.add_permission_url"
                class="text-xs font-bold text-indigo-600 hover:text-indigo-800"
            >
                ＋ 権限を追加
            </router-link>
            </div>

            <div v-if="role.permissions && role.permissions.length > 0" class="flex flex-wrap gap-2">
            <div
                v-for="perm in role.permissions"
                :key="perm.id"
                class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-xs font-mono"
            >
                <span>🔑 {{ perm.name }}</span>
                <button
                v-if="perm.remove_url"
                @click="removePermission(perm)"
                class="text-slate-400 hover:text-rose-600 font-bold ml-1 transition-colors"
                title="権限を削除"
                >
                ×
                </button>
            </div>
            </div>
            <div v-else class="text-slate-400 text-xs py-4 text-center">
            権限が割り当てられていません
            </div>
        </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api.js'

const route = useRoute()
const role = ref({})

const fetchRole = async () => {
    try {
        const res = await api.get(`/admin/roles/${route.params.id}`)
        role.value = res.data.role
    } catch (error) {
        console.error('Roleの取得に失敗しました:', error)
    }
}

const removePermission = async (perm) => {
    if (!confirm(`権限 「${perm.name}」 を解除しますか？`)) return
    try {
        await api.delete(perm.remove_url)
        role.value.permissions = role.value.permissions.filter(p => p.id !== perm.id)
    } catch (error) {
        alert('権限の削除に失敗しました')
    }
}

onMounted(fetchRole)
</script>