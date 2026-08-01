<template>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- ヘッダーエリア -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <!-- 🔙 戻るボタン ＆ タイトル -->
            <div class="flex items-center gap-3">
                <button
                    @click="$router.back()"
                    class="inline-flex items-center gap-1 px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-all active:scale-95 shadow-2xs cursor-pointer shrink-0"
                    title="前の画面に戻る"
                >
                    <span>‹</span>
                    <span>戻る</span>
                </button>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                            {{ medicalInstitutionName ? `${medicalInstitutionName} - 所属メンバー` : '所属メンバー一覧' }}
                        </h1>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        対象の医療機関に所属しているユーザーの一覧を表示しています
                    </p>
                </div>
            </div>

            <!-- アクションエリア -->
            <div class="flex items-center gap-3">
                <router-link
                    v-if="medicalInstitutionId"
                    :to="`/admin/medical-institutions/${medicalInstitutionId}`"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all active:scale-95 cursor-pointer"
                >
                    <span>🏥</span>
                    <span>医療機関詳細へ</span>
                </router-link>
            </div>
        </div>

        <!-- 🏥 医療機関の基本情報サマリーカード -->
        <div v-if="medicalInstitution" class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-md">ID: #{{ medicalInstitution.id }}</span>
                    <h2 class="text-lg font-bold text-slate-900">{{ medicalInstitution.name }}</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span>📍 〒{{ formatPostcode(medicalInstitution.postcode) }} {{ medicalInstitution.address }}</span>
                    <span>📞 {{ medicalInstitution.phone }}</span>
                </p>
            </div>
            <div class="text-xs text-indigo-900 bg-indigo-100/60 px-3 py-2 rounded-xl shrink-0">
                メンバー総数: <span class="font-bold text-base ml-1">{{ users.length }}</span> 名
            </div>
        </div>

        <!-- ローディング状態 -->
        <div v-if="loading" class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500 shadow-2xs">
            <div class="inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-3"></div>
            <p class="text-sm font-semibold">所属メンバー情報を読み込み中...</p>
        </div>

        <!-- エラー状態 -->
        <div v-else-if="error" class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ error }}</span>
        </div>

        <!-- メインコンテンツ -->
        <div v-else class="space-y-4">
            <!-- 📱 スマホ表示: カード型レイアウト (sm未満で表示) -->
            <div class="block sm:hidden space-y-3">
                <div
                    v-for="user in users"
                    :key="user.id"
                    class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs space-y-3"
                >
                    <!-- ヘッダー: 氏名、代表者バッジ、ステータス -->
                    <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-2.5">
                        <div class="space-y-1">
                            <span class="font-mono text-xs text-slate-400">#{{ user.id }}</span>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <h3 class="font-bold text-slate-900 text-base">{{ user.name }}</h3>
                                <!-- 代表者バッジ -->
                                <span
                                    v-if="isRepresentative(user)"
                                    class="px-2 py-0.5 bg-amber-100 text-amber-800 border border-amber-300 rounded text-[10px] font-bold"
                                >
                                    ★ 代表者
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 font-mono break-all">{{ user.email }}</p>
                        </div>
                        <span
                            :class="getStatusBadgeClass(user.status)"
                            class="px-2 py-0.5 rounded-full text-xs font-semibold inline-flex items-center gap-1 shrink-0"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>{{ getStatusLabel(user.status) }}</span>
                        </span>
                    </div>

                    <!-- ボディ: 権限 & 区分 -->
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-slate-400 block mb-1">権限</span>
                            <span
                                :class="getRoleBadgeClass(user.role?.name)"
                                class="px-2 py-0.5 rounded-md font-bold tracking-wide uppercase border inline-block"
                            >
                                {{ user.role?.name || 'Unassigned' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-1">区分</span>
                            <span class="font-medium text-slate-700">
                                {{ isRepresentative(user) ? '代表者' : '一般メンバー' }}
                            </span>
                        </div>
                    </div>

                    <!-- フッター: 詳細リンク -->
                    <div v-if="user.show_url" class="pt-2 border-t border-slate-100 flex justify-end">
                        <router-link
                            :to="getRelativePath(user.show_url)"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors"
                        >
                            <span>詳細を見る</span>
                            <span>→</span>
                        </router-link>
                    </div>
                </div>

                <!-- 件数ゼロ時 -->
                <div v-if="users.length === 0" class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-sm">
                    この医療機関に所属しているユーザーはいません。
                </div>
            </div>

            <!-- 💻 PC表示: テーブルレイアウト (sm以上で表示) -->
            <div class="hidden sm:block bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold tracking-wider uppercase">
                                <th class="py-3.5 px-4">ID</th>
                                <th class="py-3.5 px-4">氏名 / メールアドレス</th>
                                <th class="py-3.5 px-4">権限 (Role)</th>
                                <th class="py-3.5 px-4">区分</th>
                                <th class="py-3.5 px-4">ステータス</th>
                                <th class="py-3.5 px-4 text-right">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs sm:text-sm text-slate-700">
                            <tr
                                v-for="user in users"
                                :key="user.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <!-- ID -->
                                <td class="py-4 px-4 font-mono font-medium text-slate-400">
                                    #{{ user.id }}
                                </td>

                                <!-- 氏名 / メールアドレス -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900">{{ user.name }}</span>
                                        <!-- 代表者バッジ -->
                                        <span
                                            v-if="isRepresentative(user)"
                                            class="px-2 py-0.5 bg-amber-100 text-amber-800 border border-amber-300 rounded text-[10px] font-bold"
                                            title="医療機関の代表ユーザー"
                                        >
                                            ★ 代表者
                                        </span>
                                    </div>
                                    <span class="text-xs text-slate-400 font-mono block mt-0.5">{{ user.email }}</span>
                                </td>

                                <!-- 権限 (Role) -->
                                <td class="py-4 px-4">
                                    <span
                                        :class="getRoleBadgeClass(user.role?.name)"
                                        class="px-2.5 py-1 rounded-md text-xs font-bold tracking-wide uppercase border inline-block"
                                    >
                                        {{ user.role?.name || 'Unassigned' }}
                                    </span>
                                </td>

                                <!-- 区分（所属状況） -->
                                <td class="py-4 px-4 text-slate-500 text-xs">
                                    {{ isRepresentative(user) ? '代表者' : '一般メンバー' }}
                                </td>

                                <!-- ステータス -->
                                <td class="py-4 px-4">
                                    <span
                                        :class="getStatusBadgeClass(user.status)"
                                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold inline-flex items-center gap-1"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        <span>{{ getStatusLabel(user.status) }}</span>
                                    </span>
                                </td>

                                <!-- 操作アクション -->
                                <td class="py-4 px-4 text-right">
                                    <router-link
                                        v-if="user.show_url"
                                        :to="getRelativePath(user.show_url)"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 px-2.5 py-1.5 rounded-lg transition-colors"
                                    >
                                        <span>詳細</span>
                                        <span>→</span>
                                    </router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 件数ゼロ時の表示 -->
                <div v-if="users.length === 0" class="p-12 text-center text-slate-400 text-sm">
                    この医療機関に所属しているユーザーはいません。
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../api'

const route = useRoute()

const users = ref([])
const loading = ref(true)
const error = ref(null)

const medicalInstitutionId = computed(() => route.params.id)

// 1件目のユーザーから所属医療機関情報を参照
const medicalInstitution = computed(() => {
    return users.value.length > 0 ? users.value[0].medical_institution : null
})

const medicalInstitutionName = computed(() => {
    return medicalInstitution.value?.name || ''
})

// データの取得処理
const fetchMembers = async () => {
    loading.value = true
    error.value = null

    try {
        const id = route.params.id
        const response = await api.get(`/admin/medical-institutions/${id}/users`)

        users.value = response.data.date || response.data.data || []
    } catch (err) {
        console.error('医療機関メンバー一覧の取得失敗:', err)
        error.value = '所属メンバー情報の取得に失敗しました。時間をおいて再試行してください。'
    } finally {
        loading.value = false
    }
}

// 医療機関代表者かどうか判定
const isRepresentative = (user) => {
    if (!user || !user.medical_institution) return false
    return user.id === user.medical_institution.representative_user_id
}

// フルURLから相対パスを抽出
const getRelativePath = (fullUrl) => {
    if (!fullUrl) return ''
    return fullUrl.replace(/^https?:\/\/[^\/]+(\/api)?/, '')
}

// 郵便番号フォーマット
const formatPostcode = (code) => {
    if (!code) return ''
    const str = String(code).replace(/-/g, '')
    if (str.length === 7) {
        return `${str.slice(0, 3)}-${str.slice(3)}`
    }
    return code
}

// ロールバッジのスタイル定義
const getRoleBadgeClass = (roleName) => {
    switch (roleName?.toLowerCase()) {
        case 'admin':
        case 'administrator':
            return 'bg-rose-50 text-rose-700 border-rose-200'
        case 'director':
            return 'bg-purple-50 text-purple-700 border-purple-200'
        case 'medical_staff':
            return 'bg-blue-50 text-blue-700 border-blue-200'
        case 'member':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200'
        default:
            return 'bg-slate-100 text-slate-600 border-slate-200'
    }
}

// ステータス表示ラベル
const getStatusLabel = (status) => {
    switch (status) {
        case 1:
            return '有効'
        case 0:
            return '保留・未承認'
        case -1:
            return '停止中'
        default:
            return '不明'
    }
}

// ステータスバッジのスタイル定義
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 1:
            return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
        case 0:
            return 'bg-amber-50 text-amber-700 border border-amber-200'
        case -1:
            return 'bg-rose-50 text-rose-700 border border-rose-200'
        default:
            return 'bg-slate-100 text-slate-600 border-slate-200'
    }
}

onMounted(() => {
    fetchMembers()
})
</script>