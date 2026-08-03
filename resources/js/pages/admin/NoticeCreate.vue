<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- 1. ページヘッダー -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                        <span>📢</span> お知らせ新規登録
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">会員や関係者に公開するお知らせを作成します</p>
                </div>
                <button
                    type="button"
                    @click="goBack"
                    class="inline-flex items-center gap-1 text-xs md:text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition-all active:scale-95"
                >
                    ← 一覧へ戻る
                </button>
            </div>

            <!-- 2. フォームエリア -->
            <form @submit.prevent="handleSubmit" class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-xs space-y-6">

                <!-- エラーメッセージ一覧表示 -->
                <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs md:text-sm space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>⚠️</span> 入力内容にエラーがあります
                    </div>
                    <p>{{ errorMessage }}</p>
                </div>

                <!-- フォームグループ：2列グリッド（カテゴリ & 公開日時） -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- カテゴリ (category_id) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            カテゴリ <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.category_id"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.category_id }"
                        >
                            <option value="" disabled>カテゴリを選択してください</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                        <p v-if="errors.category_id" class="text-xs text-rose-500 font-medium">{{ errors.category_id[0] }}</p>
                    </div>

                    <!-- 公開日時 (published_at) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            公開日時 <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="datetime-local"
                            v-model="form.published_at"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.published_at }"
                        />
                        <p v-if="errors.published_at" class="text-xs text-rose-500 font-medium">{{ errors.published_at[0] }}</p>
                    </div>
                </div>

                <!-- タイトル (title) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        タイトル <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        v-model="form.title"
                        maxlength="255"
                        required
                        placeholder="お知らせのタイトルを入力"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.title }"
                    />
                    <p v-if="errors.title" class="text-xs text-rose-500 font-medium">{{ errors.title[0] }}</p>
                </div>

                <!-- 委員会名 (committee_name) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        委員会名 <span class="text-xs text-slate-400 font-normal">（任意）</span>
                    </label>
                    <input
                        type="text"
                        v-model="form.committee_name"
                        maxlength="255"
                        placeholder="例: 総務庶務委員会"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.committee_name }"
                    />
                    <p v-if="errors.committee_name" class="text-xs text-rose-500 font-medium">{{ errors.committee_name[0] }}</p>
                </div>

                <!-- 本文 (body) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        本文 <span class="text-rose-500">*</span>
                    </label>
                    <!-- 💡 ref="editorRef" と @open-media イベントハンドラを追加 -->
                    <TiptapEditor
                        ref="editorRef"
                        v-model="form.body"
                        placeholder="お知らせの詳細内容を入力してください..."
                        @open-media="showMediaModal = true"
                    />
                    <p v-if="errors.body" class="text-xs text-rose-500 font-medium">{{ errors.body[0] }}</p>
                </div>

                <!-- 対象ロール選択 (roles[]) -->
                <div class="space-y-2 border-t border-slate-100 pt-5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        対象ロール制限 <span class="text-xs text-slate-400 font-normal">（未選択の場合は全員公開）</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        <label
                            v-for="role in rolesList"
                            :key="role.id"
                            class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors text-xs font-medium"
                            :class="{ 'border-blue-500 bg-blue-50/40 text-blue-800 font-bold': form.roles.includes(role.id) }"
                        >
                            <input
                                type="checkbox"
                                :value="role.id"
                                v-model="form.roles"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 h-4 w-4"
                            />
                            <span>{{ role.name || role.display_name }}</span>
                        </label>
                    </div>
                    <p v-if="errors.roles" class="text-xs text-rose-500 font-medium">{{ errors.roles[0] }}</p>
                </div>

                <!-- ファイル添付 (file[]) -->
                <div class="space-y-2 border-t border-slate-100 pt-5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        添付ファイル <span class="text-xs text-slate-400 font-normal">（1ファイルあたり最大10MB）</span>
                    </label>

                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-slate-300 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <span class="text-xl mb-1">📁</span>
                                <p class="text-xs text-slate-600 font-medium">クリックしてファイルを選択（複数選択可）</p>
                            </div>
                            <input
                                type="file"
                                multiple
                                class="hidden"
                                @change="handleFileChange"
                            />
                        </label>
                    </div>

                    <!-- 選択済みファイル一覧プレビュー -->
                    <div v-if="selectedFiles.length > 0" class="space-y-1.5 pt-2">
                        <p class="text-xs font-bold text-slate-600">選択中のファイル ({{ selectedFiles.length }}件):</p>
                        <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl bg-slate-50/50 overflow-hidden">
                            <div
                                v-for="(file, index) in selectedFiles"
                                :key="index"
                                class="flex items-center justify-between p-2.5 text-xs text-slate-700"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <span>📄</span>
                                    <span class="truncate font-medium">{{ file.name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">({{ formatFileSize(file.size) }})</span>
                                </div>
                                <button
                                    type="button"
                                    @click="removeFile(index)"
                                    class="text-slate-400 hover:text-rose-600 p-1 rounded-md transition-colors"
                                    title="削除"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-if="errors['file'] || errors['file.0']" class="text-xs text-rose-500 font-medium">
                        {{ errors['file'] ? errors['file'][0] : errors['file.0'][0] }}
                    </p>
                </div>

                <!-- 送信・キャンセルボタン -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <button
                        type="button"
                        @click="goBack"
                        class="px-5 py-2.5 text-xs md:text-sm font-bold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all active:scale-95 cursor-pointer"
                    >
                        キャンセル
                    </button>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="px-6 py-2.5 text-xs md:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer flex items-center gap-2"
                    >
                        <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ submitting ? '登録中…' : 'お知らせを登録する' }}</span>
                    </button>
                </div>

            </form>

        </div>

        <!-- 💡 メディアライブラリモーダル -->
        <MediaLibraryModal
            v-if="showMediaModal"
            v-model="showMediaModal"
            @select="handleSelectImage"
        />

    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../api.js'
