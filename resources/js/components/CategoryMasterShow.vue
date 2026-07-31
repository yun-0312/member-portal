<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-3xl mx-auto space-y-6">

        <!-- ヘッダー -->
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
            <!-- Propsのtitleを表示（未指定ならデフォルト値） -->
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ title }}</span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800">
                📁 {{ category.name }}
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
                <button
                    v-if="data.delete_url"
                    @click="deleteCategory"
                    class="px-3.5 py-2 bg-white hover:bg-rose-50 border border-rose-200 text-rose-600 text-xs font-semibold rounded-xl transition-all"
                >
                    {{ isDeleting ? '削除中...' : '削除' }}
                </button>
            </div>
        </div>

        <!-- ⚠️ 削除エラー表示エリア -->
        <div
            v-if="deleteError"
            class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm font-medium flex items-center justify-between gap-3 animate-fade-in"
        >
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ deleteError }}</span>
            </div>
            <button
                @click="deleteError = ''"
                class="text-rose-400 hover:text-rose-600 font-bold text-xs p-1"
                title="閉じる"
            >
                ✕
            </button>
        </div>

        <!-- 属性情報一覧 -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <dl class="divide-y divide-slate-100 text-sm">
            <!-- ID（基本必須） -->
            <div class="py-3 grid grid-cols-3">
                <dt class="text-slate-400 font-medium">ID</dt>
                <dd class="col-span-2 font-mono font-bold text-slate-700">#{{ category.id }}</dd>
            </div>

            <!-- 名前（基本必須） -->
            <div class="py-3 grid grid-cols-3">
                <dt class="text-slate-400 font-medium">名称</dt>
                <dd class="col-span-2 font-semibold text-slate-800">{{ category.name }}</dd>
            </div>

            <!-- columns で指定された追加カラムを動的に描画 -->
            <template v-for="col in columns" :key="col">
                <div v-if="col in category" class="py-3 grid grid-cols-3">
                <dt class="text-slate-400 font-medium capitalize">{{ formatColumnName(col) }}</dt>
                <dd class="col-span-2 text-slate-700 font-mono">
                    {{ category[col] ?? '-' }}
                </dd>
                </div>
            </template>

            <!-- 作成日時（存在する場合） -->
            <div v-if="category.created_at" class="py-3 grid grid-cols-3">
                <dt class="text-slate-400 font-medium">作成日時</dt>
                <dd class="col-span-2 font-mono text-xs text-slate-500">{{ formatDate(category.created_at) }}</dd>
            </div>
            </dl>
        </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api.js'

// 親コンポーネントからの渡されもの（Props）の定義
const props = defineProps({
    title: {
        type: String,
        default: 'カテゴリー詳細'
    },
    apiEndpoint: {
        type: String,
        required: true
    },
    columns: {
        type: Array,
        default: () => ['slug', 'sort_order']
    }
})

const router = useRouter()
const data = ref({})
const category = computed(() => data.value.item || {})

// 削除処理用ステート
const isDeleting = ref(false)
const deleteError = ref('')

// APIリクエスト処理（props.apiEndpoint を使用）
const fetchCategory = async () => {
    if (!props.apiEndpoint) return
    try {
        const res = await api.get(props.apiEndpoint)
        data.value = res.data
    } catch (error) {
        console.error('カテゴリー情報の取得に失敗しました:', error)
    }
}

// カラム名を綺麗にフォーマット
const formatColumnName = (key) => {
    const labels = {
        slug: 'スラッグ (Slug)',
        sort_order: '並び順 (Sort Order)',
        section: 'セクション',
        display_type: '表示タイプ'
    }
    return labels[key] || key
}

const deleteCategory = async () => {
    deleteError.value = ''

    if (!confirm('このカテゴリーを削除してもよろしいですか？')) return

    isDeleting.value = true

    try {
        await api.delete(data.value.delete_url)
        router.push(data.value.index_url)
    } catch (error) {
        console.error('削除エラー:', error)

        // APIからのレスポンスメッセージを取得
        const message = error.response?.data?.message
            || error.response?.data?.error
            || 'カテゴリーの削除に失敗しました。関連データが存在する可能性があります。'

        deleteError.value = message
    } finally {
        deleteError.value = message
    }
}

const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleString('ja-JP') : '-'

// apiEndpoint の変更時や初回マウント時にデータを取得
watch(() => props.apiEndpoint, fetchCategory, { immediate: true })
</script>