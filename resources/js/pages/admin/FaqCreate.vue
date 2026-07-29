<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- 1. ページヘッダー -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                        <span>❓</span> FAQ新規作成
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">新しいよくある質問（FAQ）を登録します</p>
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

                <!-- フォームグループ：2列グリッド（受付日 & カテゴリ） -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- 受付日 (received_at) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            受付日 <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="date"
                            v-model="form.received_at"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.received_at }"
                        />
                        <p v-if="errors.received_at" class="text-xs text-rose-500 font-medium">{{ errors.received_at[0] }}</p>
                    </div>

                    <!-- カテゴリ (category_id) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            カテゴリ <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.category_id"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.category_id }"
                        >
                            <option value="" disabled>カテゴリを選択してください</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                        <p v-if="errors.category_id" class="text-xs text-rose-500 font-medium">{{ errors.category_id[0] }}</p>
                    </div>

                </div>

                <!-- 質問 (question) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        質問 (Question) <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="form.question"
                        maxlength="255"
                        rows="3"
                        required
                        placeholder="質問内容を入力（最大255文字）"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all leading-relaxed"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.question }"
                    ></textarea>
                    <div class="flex justify-between items-center text-xs text-slate-400">
                        <p v-if="errors.question" class="text-rose-500 font-medium">{{ errors.question[0] }}</p>
                        <span class="ml-auto">{{ form.question.length }}/255文字</span>
                    </div>
                </div>

                <!-- 回答 (answer) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        回答 (Answer) <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="form.answer"
                        maxlength="255"
                        rows="4"
                        required
                        placeholder="回答内容を入力（最大255文字）"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all leading-relaxed"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.answer }"
                    ></textarea>
                    <div class="flex justify-between items-center text-xs text-slate-400">
                        <p v-if="errors.answer" class="text-rose-500 font-medium">{{ errors.answer[0] }}</p>
                        <span class="ml-auto">{{ form.answer.length }}/255文字</span>
                    </div>
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
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api.js' // APIパスはプロジェクトに合わせて調整してください

const router = useRouter()

const submitting = ref(false)
const errorMessage = ref('')
const errors = ref({})
const categories = ref([])

// 今日の日付 (YYYY-MM-DD) を初期値として取得
const getTodayDate = () => {
    const today = new Date()
    const yyyy = today.getFullYear()
    const mm = String(today.getMonth() + 1).padStart(2, '0')
    const dd = String(today.getDate()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}`
}

// フォームの初期値設定
const form = reactive({
    received_at: getTodayDate(),
    category_id: '',
    question: '',
    answer: ''
})

// カテゴリ一覧の取得
const fetchCategories = async () => {
    try {
        const response = await api.get('/admin/faq-categories')
        categories.value = response.data?.items || response.data?.data || response.data || []
    } catch (error) {
        console.error('カテゴリ一覧の取得に失敗しました:', error)
    }
}

// 作成処理
const handleSubmit = async () => {
    submitting.value = true
    errorMessage.value = ''
    errors.value = {}

    const payload = {
        received_at: form.received_at,
        category_id: form.category_id,
        question: form.question,
        answer: form.answer,
    }

    try {
        const response = await api.post('/admin/faqs', payload)

        alert('FAQを作成しました！')

        router.push('/admin/faqs')

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
    router.push('/admin/faqs')
}

onMounted(() => {
    fetchCategories()
})
</script>