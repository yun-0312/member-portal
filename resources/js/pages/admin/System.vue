<template>
    <div class="max-w-6xl mx-auto p-4 md:p-8 space-y-6">
        <!-- ヘッダー -->
        <div class="border-b border-gray-200 pb-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">システム管理者ダッシュボード</h1>
            <p class="text-xs md:text-sm text-gray-500 mt-1">各種管理機能へのアクセス</p>
        </div>

        <!-- ローディング表示 -->
        <div v-if="loading" class="bg-white rounded-2xl border border-gray-200 p-12 text-center text-gray-500">
            <div class="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
            <p class="text-sm font-bold">メニューを読み込み中…</p>
        </div>

        <!-- エラー表示 -->
        <div v-else-if="error" class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-xs md:text-sm">
            {{ error }}
        </div>

        <!-- メニューカード一覧 -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            <router-link
                v-for="link in links"
                :key="link.url"
                :to="link.url"
                class="group bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs hover:shadow-md hover:border-blue-500/50 transition-all duration-200 flex items-center justify-between"
            >
                <div class="flex items-center gap-3.5">
                    <!-- アイコン表示（機能名に応じてアイコンを自動付与） -->
                    <div class="w-10 h-10 rounded-xl bg-blue-50 group-hover:bg-blue-600 text-blue-600 group-hover:text-white flex items-center justify-center font-bold transition-colors">
                        <span>{{ getIcon(link.name) }}</span>
                    </div>
                    <div>
                        <h2 class="text-sm md:text-base font-bold text-gray-800 group-hover:text-blue-600 transition-colors">
                            {{ link.name }}
                        </h2>
                    </div>
                </div>

                <!-- 矢印アイコン -->
                <span class="text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all">
                    →
                </span>
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import api from '@/api.js'

const links = ref([])
const loading = ref(true)
const error = ref(null)

// 機能名に応じてアイコン絵文字を割り当てる関数
const getIcon = (name) => {
    if (name.includes('お知らせ')) return '📢'
    if (name.includes('権限')) return '🛡️'
    return '⚙️'
}

// APIからのメニューデータ取得
onMounted(async () => {
    loading.value = true
    error.value = null

    try {
        const response = await api.get('/admin/system')

        // 返ってきた JSON 内の links 配列をセット
        links.value = response.data.links || []
    } catch (err) {
        console.error('メニュー一覧の取得に失敗しました:', err)
        error.value = 'メニューの読み込みに失敗しました。時間をおいて再読み込みしてください。'
    } finally {
        loading.value = false
    }
})
</script>