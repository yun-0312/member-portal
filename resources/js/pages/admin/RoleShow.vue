<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- ローディング表示 -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
                <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm font-medium">ロール情報を読み込み中…</p>
            </div>

            <template v-else>
                <!-- エラー表示エリア -->
                <div
                    v-if="errorMessage"
                    class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm font-medium flex items-center justify-between gap-3 shadow-2xs"
                >
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ errorMessage }}</span>
                    </div>
                    <button @click="errorMessage = ''" class="text-rose-400 hover:text-rose-600 font-bold text-xs p-1 cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- 1. ヘッダーエリア -->
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">ロール詳細</span>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 flex items-center gap-2.5 font-mono mt-0.5">
                            <span>👥</span> {{ role.name }}
                        </h1>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- 一覧へ戻る -->
                        <router-link
                            v-if="role.index_url"
                            :to="role.index_url"
                            class="px-3.5 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition-all"
                        >
                            一覧へ戻る
                        </router-link>

                        <!-- 編集 -->
                        <router-link
                            v-if="role.update_url"
                            :to="role.update_url"
                            class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-all"
                        >
                            編集
                        </router-link>

                        <!-- 削除 -->
                        <button
                            v-if="role.destroy_url"
                            @click="deleteRole"
                            :disabled="processing"
                            class="px-3.5 py-2 bg-white hover:bg-rose-50 border border-rose-200 text-rose-600 text-xs font-semibold rounded-xl transition-all disabled:opacity-50 cursor-pointer"
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
                            <dd class="col-span-2 font-mono font-bold text-slate-700">#{{ role.id }}</dd>
                        </div>
                        <div class="py-3 grid grid-cols-3">
                            <dt class="text-slate-400 font-medium">ロール名</dt>
                            <dd class="col-span-2 font-mono font-semibold text-indigo-600">{{ role.name }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- 3. アクセス可能なコンテンツカテゴリー一覧 -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                    <div>
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                            アクセス許可コンテンツカテゴリー ({{ role.content_categories?.length || 0 }})
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">このロールに紐づけられているコンテンツカテゴリーです</p>
                    </div>

                    <!-- カテゴリーバッジ一覧 -->
                    <div v-if="role.content_categories && role.content_categories.length > 0" class="flex flex-wrap gap-2 pt-2">
                        <router-link
                            v-for="category in role.content_categories"
                            :key="category.id"
                            :to="category.show_url"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 text-slate-700 hover:text-indigo-600 rounded-xl text-xs font-semibold transition-all group"
                        >
                            <span>📁</span>
                            <span>{{ category.name }}</span>
                            <span class="text-slate-300 group-hover:text-indigo-400 text-xs ml-0.5">→</span>
                        </router-link>
                    </div>

                    <div v-else class="text-slate-400 text-xs py-6 text-center border border-dashed border-slate-200 rounded-xl">
                        割り当てられているコンテンツカテゴリーはありません
                    </div>
                </div>

                <!-- 4. アクセス可能なお知らせカテゴリー一覧 -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                    <div>
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                            アクセス許可お知らせカテゴリー ({{ role.notice_categories?.length || 0 }})
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">このロールに紐づけられているお知らせカテゴリーです</p>
                    </div>

                    <!-- カテゴリーバッジ一覧 -->
                    <div v-if="role.notice_categories && role.notice_categories.length > 0" class="flex flex-wrap gap-2 pt-2">
                        <router-link
                            v-for="category in role.notice_categories"
                            :key="category.id"
                            :to="category.show_url"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-amber-50 border border-slate-200 hover:border-amber-200 text-slate-700 hover:text-amber-700 rounded-xl text-xs font-semibold transition-all group"
                        >
                            <span>📢</span>
                            <span>{{ category.name }}</span>
                            <span class="text-slate-300 group-hover:text-amber-400 text-xs ml-0.5">→</span>
                        </router-link>
                    </div>

                    <div v-else class="text-slate-400 text-xs py-6 text-center border border-dashed border-slate-200 rounded-xl">
                        割り当てられているお知らせカテゴリーはありません
                    </div>
                </div>

            </template>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api.js'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const processing = ref(false)
const errorMessage = ref('')
const role = ref({})

// ロール詳細データの取得
const fetchRole = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const res = await api.get(`/admin/roles/${route.params.id}`)
        // res.data.role または res.data から柔軟に取得
        role.value = res.data.role || res.data || {}
    } catch (error) {
        console.error('ロール情報の取得に失敗しました:', error)
        errorMessage.value = 'ロール情報の読み込みに失敗しました。'
    } finally {
        loading.value = false
    }
}

// ロール自体の削除
const deleteRole = async () => {
    if (!confirm('このロールを削除してもよろしいですか？')) return

    errorMessage.value = ''
    processing.value = true

    const url = role.value.destroy_url.startsWith('/')
        ? role.value.destroy_url
        : `/${role.value.destroy_url}`

    try {
        await api.delete(url)
        router.push(role.value.index_url || '/admin/roles')
    } catch (error) {
        console.error('削除に失敗しました:', error)
        errorMessage.value = error.response?.data?.message
            || error.response?.data?.error
            || 'ロールの削除に失敗しました。'
    } finally {
        processing.value = false
    }
}

onMounted(fetchRole)
</script>