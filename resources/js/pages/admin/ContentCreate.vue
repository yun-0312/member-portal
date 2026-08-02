<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- 1. ページヘッダー -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                        <span>📝</span> コンテンツ新規登録
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">新しいコンテンツ情報を登録します</p>
                </div>
                <button
                    type="button"
                    @click="goBack"
                    class="inline-flex items-center gap-1 text-xs md:text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer"
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

                <!-- フォームグループ：2列グリッド（カテゴリ & サブカテゴリ） -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- カテゴリ (category_id) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            カテゴリ <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.category_id"
                            @change="handleCategoryChange"
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

                    <!-- サブカテゴリ (subcategory_id) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            サブカテゴリ <span class="text-xs text-slate-400 font-normal">（任意）</span>
                        </label>
                        <select
                            v-model="form.subcategory_id"
                            :disabled="!form.category_id || filteredSubcategories.length === 0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.subcategory_id }"
                        >
                            <option value="">
                                {{ !form.category_id ? '先にカテゴリを選択してください' : filteredSubcategories.length === 0 ? 'サブカテゴリはありません' : 'サブカテゴリを選択（任意）' }}
                            </option>
                            <option v-for="subCat in filteredSubcategories" :key="subCat.id" :value="subCat.id">
                                {{ subCat.name }}
                            </option>
                        </select>
                        <p v-if="errors.subcategory_id" class="text-xs text-rose-500 font-medium">{{ errors.subcategory_id[0] }}</p>
                    </div>

                </div>

                <!-- フォームグループ：2列グリッド（会議日 & 公開日時） -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- 会議日 (meeting_date) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            会議日 <span class="text-xs text-slate-400 font-normal">（任意）</span>
                        </label>
                        <input
                            type="date"
                            v-model="form.meeting_date"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.meeting_date }"
                        />
                        <p v-if="errors.meeting_date" class="text-xs text-rose-500 font-medium">{{ errors.meeting_date[0] }}</p>
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
                        placeholder="コンテンツのタイトルを入力"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.title }"
                    />
                    <p v-if="errors.title" class="text-xs text-rose-500 font-medium">{{ errors.title[0] }}</p>
                </div>

                <!-- 本文 (body) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        本文 <span class="text-xs text-slate-400 font-normal">（任意）</span>
                    </label>
                    <textarea
                        v-model="form.body"
                        rows="8"
                        placeholder="コンテンツの詳細内容を入力してください"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all leading-relaxed"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.body }"
                    ></textarea>
                    <p v-if="errors.body" class="text-xs text-rose-500 font-medium">{{ errors.body[0] }}</p>
                </div>

                <!-- 対象ロール選択 (roles[]) -->
                <div class="space-y-2 border-t border-slate-100 pt-5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        対象ロール制限 <span class="text-xs text-slate-400 font-normal">（未選択の場合は全員公開）</span>
                    </label>
                    <div v-if="rolesList.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
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
                            <span>{{ role.name || role.display_name || role.title || `ロール ${role.id}` }}</span>
                        </label>
                    </div>
                    <div v-else class="text-xs text-slate-400 p-2">
                        読み込み中または選択可能なロールがありません
                    </div>
                    <p v-if="errors.roles" class="text-xs text-rose-500 font-medium">{{ errors.roles[0] }}</p>
                </div>

                <!-- 添付ファイル (file[]) -->
                <div class="space-y-2 border-t border-slate-100 pt-5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        添付ファイル <span class="text-xs text-slate-400 font-normal">（1ファイルあたり最大10MB・複数選択可）</span>
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

                    <!-- 選択済みファイル一覧 -->
                    <div v-if="files.length > 0" class="space-y-1.5 pt-2">
                        <p class="text-xs font-bold text-slate-600">添付予定のファイル ({{ files.length }}件):</p>
                        <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl bg-slate-50/50 overflow-hidden">
                            <div
                                v-for="(file, index) in files"
                                :key="index"
                                class="flex items-center justify-between p-2.5 text-xs text-slate-700"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <span>📎</span>
                                    <span class="truncate font-medium">{{ file.name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">({{ formatFileSize(file.size) }})</span>
                                </div>
                                <button
                                    type="button"
                                    @click="removeFile(index)"
                                    class="text-slate-400 hover:text-rose-600 p-1 rounded-md transition-colors cursor-pointer"
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
                        <span>{{ submitting ? '登録中…' : '登録する' }}</span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../api.js'

const router = useRouter()
const route = useRoute()

const submitting = ref(false)
const errorMessage = ref('')
const errors = ref({})

const categories = ref([])
const allSubcategories = ref([])
const rolesList = ref([])
const files = ref([])

// 💡 遷移前のカテゴリパラメータを保持（例: ?category=letter）
const currentCategoryParam = computed(() => route.query.category || '')

const getCurrentDatetimeLocal = () => {
    const now = new Date()
    const yyyy = now.getFullYear()
    const mm = String(now.getMonth() + 1).padStart(2, '0')
    const dd = String(now.getDate()).padStart(2, '0')
    const hh = String(now.getHours()).padStart(2, '0')
    const min = String(now.getMinutes()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}T${hh}:${min}`
}

const form = reactive({
    title: '',
    body: '',
    category_id: '',
    subcategory_id: '',
    meeting_date: '',
    published_at: getCurrentDatetimeLocal(),
    roles: []
})

const filteredSubcategories = computed(() => {
    if (!form.category_id) return []

    // 1. 選択されたカテゴリ配下のサブカテゴリのみを抽出
    const subs = allSubcategories.value.filter(
        sub => String(sub.category_id) === String(form.category_id)
    )

    // 2. 他のサブカテゴリの「親（parent_id）」として指定されている ID のリストを作成
    const parentIds = new Set(
        subs.map(sub => sub.parent_id).filter(id => id !== null && id !== undefined)
    )

    // 3. 子を持っている親サブカテゴリを除外して返す
    return subs.filter(sub => !parentIds.has(sub.id))
})

const handleCategoryChange = () => {
    form.subcategory_id = ''
}

const fetchMasterData = async () => {
    try {
        const [catRes, subCatRes, roleRes] = await Promise.all([
            api.get('/admin/content-categories?per_page=1000'),
            api.get('/admin/content-subcategories?per_page=1000'),
            api.get('/admin/roles')
        ])

        categories.value = catRes.data?.data || catRes.data || []
        allSubcategories.value = subCatRes.data?.data || subCatRes.data || []
        rolesList.value = roleRes.data?.roles || roleRes.data?.data || roleRes.data || []

        //  クエリパラメータ（?category=●●）が渡されている場合、初期選択カテゴリに設定
        if (currentCategoryParam.value && categories.value.length > 0) {
            const matchedCat = categories.value.find(
                c => c.slug === currentCategoryParam.value || String(c.id) === String(currentCategoryParam.value)
            )
            if (matchedCat) {
                form.category_id = matchedCat.id
            }
        }
    } catch (error) {
        console.error('マスターデータの取得に失敗しました:', error)
    }
}

const handleFileChange = (e) => {
    const selectedFiles = Array.from(e.target.files || [])
    selectedFiles.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`ファイル「${file.name}」は10MBを超えているため追加できません。`)
            return
        }
        files.value.push(file)
    })
    e.target.value = ''
}

const removeFile = (index) => {
    files.value.splice(index, 1)
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

//  元の一覧画面へのリダイレクトURL（パラメータ付き）を生成
const getIndexUrl = () => {
    // 1. URLクエリに category がある場合
    if (currentCategoryParam.value) {
        return `/admin/contents?category=${currentCategoryParam.value}`
    }

    // 2. フォームで選択されたカテゴリがある場合（選択したカテゴリ一覧へ戻す）
    if (form.category_id) {
        const selectedCat = categories.value.find(c => String(c.id) === String(form.category_id))
        const categoryKey = selectedCat?.slug || form.category_id
        return `/admin/contents?category=${categoryKey}`
    }

    // 3. どちらもない場合
    return '/admin/contents'
}

// 送信処理（新規登録）
const handleSubmit = async () => {
    submitting.value = true
    errorMessage.value = ''
    errors.value = {}

    const formData = new FormData()

    formData.append('title', form.title)
    formData.append('category_id', form.category_id)

    if (form.body) formData.append('body', form.body)
    if (form.subcategory_id) formData.append('subcategory_id', form.subcategory_id)
    if (form.meeting_date) formData.append('meeting_date', form.meeting_date)

    if (form.published_at) {
        const formattedPublishedAt = form.published_at.replace('T', ' ') + ':00'
        formData.append('published_at', formattedPublishedAt)
    }

    form.roles.forEach(roleId => {
        formData.append('roles[]', roleId)
    })

    files.value.forEach(file => {
        formData.append('file[]', file)
    })

    try {
        const response = await api.post('/admin/contents', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        alert('コンテンツを登録しました！')

        const selectedCat = categories.value.find(c => String(c.id) === String(form.category_id))
        const catParam = currentCategoryParam.value || selectedCat?.slug || form.category_id

        // 1. サブカテゴリが選択されている場合は、サブカテゴリ絞り込み一覧へ飛ぶ
        if (form.subcategory_id) {
            router.push(`/admin/contents?category=${catParam}&subcategory_id=${form.subcategory_id}`)
            return
        }

        // 2. サブカテゴリがない場合は、該当カテゴリの一覧画面へ飛ぶ
        router.push(getIndexUrl())

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

//  「一覧へ戻る」ボタン・「キャンセル」ボタンの処理
const goBack = () => {
    // router.back() またはパラメータを維持した一覧URLに戻る
    router.push(getIndexUrl())
}

onMounted(() => {
    fetchMasterData()
})
</script>