<template>
  <div class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
      <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">新しいパスワード設定</h2>

      <div v-if="message" class="mb-4 text-green-600 text-sm text-center">
        {{ message }}
      </div>

      <div v-if="errorMessage" class="mb-4 text-red-600 text-sm text-center">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="resetPassword">
        <label class="block text-sm font-medium text-gray-700">新しいパスワード</label>
        <input
          v-model="password"
          type="password"
          class="mt-1 w-full px-4 py-2 border rounded-md"
          required
        />

        <label class="block text-sm font-medium text-gray-700 mt-4">確認用パスワード</label>
        <input
          v-model="password_confirmation"
          type="password"
          class="mt-1 w-full px-4 py-2 border rounded-md"
          required
        />

        <button
          class="w-full bg-sky-500 text-white py-2 px-4 rounded-md shadow hover:bg-sky-600 mt-4"
        >
          パスワードを更新する
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api'

const route = useRoute()
const router = useRouter()

const token = route.query.token
const email = route.query.email

const password = ref('')
const password_confirmation = ref('')
const message = ref('')
const errorMessage = ref('')

const resetPassword = async () => {
    message.value = ''
    errorMessage.value = ''

    try {
        await api.get('/sanctum/csrf-cookie')

        const res = await api.post('/password/reset', {
        token,
        email,
        password: password.value,
        password_confirmation: password_confirmation.value,
        })

        message.value = res.data.message

        setTimeout(() => {
        router.push('/')
        }, 1500)

    } catch (error) {
        errorMessage.value =
        error.response?.data?.message || 'パスワード更新に失敗しました'
    }
}
</script>
