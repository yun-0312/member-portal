<template>
    <div class="max-w-md mx-auto p-4 md:p-6 space-y-6">
        <!-- ヘッダー -->
        <div class="border-b border-slate-200 pb-4">
            <h1 class="text-2xl font-bold text-slate-800">パスワード変更</h1>
            <p class="text-sm text-slate-500 mt-1">現在のパスワードと新しいパスワードを入力してください。</p>
        </div>

        <!-- フォームカード -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <form @submit.prevent="handleSubmit" class="space-y-4">

                <!-- 💡 ブラウザの自動補完・アクセシビリティ用の非表示ユーザー名フィールド -->
                <input
                    type="text"
                    name="username"
                    autocomplete="username"
                    class="hidden"
                    aria-hidden="true"
                />

                <!-- 全体エラーメッセージ -->
                <div
                v-if="errorMessage"
                class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-start gap-2"
                >
                    <svg class="w-4 h-4 text-rose-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>{{ errorMessage }}</div>
                </div>

                <!-- 現在のパスワード -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        現在のパスワード <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.current_password"
                        type="password"
                        autocomplete="current-password"
                        required
                        placeholder="••••••••"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                        :class="errors.current_password ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300'"
                    />
                    <p v-if="errors.current_password" class="text-xs text-rose-600 mt-1">
                        {{ errors.current_password[0] }}
                    </p>
                </div>

                <hr class="border-slate-100 my-2" />

                <!-- 新しいパスワード -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        新しいパスワード <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.new_password"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="8文字以上で入力"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                        :class="errors.new_password ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300'"
                    />
                    <p v-if="errors.new_password" class="text-xs text-rose-600 mt-1">
                        {{ errors.new_password[0] }}
                    </p>
                </div>

                <!-- 新しいパスワード（確認） -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        新しいパスワード（確認） <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.new_password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="もう一度入力してください"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                        :class="errors.new_password_confirmation ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300'"
                    />
                    <p v-if="errors.new_password_confirmation" class="text-xs text-rose-600 mt-1">
                        {{ errors.new_password_confirmation[0] }}
                    </p>
                </div>

                <!-- アクションボタン -->
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button
                        type="button"
                        @click="handleCancel"
                        class="px-4 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
                    >
                        キャンセル
                    </button>
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center px-4 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm disabled:opacity-50"
                    >
                        <span v-if="isSubmitting" class="inline-block animate-spin mr-1.5 h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                        {{ isSubmitting ? '更新中...' : 'パスワードを変更' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api.js' // ディレクトリ階層に応じてパスを調整してください

const router = useRouter()

const isSubmitting = ref(false)
const errorMessage = ref('')
const errors = ref({})

const form = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
})

// キャンセル処理
const handleCancel = () => {
    router.back()
}

// フォーム送信処理
const handleSubmit = async () => {
    isSubmitting.value = true
    errorMessage.value = ''
    errors.value = {}

    try {
        // パスワード変更API送信（/users/password）
        const response = await api.post('/users/password', form)

        alert(response.data?.message || 'パスワードを変更しました。')
        router.back()
    } catch (error) {
        console.error('パスワード変更失敗:', error)

        if (error.response?.status === 422) {
        // Laravelなどのバリデーションエラーレスポンスに対応
        errors.value = error.response.data.errors || {}
        errorMessage.value = error.response.data.message || '入力内容をご確認ください。'
        } else {
        errorMessage.value = error.response?.data?.message || 'パスワードの変更に失敗しました。'
        }
    } finally {
        isSubmitting.value = false
    }
}
</script>