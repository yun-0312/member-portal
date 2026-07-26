<template>
    <div class="max-w-4xl mx-auto p-4 md:p-6 space-y-6">
        <!-- ページヘッダーエリア -->
        <div class="border-b border-slate-200 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">会員詳細情報</h1>
            <p class="text-sm text-slate-500 mt-1">登録中の会員アカウントおよび所属医療機関の情報</p>
        </div>

        <!-- ヘッダー右側のアクションボタンエリア -->
        <div v-if="profileData" class="flex flex-wrap items-center gap-2">
            <!-- 所属医療機関のユーザー一覧ボタン（nullでない場合のみ表示） -->
            <router-link
            v-if="profileData.users_url"
            :to="formatUrl(profileData.users_url)"
            class="inline-flex items-center text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-3.5 py-2 rounded-lg transition-colors"
            >
            所属機関のメンバー一覧
            </router-link>

            <!-- 退職手続きリンク（nullでない場合のみ表示） -->
            <router-link
            v-if="profileData.retire_url"
            :to="formatUrl(profileData.retire_url)"
            class="inline-flex items-center text-xs bg-rose-50 hover:bg-rose-100 text-rose-600 font-medium px-3.5 py-2 rounded-lg border border-rose-200 transition-colors"
            >
            退職手続き
            </router-link>
        </div>
    </div>

    <!-- ローディング表示 -->
    <div v-if="isLoading" class="text-center py-12 text-slate-500">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-300 border-t-blue-600 mb-2"></div>
        <p class="text-sm">データを読み込み中...</p>
        </div>

        <!-- プロフィール本体 -->
        <div v-else-if="user" class="space-y-6">
        <!-- 退職メッセージ（nullでない場合のみ表示） -->
        <div
            v-if="profileData?.retired_message"
            class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 leading-relaxed flex items-start gap-2"
        >
            <svg class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div>{{ profileData.retired_message }}</div>
        </div>

        <!-- 1. ユーザー基本情報カード -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 gap-2">
            <h2 class="text-base font-bold text-slate-800">基本情報</h2>

            <div class="flex items-center gap-2">
                <!-- ステータスバッジ -->
                <span
                :class="[
                    'text-xs font-bold px-2.5 py-1 rounded-full border',
                    user.status === 1
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-amber-50 text-amber-700 border-amber-200'
                ]"
                >
                {{ user.status === 1 ? '承認済み' : '未承認' }}
                </span>

                <!-- 会員情報を編集ボタン（基本情報の横に移動） -->
                <router-link
                v-if="profileData?.user_update_url"
                :to="formatUrl(profileData.user_update_url)"
                class="inline-flex items-center text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium px-3 py-1.5 rounded-lg transition-colors shadow-sm"
                >
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                編集
                </router-link>
            </div>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <!-- 氏名 -->
            <div>
                <dt class="text-slate-400 font-medium">氏名</dt>
                <dd class="text-slate-800 font-semibold text-sm mt-0.5">{{ user.name }}</dd>
            </div>

            <!-- 権限区分（role.name） -->
            <div>
                <dt class="text-slate-400 font-medium">権限区分</dt>
                <dd class="text-slate-800 font-medium mt-0.5">
                <span class="inline-block bg-slate-100 px-2 py-0.5 rounded text-slate-600 uppercase font-mono">
                    {{ user.role?.name || '未設定' }}
                </span>
                </dd>
            </div>

            <!-- メールアドレス -->
            <div>
                <dt class="text-slate-400 font-medium">メールアドレス</dt>
                <dd class="text-slate-800 font-mono mt-0.5">{{ user.email }}</dd>
            </div>

            <!-- メール認証日時 -->
            <div>
                <dt class="text-slate-400 font-medium">メール認証日時</dt>
                <dd class="text-slate-700 font-mono mt-0.5">
                {{ formatDate(user.email_verified_at) }}
                </dd>
            </div>

            <!-- アカウント承認日時 -->
            <div>
                <dt class="text-slate-400 font-medium">アカウント承認日時</dt>
                <dd class="text-slate-700 font-mono mt-0.5">
                {{ formatDate(user.approved_at) }}
                </dd>
            </div>

            <!-- 登録日時 -->
            <div>
                <dt class="text-slate-400 font-medium">会員登録日時</dt>
                <dd class="text-slate-700 font-mono mt-0.5">
                {{ formatDate(user.created_at) }}
                </dd>
            </div>
            </dl>
        </div>

        <!-- 2. 所属医療機関情報カード -->
        <div v-if="user.medical_institution" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 gap-2">
            <h2 class="text-base font-bold text-slate-800">所属医療機関情報</h2>

            <!-- 医療機関詳細ボタン（medical_institution_url がある場合表示） -->
            <router-link 
                v-if="profileData?.medical_institution_url" 
                :to="formatUrl(profileData.medical_institution_url)"
                class="inline-flex items-center text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-3 py-1.5 rounded-lg transition-colors"
            >
                医療機関詳細
                <svg class="w-3.5 h-3.5 ml-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </router-link>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <!-- 医療機関名 -->
            <div class="sm:col-span-2">
                <dt class="text-slate-400 font-medium">医療機関名</dt>
                <dd class="text-slate-800 font-bold text-sm mt-0.5">
                {{ user.medical_institution.name }}
                </dd>
            </div>

            <!-- 電話番号 -->
            <div>
                <dt class="text-slate-400 font-medium">電話番号</dt>
                <dd class="text-slate-800 font-mono mt-0.5">
                {{ user.medical_institution.phone }}
                </dd>
            </div>

            <!-- 所在地 -->
            <div class="sm:col-span-2">
                <dt class="text-slate-400 font-medium">所在地</dt>
                <dd class="text-slate-800 mt-0.5 leading-relaxed">
                {{ user.medical_institution.address }}
                </dd>
            </div>
            </dl>
        </div>
        </div>

        <!-- エラー・データ未取得 -->
        <div v-else class="text-center py-12 bg-white rounded-xl border border-slate-200 text-slate-500">
        会員情報を取得できませんでした。
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../api.js'

const route = useRoute()
const profileData = ref(null)
const isLoading = ref(false)

const user = computed(() => profileData.value?.user || null)

// APIからのデータ取得
const fetchUserProfile = async () => {
    const userId = route.params.id || route.params.userId || route.params.user

    if (!userId) {
        console.error('ユーザーIDが指定されていません。')
        return
    }

    isLoading.value = true
    try {
        const response = await api.get(`/users/${userId}`)
        profileData.value = response.data
    } catch (error) {
        console.error('会員情報の取得に失敗しました:', error)
        profileData.value = null
    } finally {
        isLoading.value = false
    }
}

// URLがスラッシュから始まらない相対パスの場合、先頭にスラッシュを補正する helper
const formatUrl = (url) => {
    if (!url) return '#'
    return url.startsWith('/') ? url : `/${url}`
}

// 日時フォーマット関数
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
    fetchUserProfile()
})
</script>