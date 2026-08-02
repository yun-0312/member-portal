<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- 1. ページヘッダー -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                        <span>✏️</span> コンテンツ編集
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">登録されているコンテンツ情報を更新します</p>
                </div>
                <button
                    type="button"
                    @click="goBack"
                    class="inline-flex items-center gap-1 text-xs md:text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer"
                >
                    ← 一覧へ戻る
                </button>
            </div>

            <!-- ローディング表示 -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3 bg-white rounded-2xl border border-slate-200 shadow-xs">
                <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm font-medium">コンテンツデータを読み込み中…</p>
            </div>

            <!-- 2. フォームエリア -->
            <form v-else @submit.prevent="handleSubmit" class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-xs space-y-6">

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
                    <TiptapEditor
                        ref="editorRef"
                        v-model="form.body"
                        placeholder="コンテンツの詳細内容を入力してください..."
                        @open-media="showMediaModal = true"
                    />
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

                <!-- 添付ファイル管理 -->
                <div class="space-y-4 border-t border-slate-100 pt-5">

                    <!-- A. 既存の登録済みファイル一覧 -->
                    <div v-if="existingFiles.length > 0" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs md:text-sm font-bold text-slate-700">
                                現在添付されているファイル ({{ existingFiles.length }}件)
                            </label>
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="deleteAllFiles"
                                    class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 h-4 w-4"
                                />
                                <span>既存ファイルをすべて削除する</span>
                            </label>
                        </div>

                        <div v-if="!deleteAllFiles" class="divide-y divide-slate-100 border border-slate-200 rounded-xl bg-slate-50 overflow-hidden">
                            <div
                                v-for="file in existingFiles"
                                :key="file.id"
                                class="flex items-center justify-between p-3 text-xs"
                                :class="{ 'bg-rose-50/50 opacity-60': deleteFileIds.includes(file.id) }"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <span>📎</span>
                                    <span class="truncate font-medium text-slate-700" :class="{ 'line-through text-slate-400': deleteFileIds.includes(file.id) }">
                                        {{ file.name || file.original_name }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    @click="toggleDeleteExistingFile(file.id)"
                                    class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition-all cursor-pointer"
                                    :class="deleteFileIds.includes(file.id) 
                                        ? 'bg-slate-200 text-slate-700 hover:bg-slate-300' 
                                        : 'bg-rose-100 text-rose-700 hover:bg-rose-200'"
                                >
                                    {{ deleteFileIds.includes(file.id) ? '削除を取消' : '削除対象にする' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- B. 新規添付ファイルドロップゾーン -->
                    <div class="space-y-2">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            新しい添付ファイルを追加 <span class="text-xs text-slate-400 font-normal">（1ファイルあたり最大10MB）</span>
                        </label>

                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-slate-300 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <span class="text-xl mb-1">📁</span>
                                    <p class="text-xs text-slate-600 font-medium">クリックして追加ファイルを選択（複数選択可）</p>
                                </div>
                                <input
                                    type="file"
                                    multiple
                                    class="hidden"
                                    @change="handleFileChange"
                                />
                            </label>
                        </div>

                        <!-- 追加入力予定のファイル一覧 -->
                        <div v-if="newFiles.length > 0" class="space-y-1.5 pt-2">
                            <p class="text-xs font-bold text-slate-600">追加予定のファイル ({{ newFiles.length }}件):</p>
                            <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl bg-slate-50/50 overflow-hidden">
                                <div
                                    v-for="(file, index) in newFiles"
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
                                        @click="removeNewFile(index)"
                                        class="text-slate-400 hover:text-rose-600 p-1 rounded-md transition-colors cursor-pointer"
                                        title="削除"
                                    >
                                        ✕
                                    </button>
                                </div>
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
                        <span>{{ submitting ? '更新中…' : '更新を保存する' }}</span>
                    </button>
                </div>

            </form>

        </div>

        <!--  メディアライブラリ モーダル -->
        <MediaLibraryModal
            v-if="showMediaModal"
            @close="showMediaModal = false"
            @select="handleSelectImage"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../api.js'
import TiptapEditor from '../../components/TiptapEditor.vue'
import MediaLibraryModal from '../../components/MediaLibraryModal.vue'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const submitting = ref(false)
const errorMessage = ref('')
const errors = ref({})

// リッチテキスト＆モーダル用の ref
const editorRef = ref(null)
const showMediaModal = ref(false)

const categories = ref([])
const allSubcategories = ref([])
const rolesList = ref([])

// ファイル管理用ステート
const existingFiles = ref([])
const deleteFileIds = ref([])
const deleteAllFiles = ref(false)
const newFiles = ref([])

const currentCategoryParam = computed(() => route.query.category || '')

const formatToDatetimeLocal = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return ''
    const yyyy = date.getFullYear()
    const mm = String(date.getMonth() + 1).padStart(2, '0')
    const dd = String(date.getDate()).padStart(2, '0')
    const hh = String(date.getHours()).padStart(2, '0')
    const min = String(date.getMinutes()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}T${hh}:${min}`
}

const form = reactive({
    title: '',
    body: '',
    category_id: '',
    subcategory_id: '',
    meeting_date: '',
    published_at: '',
    roles: []
})

const filteredSubcategories = computed(() => {
    if (!form.category_id) return []

    const subs = allSubcategories.value.filter(
        sub => String(sub.category_id) === String(form.category_id)
    )

    const parentIds = new Set(
        subs.map(sub => sub.parent_id).filter(id => id !== null && id !== undefined)
    )

    return subs.filter(sub => !parentIds.has(sub.id))
})

const handleCategoryChange = () => {
    form.subcategory_id = ''
}

// メディアライブラリで画像が選択された時の処理
const handleSelectImage = (file) => {
    if (file && file.url) {
        editorRef.value?.insertImage(file.url)
    }
    showMediaModal.value = false
}

// データの初期読み込み (詳細データ & マスターデータ)
const fetchData = async () => {
    loading.value = true
    try {
        const id = route.params.id
        const [contentRes, catRes, subCatRes, roleRes] = await Promise.all([
            api.get(`/admin/contents/${id}`),
            api.get('/admin/content-categories?per_page=1000'),
            api.get('/admin/content-subcategories?per_page=1000'),
            api.get('/admin/roles')
        ])

        categories.value = catRes.data?.data || catRes.data || []
        allSubcategories.value = subCatRes.data?.data || subCatRes.data || []
        rolesList.value = roleRes.data?.roles || roleRes.data?.data || roleRes.data || []

        const res = contentRes.data
        const item = res?.item || res?.data || res

        if (item) {
            form.title = item.title ?? ''
            form.body = item.body ?? ''
            form.category_id = item.category_id ?? item.content_category_id ?? ''
            form.subcategory_id = item.subcategory_id ?? item.content_subcategory_id ?? ''
            form.meeting_date = item.meeting_date ? item.meeting_date.substring(0, 10) : ''
            form.published_at = formatToDatetimeLocal(item.published_at)

            const roles = res?.roles || item.roles || []
            if (Array.isArray(roles)) {
                form.roles = roles.map(r => typeof r === 'object' ? r.id : Number(r))
            }

            existingFiles.value = item.files || item.attachments || []
        }

    } catch (error) {
        console.error('データの取得に失敗しました:', error)
        errorMessage.value = 'コンテンツデータの読み込みに失敗しました。'
    } finally {
        loading.value = false
    }
}

const toggleDeleteExistingFile = (fileId) => {
    const idx = deleteFileIds.value.indexOf(fileId)
    if (idx > -1) {
        deleteFileIds.value.splice(idx, 1)
    } else {
        deleteFileIds.value.push(fileId)
    }
}

const handleFileChange = (e) => {
    const selectedFiles = Array.from(e.target.files || [])
    selectedFiles.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`ファイル「${file.name}」は10MBを超えているため追加できません。`)
            return
        }
        newFiles.value.push(file)
    })
    e.target.value = ''
}

const removeNewFile = (index) => {
    newFiles.value.splice(index, 1)
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

const getIndexUrl = () => {
    if (currentCategoryParam.value) {
        return `/admin/contents?category=${currentCategoryParam.value}`
    }
    if (form.category_id) {
        const selectedCat = categories.value.find(c => String(c.id) === String(form.category_id))
        const categoryKey = selectedCat?.slug || form.category_id
        return `/admin/contents?category=${categoryKey}`
    }
    return '/admin/contents'
}

const handleSubmit = async () => {
    submitting.value = true
    errorMessage.value = ''
    errors.value = {}

    const id = route.params.id
    const formData = new FormData()

    formData.append('_method', 'PATCH')

    if (form.title) formData.append('title', form.title)
    if (form.category_id) formData.append('category_id', form.category_id)
    if (form.subcategory_id) formData.append('subcategory_id', form.subcategory_id)
    formData.append('body', form.body || '')

    if (form.meeting_date) formData.append('meeting_date', form.meeting_date)

    if (form.published_at) {
        const formattedPublishedAt = form.published_at.replace('T', ' ') + ':00'
        formData.append('published_at', formattedPublishedAt)
    }

    form.roles.forEach(roleId => {
        formData.append('roles[]', roleId)
    })

    newFiles.value.forEach(file => {
        formData.append('file[]', file)
    })

    if (deleteAllFiles.value) {
        formData.append('delete_all_files', '1')
    } else {
        deleteFileIds.value.forEach(fileId => {
            formData.append('delete_file_ids[]', fileId)
        })
    }

    try {
        await api.post(`/admin/contents/${id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        alert('コンテンツを更新しました！')

        const selectedCat = categories.value.find(c => String(c.id) === String(form.category_id))
        const catParam = currentCategoryParam.value || selectedCat?.slug || form.category_id

        if (form.subcategory_id) {
            router.push(`/admin/contents?category=${catParam}&subcategory_id=${form.subcategory_id}`)
            return
        }

        router.push({
            path: `/admin/contents/${id}`,
            query: route.query
        })

    } catch (error) {
        console.error('更新処理に失敗しました:', error)
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {}
            errorMessage.value = '入力内容に不備があります。エラー項目を確認してください。'
        } else {
            errorMessage.value = error.response?.data?.message || '更新処理中にエラーが発生しました。'
        }
    } finally {
        submitting.value = false
    }
}

const goBack = () => {
    router.push(getIndexUrl())
}

onMounted(() => {
    fetchData()
})
</script>