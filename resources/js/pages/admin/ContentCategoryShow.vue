<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

        <!-- ヘッダー -->
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">コンテンツカテゴリー詳細</span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800">
                📂 {{ item.name }}
            </h1>
            </div>

            <div class="flex items-center gap-2">
            <router-link
                v-if="data.index_url"
                :to="data.index_url"
                class="px-3.5 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition-all"
            >
                一覧へ戻る
            </router-link>
            <router-link
                v-if="data.update_url"
                :to="data.update_url"
                class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-all"
            >
                編集
            </router-link>
            </div>
        </div>

        <!-- 基本情報 -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <h2 class="text-sm font-bold text-slate-500 uppercase mb-4">基本属性</h2>
            <dl class="divide-y divide-slate-100 text-sm">
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">ID / スラッグ</dt>
                <dd class="col-span-2 font-mono text-slate-700">#{{ item.id }} ({{ item.slug }})</dd>
            </div>
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">セクション / 表示タイプ</dt>
                <dd class="col-span-2 space-x-2">
                <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-xs font-mono">{{ item.section }}</span>
                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-xs font-mono">{{ item.display_type }}</span>
                </dd>
            </div>
            </dl>
        </div>

        <!-- サブカテゴリー (Subcategories) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-3">
            <h2 class="text-sm font-bold text-slate-500 uppercase">サブカテゴリー ({{ item.subcategories?.length || 0 }})</h2>
            <div v-if="item.subcategories && item.subcategories.length > 0" class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
            <div
                v-for="sub in item.subcategories"
                :key="sub.id"
                class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors"
            >
                <div>
                <div class="font-bold text-sm text-slate-800">{{ sub.name }}</div>
                <div class="text-xs font-mono text-slate-400">slug: {{ sub.slug }}</div>
                </div>
                <span class="text-xs font-mono px-2 py-1 bg-slate-100 rounded text-slate-600">
                {{ sub.display_type }}
                </span>
            </div>
            </div>
            <div v-else class="text-slate-400 text-xs py-2">サブカテゴリーはありません</div>
        </div>

        <!-- 閲覧許可ロール (Roles) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-3">
            <h2 class="text-sm font-bold text-slate-500 uppercase">閲覧対象ロール</h2>
            <div class="flex flex-wrap gap-2">
            <span
                v-for="role in item.roles"
                :key="role.id"
                class="px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-lg"
            >
                👥 {{ role.name }}
            </span>
            </div>
        </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api.js'

const route = useRoute()
const data = ref({})
const item = computed(() => data.value.item || {})

const fetchContentCategory = async () => {
    try {
        const res = await api.get(`/admin/content-categories/${route.params.id}`)
        data.value = res.data
    } catch (error) {
        console.error('ContentCategoryの取得に失敗しました:', error)
    }
}

onMounted(fetchContentCategory)
</script>