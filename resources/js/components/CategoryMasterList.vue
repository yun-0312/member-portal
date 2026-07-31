<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-6xl mx-auto space-y-6">

        <!-- 1. ヘッダーエリア -->
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <span>📁</span> {{ title }}
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">マスターデータの確認および管理が行えます</p>
            </div>

            <!-- アクションボタンエリア -->
            <div class="flex items-center gap-3">
            <!-- ➕ 親カテゴリー新規登録 -->
            <router-link
                v-if="paginationData?.store_url"
                :to="getRelativePath(paginationData.store_url + '/create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all active:scale-95 cursor-pointer"
            >
                <span>＋</span>
                <span>親カテゴリー追加</span>
            </router-link>

            <!-- ➕ サブカテゴリー新規登録 (content-categories固有) -->
            <router-link
                v-if="paginationData?.subcategory_store_url"
                :to="getRelativePath(paginationData.subcategory_store_url + '/create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all active:scale-95 cursor-pointer"
            >
                <span>＋</span>
                <span>サブカテゴリー追加</span>
            </router-link>

            <!-- 管理トップへ戻るボタン -->
            <router-link
                to="/admin/management"
                class="inline-flex items-center gap-1 px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-all active:scale-95 shadow-2xs cursor-pointer shrink-0"
                title="管理トップに戻る"
            >
                <span>‹</span>
                <span>管理画面へ戻る</span>
            </router-link>
            </div>
        </div>

        <!-- 2. ローディング表示 -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
            <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-medium">データを読み込み中…</p>
        </div>

        <!-- 3. エラー表示 -->
        <div v-else-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ errorMessage }}</span>
        </div>

        <!-- 4. データテーブル -->
        <div v-else class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <th class="py-3.5 px-4 w-16 text-center">ID</th>
                    <th class="py-3.5 px-4">名称</th>
                    <th v-if="hasColumn('slug')" class="py-3.5 px-4">スラッグ</th>
                    <th v-if="hasColumn('section')" class="py-3.5 px-4">セクション / タイプ</th>
                    <th v-if="hasColumn('sort_order')" class="py-3.5 px-4 w-24 text-center">表示順</th>
                    <th v-if="hasColumn('roles')" class="py-3.5 px-4">アクセス権限</th>
                    <th class="py-3.5 px-4 w-28 text-center">操作</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm font-medium text-slate-700">
                <tr v-if="items.length === 0">
                    <td :colspan="columnCount" class="py-12 text-center text-slate-400">
                    データが登録されていません
                    </td>
                </tr>
                <template v-for="item in items" :key="item.id">
                    <!-- 親カテゴリー行 -->
                    <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3.5 px-4 text-center font-mono text-slate-400 text-xs">
                        #{{ item.id }}
                    </td>

                    <td class="py-3.5 px-4 font-bold text-slate-800">
                        <div class="flex items-center gap-2">
                        <span>{{ item.name }}</span>
                        <span v-if="item.subcategories && item.subcategories.length > 0" class="text-xs px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full font-normal">
                            sub {{ item.subcategories.length }} 件
                        </span>
                        </div>
                    </td>

                    <td v-if="hasColumn('slug')" class="py-3.5 px-4 font-mono text-xs text-slate-500">
                        {{ item.slug || '-' }}
                    </td>

                    <!-- セクション / タイプ -->
                    <td v-if="hasColumn('section')" class="py-3.5 px-4">
                        <div class="flex flex-col items-start gap-1 text-xs">
                            <span v-if="item.section" class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md font-semibold">
                                {{ item.section }}
                            </span>
                            <span v-if="item.display_type" class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-md font-medium">
                                {{ item.display_type }}
                            </span>
                        </div>
                    </td>

                    <td v-if="hasColumn('sort_order')" class="py-3.5 px-4 text-center font-mono">
                        <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-600 text-xs font-semibold">
                        {{ item.sort_order ?? '-' }}
                        </span>
                    </td>

                    <td v-if="hasColumn('roles')" class="py-3.5 px-4">
                        <div class="flex flex-wrap gap-1">
                        <span
                            v-for="role in item.roles"
                            :key="role.id"
                            class="px-2 py-0.5 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-semibold rounded-md"
                        >
                            {{ role.name }}
                        </span>
                        <span v-if="!item.roles || item.roles.length === 0" class="text-slate-400 text-xs">-</span>
                        </div>
                    </td>

                    <td class="py-3 px-3 text-center">
                        <router-link
                        v-if="item.show_url"
                        :to="getRelativePath(item.show_url)"
                        class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 border border-indigo-200 rounded-lg transition-all active:scale-95 cursor-pointer"
                        >
                        詳細 / 編集
                        </router-link>
                    </td>
                    </tr>

                    <!-- 階層表現：サブカテゴリー行（存在する場合） -->
                    <tr
                    v-for="sub in item.subcategories"
                    :key="'sub-' + sub.id"
                    class="bg-slate-50/40 hover:bg-slate-100/50 transition-colors border-l-4 border-indigo-300"
                    >
                    <td class="py-2.5 px-4 text-center font-mono text-slate-400 text-xs">
                        #{{ sub.id }}
                    </td>
                    <td class="py-2.5 px-4 pl-8 text-xs font-medium text-slate-700">
                        <span class="text-slate-300 mr-2">└</span>
                        <span>{{ sub.name }}</span>
                    </td>
                    <td v-if="hasColumn('slug')" class="py-2.5 px-4 font-mono text-xs text-slate-400">
                        {{ sub.slug || '-' }}
                    </td>
                    <td v-if="hasColumn('section')" class="py-2.5 px-4">
                        <span class="px-2 py-0.5 bg-amber-50/80 text-amber-700 border border-amber-100 text-xs rounded-md">
                        {{ sub.display_type }}
                        </span>
                    </td>
                    <td v-if="hasColumn('sort_order')" class="py-2.5 px-4 text-center font-mono text-xs text-slate-500">
                        {{ sub.sort_order ?? '-' }}
                    </td>
                    <td v-if="hasColumn('roles')" class="py-2.5 px-4">
                        <div class="flex flex-wrap gap-1">
                            <!-- 1. サブカテゴリーに固有のロールが割り当てられている場合 -->
                            <template v-if="sub.roles && sub.roles.length > 0">
                                <span
                                    v-for="role in sub.roles"
                                    :key="role.id"
                                    class="px-2 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-md"
                                >
                                    {{ role.name }}
                                </span>
                            </template>

                            <!-- 2. サブカテゴリーにロールがない場合は "(親に準拠)" を表示 -->
                            <span v-else class="text-slate-400 text-xs italic">
                                (親に準拠)
                            </span>
                        </div>
                    </td>
                    <td class="py-2.5 px-4 text-center">
                        <router-link
                        v-if="sub.show_url"
                        :to="getRelativePath(sub.show_url)"
                        class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-white border border-slate-200 rounded-md transition-all cursor-pointer"
                        >
                        編集
                        </router-link>
                    </td>
                    </tr>
                </template>
                </tbody>
            </table>
            </div>

            <!-- 5. ページネーション Footer -->
            <div
            v-if="paginationData && paginationData.last_page > 1"
            class="flex items-center justify-between px-6 py-4 bg-slate-50/50 border-t border-slate-200/80 text-xs text-slate-500"
            >
            <div>
                全 <span class="font-bold text-slate-700">{{ paginationData.total }}</span> 件中
                <span class="font-bold text-slate-700">{{ paginationData.from }}</span> -
                <span class="font-bold text-slate-700">{{ paginationData.to }}</span> 件を表示
            </div>

            <div class="flex items-center gap-1">
                <button
                v-for="(link, idx) in paginationData.links"
                :key="idx"
                @click="fetchPage(link.url)"
                :disabled="!link.url || link.active"
                v-html="formatPaginationLabel(link.label)"
                class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all cursor-pointer disabled:cursor-not-allowed"
                :class="[
                    link.active
                    ? 'bg-indigo-600 border-indigo-600 text-white shadow-2xs'
                    : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:hover:bg-white'
                ]"
                ></button>
            </div>
            </div>
        </div>

        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '../api.js'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    apiEndpoint: {
        type: String,
        required: true
    },
    columns: {
        type: Array,
        default: () => ['sort_order']
    }
})