import TiptapEditor from '../../components/TiptapEditor.vue'
import MediaLibraryModal from '../../components/MediaLibraryModal.vue'

const router = useRouter()
const route = useRoute()

// リッチテキスト＆モーダル用の ref
const editorRef = ref(null)
const showMediaModal = ref(false)

// フォーム初期値（published_at には現在日時を JST で初期セット）
const now = new Date()
const jstNow = new Date(now.getTime() + 9 * 60 * 60 * 1000)
const defaultPublishedAt = jstNow.toISOString().slice(0, 16) // "YYYY-MM-DDTHH:mm"

const form = reactive({
    category_id: '',
    title: '',
    body: '',
    published_at: defaultPublishedAt,
    committee_name: '',
    roles: []
})

const selectedFiles = ref([])
const categories = ref([])
const rolesList = ref([])

const submitting = ref(false)
const errorMessage = ref('')
const errors = ref({})

// 初期選択肢データ（カテゴリ・ロール）の取得
const fetchMasterData = async () => {
    try {
        const [catRes, roleRes] = await Promise.all([
            api.get('/admin/notice-categories'),
            api.get('/admin/roles')
        ])
        categories.value = catRes.data?.data || catRes.data || []
        rolesList.value = roleRes.data?.data || roleRes.data || []

        // URLクエリからカテゴリを取得 (?category=letter または ?category=1 など)
        const categoryParam = route.query.category

        if (categoryParam && categories.value.length > 0) {
            const paramLower = String(categoryParam).toLowerCase()

            // slug または ID が一致するカテゴリを検索
            const matchedCategory = categories.value.find(cat =>
                String(cat.id) === paramLower ||
                (cat.slug && cat.slug.toLowerCase() === paramLower)
            )

            if (matchedCategory) {
                form.category_id = matchedCategory.id
            }
        }
    } catch (error) {
        console.error('マスターデータの取得に失敗しました:', error)
    }
}

// 該当カテゴリ一覧ページへのリダイレクト処理
const redirectToCategoryList = () => {
    const categoryId = form.category_id

    // 選択されたカテゴリのオブジェクトを検索
    const selectedCategory = categories.value.find(c => c.id === Number(categoryId) || c.id === categoryId)

    // slug や code があれば優先して使用し、無ければ ID を使用
    const categoryParam = selectedCategory?.slug || selectedCategory?.code || categoryId

    if (categoryParam) {
        router.push({
            path: '/admin/notices',
            query: { category: categoryParam }
        })
    } else {
        router.push('/admin/notices')
    }
}

// メディアライブラリで画像が選択された時の処理
const handleSelectImage = (file) => {
    if (file && file.url) {
        editorRef.value?.insertImage(file.url)
    }
    showMediaModal.value = false
}

// ファイル追加処理
const handleFileChange = (e) => {
    const files = Array.from(e.target.files || [])
    files.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`ファイル「${file.name}」は10MBを超えているため追加できません。`)
            return
        }
        selectedFiles.value.push(file)
    })
    e.target.value = '' // リセット
}

// ファイル削除処理
const removeFile = (index) => {
    selectedFiles.value.splice(index, 1)
}

// ファイルサイズのフォーマット表示
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

// フォーム送信（FormData 形式）
const handleSubmit = async () => {
    submitting.value = true
    errorMessage.value = ''
    errors.value = {}

    const formData = new FormData()
    formData.append('category_id', form.category_id)
    formData.append('title', form.title)
    formData.append('body', form.body)

    if (form.published_at) {
        const formattedPublishedAt = form.published_at.replace('T', ' ') + ':00'
        formData.append('published_at', formattedPublishedAt)
    }

    if (form.committee_name) {
        formData.append('committee_name', form.committee_name)
    }

    form.roles.forEach(roleId => {
        formData.append('roles[]', roleId)
    })

    selectedFiles.value.forEach(file => {
        formData.append('file[]', file)
    })

    try {
        await api.post('/admin/notices', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        alert('お知らせを登録しました！')
        redirectToCategoryList()
    } catch (error) {
        console.error('登録処理に失敗しました:', error)
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {}
            errorMessage.value = '入力内容に不備があります。エラー項目を確認してください。'
        } else {
            errorMessage.value = error.response?.data?.message || '登録処理中にエラーが発生しました。'
        }
    } finally {
        submitting.value = false
    }
}

// 戻る
const goBack = () => {
    router.back()
}

onMounted(() => {
    fetchMasterData()
})
</script>