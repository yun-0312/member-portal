<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-4 sm:p-6 md:p-10">
        <div class="max-w-3xl mx-auto space-y-6">

            <!-- 1. ヘッダーエリア -->
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                        <span>🏥</span> 医療機関新規登録
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">新しい医療機関の情報を入力して登録します</p>
                </div>

                <!-- 戻るボタン -->
                <router-link
                    to="/admin/medical-institutions"
                    class="inline-flex items-center gap-1 px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-all active:scale-95 shadow-2xs cursor-pointer shrink-0"
                >
                    <span>‹</span>
                    <span>一覧へ戻る</span>
                </router-link>
            </div>

            <!-- 2. アラートメッセージ -->
            <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs sm:text-sm font-medium flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ errorMessage }}</span>
                </div>
                <button @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 font-bold cursor-pointer">✕</button>
            </div>

            <!-- 3. 入力フォーム -->
            <form @submit.prevent="handleSubmit" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-8 space-y-6">

                <!-- 医療機関名 (必須) -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs sm:text-sm font-bold text-slate-700">
                        医療機関名 <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="例: ○○総合病院"
                        class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border rounded-xl transition-all focus:bg-white focus:outline-none focus:ring-2"
                        :class="errors.name ? 'border-rose-300 focus:ring-rose-400' : 'border-slate-200 focus:ring-indigo-500'"
                    />
                    <p v-if="errors.name" class="text-xs text-rose-500 font-medium pt-0.5">{{ errors.name[0] }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- 郵便番号 -->
                    <div class="space-y-1.5">
                        <label for="postcode" class="block text-xs sm:text-sm font-bold text-slate-700">
                            郵便番号 <span class="text-xs font-normal text-slate-400">(ハイフンあり)</span>
                        </label>
                        <input
                            id="postcode"
                            v-model="form.postcode"
                            type="text"
                            placeholder="例: 123-4567"
                            maxlength="8"
                            class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border rounded-xl font-mono transition-all focus:bg-white focus:outline-none focus:ring-2"
                            :class="errors.postcode ? 'border-rose-300 focus:ring-rose-400' : 'border-slate-200 focus:ring-indigo-500'"
                        />
                        <p v-if="errors.postcode" class="text-xs text-rose-500 font-medium pt-0.5">{{ errors.postcode[0] }}</p>
                    </div>

                    <!-- 電話番号 -->
                    <div class="space-y-1.5">
                        <label for="phone" class="block text-xs sm:text-sm font-bold text-slate-700">
                            電話番号
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            placeholder="例: 03-1234-5678"
                            class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border rounded-xl font-mono transition-all focus:bg-white focus:outline-none focus:ring-2"
                            :class="errors.phone ? 'border-rose-300 focus:ring-rose-400' : 'border-slate-200 focus:ring-indigo-500'"
                        />
                        <p v-if="errors.phone" class="text-xs text-rose-500 font-medium pt-0.5">{{ errors.phone[0] }}</p>
                    </div>
                </div>

                <!-- 住所 -->
                <div class="space-y-1.5">
                    <label for="address" class="block text-xs sm:text-sm font-bold text-slate-700">
                        住所
                    </label>
                    <input
                        id="address"
                        v-model="form.address"
                        type="text"
                        placeholder="例: 東京都千代田区1-1-1"
                        class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border rounded-xl transition-all focus:bg-white focus:outline-none focus:ring-2"
                        :class="errors.address ? 'border-rose-300 focus:ring-rose-400' : 'border-slate-200 focus:ring-indigo-500'"
                    />
                    <p v-if="errors.address" class="text-xs text-rose-500 font-medium pt-0.5">{{ errors.address[0] }}</p>
                </div>

                <!-- ボタンエリア -->
                <div class="pt-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-end gap-3">
                    <router-link
                        to="/admin/medical-institutions"
                        class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-all text-center"
                    >
                        キャンセル
                    </router-link>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer"
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api.js'

const router = useRouter()

const form = ref({
    name: '',
    postcode: '',
    address: '',
    phone: ''
})

const submitting = ref(false)
const errorMessage = ref('')
const errors = ref({})

const handleSubmit = async () => {
    submitting.value = true
    errorMessage.value = ''
    errors.value = {}

    try {
        const payload = {
            name: form.value.name,
            postcode: form.value.postcode || null,
            address: form.value.address || null,
            phone: form.value.phone || null,
            representative_user_id: null // 新規時はnull固定で送信
        }

        await api.post('/admin/medical-institutions', payload)

        router.push({
            path: '/admin/medical-institutions',
            query: { message: '医療機関を新規登録しました。ユーザー登録後、代表者を登録してください。' }
        })
    } catch (error) {
        console.error('登録エラー:', error)

        if (error.response?.status === 422 && error.response?.data?.errors) {
            errors.value = error.response.data.errors
            errorMessage.value = '入力内容に不備があります。確認してください。'
        } else {
            errorMessage.value = error.response?.data?.message || '登録処理に失敗しました。'
        }
    } finally {
        submitting.value = false
    }
}
</script>