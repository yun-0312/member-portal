<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-2xl mx-auto space-y-6">

            <!-- ヘッダー -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800">
                        ➕ {{ title }}
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">必要な情報を入力して登録してください</p>
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

            <!-- 入力フォーム -->
            <form @submit.prevent="handleSubmit" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-5">

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
                    <label class="block text-xs font-bold text-slate-600 mb-1">スラッグ (Slug)</label>
                    <input
                        v-model="form.slug"
                        type="text"
                        placeholder="例: news"
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>

                <!-- 2. 表示順 (sort_order) -->
                <div v-if="hasField('sort_order')">
                    <label class="block text-xs font-bold text-slate-600 mb-1">表示順 (Sort Order)</label>
                    <input
                        v-model.number="form.sort_order"
                        type="number"
                        placeholder="0"
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>

                <!-- 3. セクション (section) -->
                <div v-if="hasField('section')">
                    <label class="block text-xs font-bold text-slate-600 mb-1">セクション</label>
                    <input
                        v-model="form.section"
                        type="text"
                        placeholder="例: main"
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>

                <!-- 親カテゴリー選択 (サブカテゴリー登録時用) -->
                <div v-if="hasField('parent_id')">
                    <label class="block text-xs font-bold text-slate-600 mb-1">
                        親カテゴリー <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-model="form.parent_id"
                        required
                        class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white"
                    >
                        <option value="" disabled>親カテゴリーを選択してください</option>
                        <option v-for="parent in parentOptions" :key="parent.id" :value="parent.id">
                            {{ parent.name }}
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
                        {{ isSubmitting ? '保存中...' : '登録する' }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api.js'

const props = defineProps({
    title: {
        type: String,
        default: 'カテゴリー新規登録'
    },
    submitUrl: {
        type: String,
        required: true
    },
    indexUrl: {
        type: String,
        default: ''
    },
    // 表示・入力させるフィールドの指定
    fields: {
        type: Array,
        default: () => ['slug', 'sort_order']
    },
    // サブカテゴリー登録用の親カテゴリー選択肢リスト
    parentOptions: {
        type: Array,
        default: () => []
    }
})

const router = useRouter()
const isSubmitting = ref(false)
const errorMessage = ref('')

// フォームの入力状態
const form = reactive({
    name: '',
    slug: '',
    sort_order: 0,
    section: '',
    parent_id: ''
})

const hasField = (fieldName) => props.fields.includes(fieldName)

// フォーム送信処理
const handleSubmit = async () => {
    isSubmitting.value = true
    errorMessage.value = ''

    // 表示されているフィールドのみ送信データに含める
    const payload = { name: form.name }
    props.fields.forEach(field => {
        if (field in form) {
            payload[field] = form[field]
        }
    })

    try {
        await api.post(props.submitUrl, payload)

        // 成功したら一覧画面等へリダイレクト
        if (props.indexUrl) {
            router.push(props.indexUrl)
        } else {
            router.back()
        }
    } catch (error) {
        console.error('登録エラー:', error)

        // バリデーションエラー等のメッセージを表示
        errorMessage.value = error.response?.data?.message
            || error.response?.data?.error
            || '登録に失敗しました。入力内容をご確認ください。'
    } finally {
        isSubmitting.value = false
    }
}
</script>