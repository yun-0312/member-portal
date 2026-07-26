<template>
    <div class="bg-gray-100 min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg w-96">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">
                ログイン
            </h2>

            <div
                v-if="errorMessage"
                class="mb-4 text-red-600 text-sm text-center"
            >
                {{ errorMessage }}
            </div>

            <form @submit.prevent="login">
                <div class="mb-4">
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700"
                        >メールアドレス</label
                    >
                    <input
                        v-model="email"
                        type="email"
                        id="email"
                        name="email"
                        autocomplete="email"
                        placeholder="your@example.com"
                        required
                        :class="[
                            'mt-1 w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm',
                            errorMessage
                                ? 'border-red-500 bg-red-50'
                                : 'border-gray-300',
                        ]"
                    />
                </div>
                <div class="mb-6 relative">
                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700"
                        >パスワード</label
                    >
                    <div class="relative">
                        <input
                            v-model="password"
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            required
                            :class="[
                                'mt-1 w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm',
                                errorMessage
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-gray-300',
                            ]"
                        />
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <input
                            v-model="remember"
                            type="checkbox"
                            id="remember"
                            name="remember"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                        />
                        <label
                            for="remember"
                            class="ml-2 block text-sm text-gray-900"
                            >ログイン状態を保持</label
                        >
                    </div>
                </div>
                <button
                    class="w-full bg-sky-500 text-white py-2 px-4 rounded-md shadow hover:bg-sky-600"
                >
                    ログイン
                </button>
            </form>
            <div class="text-right mt-2">
                <RouterLink
                    to="/forgot-password"
                    class="text-sm text-sky-600 hover:underline"
                >
                    パスワードを忘れた方はこちら
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import api from "../../api";
import { useRouter } from 'vue-router'
const router = useRouter()

const email = ref("");
const password = ref("");
const errorMessage = ref("");
const remember = ref(false);

const login = async () => {
    errorMessage.value = "";

    try {
        await api.get("/sanctum/csrf-cookie");

        const res = await api.post("/login", {
            email: email.value,
            password: password.value,
            remember: remember.value,
        });

        console.log("ログイン成功", res.data);

        localStorage.setItem("user", JSON.stringify(res.data.user));
        localStorage.setItem("token", res.data.token);

        window.dispatchEvent(new Event('user-updated'))
        router.push('/dashboard')
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || "ログインに失敗しました";
    }
};
</script>
