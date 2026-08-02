<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-2xl mx-auto space-y-6">

            <!-- ヘッダー -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800">
                        ✏️ {{ title }}
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">必要な情報を変更して更新してください</p>
                </div>
                <router-link
                    v-if="indexUrl"
                    :to="indexUrl"
                    class="px-3.5 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition-all cursor-pointer"
                >
                    キャンセル
                </router-link>
            </div>

            <!-- エラー表示エリア -->
            <div
                v-if="errorMessage"
                class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm font-medium flex items-center justify-between gap-3"
            >
                <div class="flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ errorMessage }}</span>
                </div>
                <button @click="errorMessage = ''" class="text-rose-400 hover:text-rose-600 font-bold text-xs p-1 cursor-pointer">
                    ✕
                </button>
            </div>

            <!-- ローディング表示 -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
                <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm font-medium">データを読み込み中…</p>
            </div>

            <!-- 入力フォーム -->
            <form v-else @submit.prevent="handleSubmit" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-5">

                <!-- 名称 (基本必須) -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">
                        名称 <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="例: お知らせ"
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>

                <!-- 動的フィールド群 -->
                <!-- 1. スラッグ (slug) -->
                <div v-if="hasField('slug')">
                    <label class="block text-xs font-bold text-slate-600 mb-1">スラッグ (Slug)<span class="text-rose-500">*</span></label>
                    <input
                        v-model="form.slug"
                        type="text"
                        placeholder="例: news"
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>

                <!-- 2. 表示順 (sort_order) -->
                <div v-if="hasField('sort_order')">
                    <label class="block text-xs font-bold text-slate-600 mb-1">表示順 (Sort Order)<span class="text-rose-500">*</span></label>
                    <input
                        v-model.number="form.sort_order"
                        type="number"
                        placeholder="0"
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>

                <!-- 3. セクション (section) -->
                <div v-if="hasField('section')">
                    <label class="block text-xs font-bold text-slate-600 mb-1">セクション<span class="text-rose-500">*</span></label>
                    <select
                        v-model="form.section"
                        required
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white"
                    >
                        <option value="" disabled>セクションを選択してください</option>
                        <option v-for="opt in sectionOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>

                <!-- 4. 表示タイプ (display_type) -->
                <div v-if="hasField('display_type')">
                    <label class="block text-xs font-bold text-slate-600 mb-1">表示タイプ (Display Type)<span class="text-rose-500">*</span></label>
                    <select
                        v-model="form.display_type"
                        required
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white font-mono"
                    >
                        <option value="" disabled>表示タイプを選択してください</option>
                        <option v-for="opt in displayTypeOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>

                <!-- 5. 所属カテゴリー選択 (category_id) -->
                <div v-if="hasField('category_id')">
                    <label class="block text-xs font-bold text-slate-600 mb-1">
                        所属カテゴリー (category_id) <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-model="form.category_id"
                        required
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white font-mono"
                    >
                        <option value="" disabled>カテゴリーを選択してください</option>
                        <option v-for="cat in categoryOptions" :key="cat.id" :value="cat.id">
                            #{{ cat.id }} - {{ cat.name }} ({{ cat.slug }})
                        </option>
                    </select>
                    <p v-if="categoryOptions.length === 0" class="text-xs text-amber-600 mt-1">選択可能なカテゴリーを取得中か、存在しません。</p>
                </div>

                <!-- 6. 親サブカテゴリー選択 (parent_id) -->
                <div v-if="hasField('parent_id')">
                    <label class="block text-xs font-bold text-slate-600 mb-1">
                        親サブカテゴリー (parent_id)
                    </label>
                    <select
                        v-model="form.parent_id"
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white font-mono"
                    >
                        <option :value="null">-- 親サブカテゴリーなし (ルート) --</option>
                        <option v-for="sub in subCategoryOptions" :key="sub.id" :value="sub.id">
                            #{{ sub.id }} - {{ sub.name }} ({{ sub.slug }})
                        </option>
                    </select>
                </div>

                <!-- 送信ボタン -->
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <router-link
                        v-if="indexUrl"
                        :to="indexUrl"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-all cursor-pointer"
                    >
                        キャンセル
                    </router-link>
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        {{ isSubmitting ? '更新中...' : '更新する' }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api.js'

const props = defineProps({
    title: {
        type: String,
        default: 'カテゴリー編集'
    },
    fetchUrl: {
        type: String,
        required: true
    },
    submitUrl: {
        type: String,
        required: true
    },
    indexUrl: {
        type: String,
        default: ''
    },
    fields: {
        type: Array,
        default: () => ['slug', 'sort_order']
    },
    sectionOptions: {
        type: Array,
        default: () => [
            { value: 'download', label: 'download' },
            { value: 'main_menu', label: 'main_menu' },
            { value: 'special', label: 'special' }
        ]
    },
    displayTypeOptions: {
        type: Array,
        default: () => [
            { value: 'list', label: 'list' },
            { value: 'year_archive', label: 'year_archive' },
            { value: 'subcategory', label: 'subcategory' }
        ]
    },
    categoryOptions: {
        type: Array,
        default: () => []
    },
    subCategoryOptions: {
        type: Array,
        default: () => []
    }
})

const router = useRouter()
const loading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')

const form = reactive({
    name: '',
    slug: '',
    sort_order: 0,
    section: '',
    display_type: '',
    category_id: '',
    parent_id: null
})

const hasField = (fieldName) => props.fields.includes(fieldName)

// 初期データの取得 (GET)
const fetchCategory = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const res = await api.get(props.fetchUrl)

        const rawData = res.data
        const data = rawData.role
            || rawData.permission
            || rawData.content_subcategory
            || rawData.content_category
            || rawData.notice_category
            || rawData.category
            || rawData.item
            || rawData.data
            || rawData

        if (data && typeof data === 'object') {
            form.name = data.name ?? ''
            form.slug = data.slug ?? ''
            form.sort_order = data.sort_order ?? 0
            form.section = data.section ?? ''
            form.display_type = data.display_type ?? ''

            // 型揺れ（文字列/数値）を統一・ネストされたデータ構造にも対応
            form.category_id = data.category_id ?? data.content_category_id ?? data.category?.id ?? ''
            form.parent_id = data.parent_id ?? data.parent_subcategory_id ?? null
        }
    } catch (error) {
        console.error('データ取得エラー:', error)
        errorMessage.value = 'データの読み込みに失敗しました。'
    } finally {
        loading.value = false
    }
}

// フォーム更新処理 (PUT)
const handleSubmit = async () => {
    isSubmitting.value = true
    errorMessage.value = ''

    const payload = { name: form.name }
    props.fields.forEach(field => {
        if (field in form) {
            payload[field] = (form[field] === '' || form[field] === undefined) ? null : form[field]
        }
    })

    try {
        await api.put(props.submitUrl, payload)

        if (props.indexUrl) {
            router.push(props.indexUrl)
        } else {
            router.back()
        }
    } catch (error) {
        console.error('更新エラー:', error)
        errorMessage.value = error.response?.data?.message
            || error.response?.data?.error
            || '更新に失敗しました。入力内容をご確認ください。'
    } finally {
        isSubmitting.value = false
    }
}

onMounted(() => {
    fetchCategory()
})
</script>