const loading = ref(true)
const errorMessage = ref('')
const items = ref([])
const paginationData = ref(null)

const hasColumn = (columnName) => props.columns.includes(columnName)

const columnCount = computed(() => {
    let count = 3
    if (hasColumn('slug')) count++
    if (hasColumn('section')) count++
    if (hasColumn('sort_order')) count++
    if (hasColumn('roles')) count++
    return count
    })

const getRelativePath = (url) => {
    if (!url) return '#'
    let path = url.replace(/^https?:\/\/[^\/]+/, '')
    if (path.startsWith('/api/')) {
        path = path.replace(/^\/api/, '')
    }
    return path
}

const formatPaginationLabel = (label) => {
    if (label === 'pagination.previous' || label.includes('Previous')) return '‹ 前へ'
    if (label === 'pagination.next' || label.includes('Next')) return '次へ ›'
    return label
}

const fetchData = async (targetUrl = null) => {
    loading.value = true
    errorMessage.value = ''

    try {
        const url = targetUrl ? getRelativePath(targetUrl) : props.apiEndpoint
        const res = await api.get(url)

        items.value = res.data.data || []
        paginationData.value = res.data
    } catch (error) {
        console.error('データの取得に失敗しました:', error)
        errorMessage.value = 'データの読み込みに失敗しました。時間をおいて再度お試しください。'
    } finally {
        loading.value = false
    }
}

const fetchPage = (url) => {
    if (url) {
        fetchData(url)
    }
}

watch(() => props.apiEndpoint, () => {
  fetchData()
})

onMounted(() => {
  fetchData()
})
</script>