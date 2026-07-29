<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- 1. ヘッダーエリア (タイトル & 操作ボタン) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                        <span>🗓️</span> ワークショップ詳細
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">ワークショップの登録内容を確認・管理します</p>
                </div>
                
                <div class="flex items-center gap-2.5">
                    <button
                        type="button"
                        @click="goIndex"
                        class="inline-flex items-center gap-1 text-xs md:text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer"
                    >
                        ← 一覧へ戻る
                    </button>
                    <button
                        v-if="item"
                        type="button"
                        @click="goEdit"
                        class="inline-flex items-center gap-1 text-xs md:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer"
                    >
                        ✏️ 編集する
                    </button>
                </div>
            </div>

            <!-- ローディング状態 -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3 bg-white rounded-2xl border border-slate-200 shadow-xs">
                <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm font-medium">データを読み込み中…</p>
            </div>

            <!-- エラー表示 -->
            <div v-else-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm">
                <p class="font-bold">⚠️ {{ errorMessage }}</p>
            </div>

            <!-- 詳細カード表示 -->
            <div v-else-if="item" class="space-y-6">

                <!-- メイン情報カード -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-xs space-y-6">
                    
                    <!-- ワークショップタイトル -->
                    <div class="border-b border-slate-100 pb-5">
                        <h2 class="text-xl md:text-2xl font-bold text-slate-900 leading-snug">
                            {{ item.title }}
                        </h2>
                    </div>

                    <!-- 概要グリッド（日時 / 講師 / 場所） -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/80 p-5 rounded-xl border border-slate-100">
                        
                        <!-- 講師 (lecture) -->
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">講師</span>
                            <p class="text-sm md:text-base font-semibold text-slate-800">
                                {{ item.lecture || '未設定' }}
                            </p>
                        </div>

                        <!-- 開催場所 (location) -->
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">開催場所</span>
                            <p class="text-sm md:text-base font-semibold text-slate-800">
                                {{ item.location || '未設定' }}
                            </p>
                        </div>

                        <!-- 開始日時 (start_at) -->
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">開始日時</span>
                            <p class="text-sm md:text-base font-semibold text-slate-800 font-mono">
                                {{ formatDate(item.start_at) }}
                            </p>
                        </div>

                        <!-- 終了日時 (end_at) -->
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">終了日時</span>
                            <p class="text-sm md:text-base font-semibold text-slate-800 font-mono">
                                {{ formatDate(item.end_at) }}
                            </p>
                        </div>

                    </div>

                    <!-- 詳細・説明 (description) -->
                    <div class="space-y-2 pt-2">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">詳細・説明</h3>
                        <div class="text-xs md:text-sm text-slate-700 leading-relaxed whitespace-pre-wrap bg-white p-4 rounded-xl border border-slate-200">
                            {{ item.description || '説明はありません。' }}
                        </div>
                    </div>

                </div>

                <!-- システム管理情報カード -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">システム情報</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs md:text-sm">
                        
                        <!-- ⭕️ IDから「作成者名」に変更 -->
                        <div class="space-y-0.5">
                            <span class="text-slate-400 block">作成者</span>
                            <span class="font-medium text-slate-700">
                                {{ item.creator?.name || '不明' }}
                            </span>
                        </div>

                        <!-- 作成日時 -->
                        <div class="space-y-0.5">
                            <span class="text-slate-400 block">作成日時</span>
                            <span class="font-medium text-slate-700 font-mono">
                                {{ formatDate(item.created_at) }}
                            </span>
                        </div>

                        <!-- 更新日時 -->
                        <div class="space-y-0.5">
                            <span class="text-slate-400 block">最終更新日時</span>
                            <span class="font-medium text-slate-700 font-mono">
                                {{ formatDate(item.updated_at) }}
                            </span>
                        </div>

                    </div>
                </div>

                <!-- 削除エリア（下部危険ゾーン） -->
                <div class="flex justify-end pt-2">
                    <button
                        type="button"
                        @click="handleDelete"
                        :disabled="deleting"
                        class="text-xs md:text-sm font-semibold text-rose-600 hover:text-rose-800 hover:bg-rose-50 border border-rose-200 px-4 py-2 rounded-xl transition-all cursor-pointer disabled:opacity-50"
                    >
                        {{ deleting ? '削除中…' : '🗑️ この研修会を削除する' }}
                    </button>
                </div>

            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../api.js'

const router = useRouter()
const route = useRoute()

const item = ref(null)
const indexUrl = ref('/admin/workshops')
const loading = ref(true)
const deleting = ref(false)
const errorMessage = ref('')

// 日時フォーマット関数
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const d = new Date(dateString)
    if (isNaN(d.getTime())) return dateString

    const yyyy = d.getFullYear()
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    const hh = String(d.getHours()).padStart(2, '0')
    const min = String(d.getMinutes()).padStart(2, '0')

    return `${yyyy}/${mm}/${dd} ${hh}:${min}`
}

// データ取得
const fetchData = async () => {
    loading.value = true
    errorMessage.value = ''
    try {
        const id = route.params.id
        const response = await api.get(`/admin/workshops/${id}`)

        item.value = response.data?.item || null
        if (response.data?.index_url) {
            indexUrl.value = response.data.index_url
        }
    } catch (error) {
        console.error('詳細データの取得に失敗しました:', error)
        errorMessage.value = 'ワークショップ情報の取得に失敗しました。'
    } finally {
        loading.value = false
    }
}

// 削除処理
const handleDelete = async () => {
    if (!confirm('本当にこのワークショップを削除しますか？')) return

    deleting.value = true
    try {
        const id = route.params.id
        await api.delete(`/admin/workshops/${id}`)
        alert('ワークショップを削除しました。')
        router.push(indexUrl.value)
    } catch (error) {
        console.error('削除に失敗しました:', error)
        alert(error.response?.data?.message || '削除処理中にエラーが発生しました。')
    } finally {
        deleting.value = false
    }
}

const goIndex = () => {
    router.push(indexUrl.value)
}

const goEdit = () => {
    const id = route.params.id
    router.push(`/admin/workshops/${id}/edit`)
}

onMounted(() => {
    fetchData()
})
</script>