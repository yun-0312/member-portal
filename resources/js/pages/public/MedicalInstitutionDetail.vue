<template>
    <div class="max-w-4xl mx-auto p-4 md:p-6 space-y-6">
        <!-- ページヘッダーエリア -->
        <div class="border-b border-slate-200 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">医療機関詳細情報</h1>
            <p class="text-sm text-slate-500 mt-1">登録されている医療機関の基本情報および代表者情報</p>
        </div>

        <!-- ヘッダー右側のアクションボタンエリア -->
        <div v-if="institutionData" class="flex flex-wrap items-center gap-2">
            <!-- 所属メンバー一覧ボタン -->
            <router-link
            v-if="institutionData.users_url"
            :to="formatUrl(institutionData.users_url)"
            class="inline-flex items-center text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-3.5 py-2 rounded-lg transition-colors border border-slate-200"
            >
            <svg class="w-3.5 h-3.5 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            所属メンバー一覧
            </router-link>

            <!-- 医療機関情報編集ボタン -->
            <router-link
            v-if="institutionData.edit_url"
            :to="formatUrl(institutionData.edit_url)"
            class="inline-flex items-center text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium px-3.5 py-2 rounded-lg transition-colors shadow-sm"
            >
            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            編集
            </router-link>
        </div>
        </div>

        <!-- ローディング表示 -->
        <div v-if="isLoading" class="text-center py-12 text-slate-500">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-300 border-t-blue-600 mb-2"></div>
        <p class="text-sm">データを読み込み中...</p>
        </div>

        <!-- 本体コンテンツ -->
        <div v-else-if="institution" class="space-y-6">
        <!-- 1. 医療機関基本情報カード -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
            <h2 class="text-base font-bold text-slate-800">基本情報</h2>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <!-- 医療機関名 -->
            <div class="sm:col-span-2">
                <dt class="text-slate-400 font-medium">医療機関名</dt>
                <dd class="text-slate-800 font-bold text-base mt-0.5">
                {{ institution.name || '-' }}
                </dd>
            </div>

            <!-- 郵便番号 -->
            <div>
                <dt class="text-slate-400 font-medium">郵便番号</dt>
                <dd class="text-slate-800 font-mono mt-0.5">
                {{ formatPostcode(institution.postcode) }}
                </dd>
            </div>

            <!-- 電話番号 -->
            <div>
                <dt class="text-slate-400 font-medium">電話番号</dt>
                <dd class="text-slate-800 font-mono mt-0.5">
                {{ institution.phone || '-' }}
                </dd>
            </div>

            <!-- 所在地 -->
            <div class="sm:col-span-2">
                <dt class="text-slate-400 font-medium">所在地</dt>
                <dd class="text-slate-800 mt-0.5 leading-relaxed">
                {{ institution.address || '-' }}
                </dd>
            </div>

            <!-- 登録日時 -->
            <div>
                <dt class="text-slate-400 font-medium">登録日時</dt>
                <dd class="text-slate-700 font-mono mt-0.5">
                {{ formatDate(institution.created_at) }}
                </dd>
            </div>

            <!-- 更新日時 -->
            <div>
                <dt class="text-slate-400 font-medium">最終更新日時</dt>
                <dd class="text-slate-700 font-mono mt-0.5">
                {{ formatDate(institution.updated_at) }}
                </dd>
            </div>
            </dl>
        </div>

        <!-- 2. 代表者情報カード -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
            <h2 class="text-base font-bold text-slate-800">代表者情報</h2>
            </div>

            <div v-if="institution.representative" class="space-y-4">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <!-- 代表者 氏名 -->
                <div>
                <dt class="text-slate-400 font-medium">代表者氏名</dt>
                <dd class="text-slate-800 font-semibold text-sm mt-0.5">
                    {{ institution.representative.name || '-' }}
                </dd>
                </div>

                <!-- 代表者 メールアドレス -->
                <div>
                <dt class="text-slate-400 font-medium">メールアドレス</dt>
                <dd class="text-slate-800 font-mono mt-0.5">
                    {{ institution.representative.email || '-' }}
                </dd>
                </div>

                <!-- アカウントステータス -->
                <div>
                <dt class="text-slate-400 font-medium">ステータス</dt>
                <dd class="mt-1">
                    <span
                    :class="[
                        'text-xs font-bold px-2.5 py-0.5 rounded-full border',
                        institution.representative.status === 1
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                        : 'bg-amber-50 text-amber-700 border-amber-200'
                    ]"
                    >
                    {{ institution.representative.status === 1 ? '承認済み' : '未承認' }}
                    </span>
                </dd>
                </div>

                <!-- アカウント承認日時 -->
                <div>
                <dt class="text-slate-400 font-medium">アカウント承認日時</dt>
                <dd class="text-slate-700 font-mono mt-0.5">
                    {{ formatDate(institution.representative.approved_at) }}
                </dd>
                </div>
            </dl>
            </div>

            <!-- 代表者未設定時 -->
            <div v-else class="text-xs text-slate-500 py-2">
            代表者情報が登録されていません。
            </div>
        </div>
        </div>

        <!-- エラー・データ未取得 -->
        <div v-else class="text-center py-12 bg-white rounded-xl border border-slate-200 text-slate-500 text-xs">
        医療機関情報を取得できませんでした。
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../api.js'

const route = useRoute()
const institutionData = ref(null)
const isLoading = ref(false)

const institution = computed(() => institutionData.value?.institution || null)

// データ取得
const fetchInstitutionDetail = async () => {
    const institutionId = route.params.id || route.params.medicalInstitutionId

    if (!institutionId) {
        console.error('医療機関IDが指定されていません。')
        return
    }

    isLoading.value = true
    try {
        const response = await api.get(`/medical-institutions/${institutionId}`)
        institutionData.value = response.data
    } catch (error) {
        console.error('医療機関情報の取得に失敗しました:', error)
        institutionData.value = null
    } finally {
        isLoading.value = false
    }
}

// URLフォーマット補助関数
const formatUrl = (url) => {
    if (!url) return '#'

    if (url.startsWith('http://') || url.startsWith('https://')) {
        try {
        return new URL(url).pathname
        } catch {
        return url
        }
    }

    return url.startsWith('/') ? url : `/${url}`
}

// 郵便番号フォーマット (5315166 ➔ 〒531-5166)
const formatPostcode = (code) => {
    if (!code) return '-'
    const cleaned = String(code).replace(/\D/g, '')
    if (cleaned.length === 7) {
        return `〒${cleaned.slice(0, 3)}-${cleaned.slice(3)}`
    }
    return `〒${code}`
}

// 日時フォーマット
const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    const date = new Date(dateStr.replace(/-/g, '/'))
    if (isNaN(date.getTime())) return dateStr

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')

    return `${year}/${month}/${day} ${hours}:${minutes}`
}

onMounted(() => {
    fetchInstitutionDetail()
})
</script>