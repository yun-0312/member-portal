<template>
    <div class="bg-gray-100 min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">パスワード再設定</h2>

        <div v-if="message" class="mb-4 text-green-600 text-sm text-center">
            {{ message }}
        </div>

        <div v-if="errorMessage" class="mb-4 text-red-600 text-sm text-center">
            {{ errorMessage }}
        </div>

        <form @submit.prevent="sendResetLink">
            <label class="block text-sm font-medium text-gray-700">メールアドレス</label>
            <input
            v-model="email"
            type="email"
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm"
            placeholder="your@example.com"
            required
            />

            <button
            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md shadow hover:bg-blue-700 mt-4"
            >
            リセットリンクを送信
            </button>
        </form>
        </div>
    </div>
</template>

<script setup>
    import { ref } from 'vue'
    import api from '../../api.js'

    const email = ref('')
    const message = ref('')
    const errorMessage = ref('')

    const sendResetLink = async () => {
    message.value = ''
    errorMessage.value = ''

    try {
        await api.get('/sanctum/csrf-cookie')

        const res = await api.post('/password/email', {
        email: email.value,
        })

        message.value = res.data.message
    } catch (error) {
        errorMessage.value =
        error.response?.data?.message || 'メール送信に失敗しました'
    }
}
</script>
