<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- 1. ページヘッダー -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                        <span>🗓️</span> ワークショップ新規作成
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">新しいワークショップの情報を登録します</p>
                </div>
                <button
                    type="button"
                    @click="goBack"
                    class="inline-flex items-center gap-1 text-xs md:text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer"
                >
                    ← 一覧へ戻る
                </button>
            </div>

            <!-- 2. フォームエリア -->
            <form @submit.prevent="handleSubmit" class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-xs space-y-6">

                <!-- エラーメッセージ一覧表示 -->
                <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs md:text-sm space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>⚠️</span> 入力内容にエラーがあります
                    </div>
                    <p>{{ errorMessage }}</p>
                </div>

                <!-- タイトル (title) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        タイトル <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        v-model="form.title"
                        maxlength="255"
                        required
                        placeholder="ワークショップのタイトルを入力"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.title }"
                    />
                    <p v-if="errors.title" class="text-xs text-rose-500 font-medium">{{ errors.title[0] }}</p>
                </div>

                <!-- フォームグループ：2列グリッド（講師 & 開催場所） -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- 講師 (lecture) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            講師 <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.lecture"
                            maxlength="255"
                            required
                            placeholder="講師名を入力"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.lecture }"
                        />
                        <p v-if="errors.lecture" class="text-xs text-rose-500 font-medium">{{ errors.lecture[0] }}</p>
                    </div>

                    <!-- 開催場所 (location) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            開催場所 <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.location"
                            maxlength="255"
                            required
                            placeholder="会場名や住所、オンラインURLなど"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.location }"
                        />
                        <p v-if="errors.location" class="text-xs text-rose-500 font-medium">{{ errors.location[0] }}</p>
                    </div>

                </div>

                <!-- フォームグループ：2列グリッド（開始日時 & 終了日時） -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- 開始日時 (start_at) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            開始日時 <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="datetime-local"
                            v-model="form.start_at"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.start_at }"
                        />
                        <p v-if="errors.start_at" class="text-xs text-rose-500 font-medium">{{ errors.start_at[0] }}</p>
                    </div>

                    <!-- 終了日時 (end_at) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            終了日時 <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="datetime-local"
                            v-model="form.end_at"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.end_at }"
                        />
                        <p v-if="errors.end_at" class="text-xs text-rose-500 font-medium">{{ errors.end_at[0] }}</p>
                    </div>

                </div>

                <!-- 概要・詳細 (description) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        詳細・説明 <span class="text-xs text-slate-400 font-normal">（任意）</span>
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="6"
                        placeholder="ワークショップのアジェンダや概要などを入力してください"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all leading-relaxed"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.description }"
                    ></textarea>
                    <p v-if="errors.description" class="text-xs text-rose-500 font-medium">{{ errors.description[0] }}</p>
                </div>

                <!-- 送信・キャンセルボタン -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <button
                        type="button"
                        @click="goBack"
                        class="px-5 py-2.5 text-xs md:text-sm font-bold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all active:scale-95 cursor-pointer"
                    >
                        キャンセル
                    </button>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="px-6 py-2.5 text-xs md:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer flex items-center gap-2"
                    >
                        <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ submitting ? '登録中…' : '登録する' }}</span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api.js' // APIパスはプロジェクトに合わせて調整してください

const router = useRouter()

const submitting = ref(false)
const errorMessage = ref('')
const errors = ref({})

// 今日の「YYYY-MM-DDTHH:mm」形式を作成するヘルパー
const getDefaultDateTime = (hour) => {
    const d = new Date()

    // 例: 今日の日付で時間を設定（必要に応じて明日などに調整も可能）
    d.setHours(hour, 0, 0, 0)

    const yyyy = d.getFullYear()
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    const hh = String(d.getHours()).padStart(2, '0')
    const min = String(d.getMinutes()).padStart(2, '0')

    return `${yyyy}-${mm}-${dd}T${hh}:${min}`
}

// フォーム初期値の設定
const form = reactive({
    title: '',
    description: '',
    start_at: getDefaultDateTime(20), // ⭕️ 20:00 に設定
    end_at: getDefaultDateTime(21),   // ⭕️ 21:00 に設定
    location: '',
    lecture: ''
})

// 作成処理
const handleSubmit = async () => {
    submitting.value = true
    errorMessage.value = ''
    errors.value = {}

    // datetime-local の値（YYYY-MM-DDTHH:mm）を Laravel が受け取れる形式（YYYY-MM-DD HH:mm:ss）へフォーマット
    const payload = {
        title: form.title,
        description: form.description || null,
        location: form.location,
        lecture: form.lecture,
        start_at: form.start_at ? form.start_at.replace('T', ' ') + ':00' : null,
        end_at: form.end_at ? form.end_at.replace('T', ' ') + ':00' : null,
    }

    try {
        const response = await api.post('/admin/workshops', payload)

        alert('ワークショップを作成しました！')

        // 新規作成されたWorkshopの詳細画面（または一覧画面）へ遷移
        const createdId = response.data?.item?.id || response.data?.id || response.data?.data?.id
        if (createdId) {
            router.push(`/admin/workshops/${createdId}`)
        } else {
            router.push('/admin/workshops')
        }

    } catch (error) {
        console.error('作成処理に失敗しました:', error)
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {}
            errorMessage.value = '入力内容に不備があります。エラー項目を確認してください。'
        } else {
            errorMessage.value = error.response?.data?.message || '作成処理中にエラーが発生しました。'
        }
    } finally {
        submitting.value = false
    }
}

const goBack = () => {
    router.push('/admin/workshops')
}
</script>