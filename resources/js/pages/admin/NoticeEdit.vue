<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- 1. ページヘッダー -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                        <span>✏️</span> お知らせ編集
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">登録されているお知らせ情報の変更・更新を行います</p>
                </div>
                <button
                    type="button"
                    @click="goBack"
                    class="inline-flex items-center gap-1 text-xs md:text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer"
                >
                    ← 一覧へ戻る
                </button>
            </div>

            <!-- 2. ローディング表示 -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
                <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm font-medium">お知らせ情報を読み込み中…</p>
            </div>

            <!-- 3. フォームエリア -->
            <form v-else @submit.prevent="handleSubmit" class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-xs space-y-6">

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
                        本文
                    </label>
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

                <!-- 既存の添付ファイル管理 -->
                <div v-if="existingFiles.length > 0" class="space-y-3 border-t border-slate-100 pt-5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            登録済みの添付ファイル ({{ existingFiles.length }}件)
                        </label>
                        <button
                            type="button"
                            @click="toggleDeleteAllFiles"
                            class="text-xs font-bold transition-colors cursor-pointer"
                            :class="deleteAllFiles ? 'text-blue-600 hover:underline' : 'text-rose-600 hover:underline'"
                        >
                            {{ deleteAllFiles ? '一括削除を取り消す' : 'すべての既存ファイルを削除' }}
                        </button>
                    </div>

                    <!-- 全削除フラグが立っている場合の警告 -->
                    <div v-if="deleteAllFiles" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-bold flex items-center justify-between">
                        <span>⚠️ 送信時にすべての既存ファイルが削除されます</span>
                    </div>

                    <!-- 登録済みファイル一覧 -->
                    <div v-else class="divide-y divide-slate-100 border border-slate-200 rounded-xl bg-slate-50/50 overflow-hidden">
                        <div
                            v-for="file in existingFiles"
                            :key="file.id"
                            class="flex items-center justify-between p-3 text-xs"
                            :class="{ 'opacity-40 bg-slate-100 line-through': deleteFileIds.includes(file.id) }"
                        >
                            <div class="flex items-center gap-2 truncate">
                                <span>📎</span>
                                <a
                                    v-if="file.url"
                                    :href="file.url"
                                    target="_blank"
                                    class="font-medium text-blue-600 hover:underline truncate"
                                >
                                    {{ file.name || file.file_name || file.original_name }}
                                </a>
                                <span v-else class="font-medium text-slate-700 truncate">
                                    {{ file.name || file.file_name || file.original_name }}
                                </span>
                            </div>

                            <button
                                type="button"
                                @click="toggleDeleteFile(file.id)"
                                class="px-2.5 py-1 rounded-lg font-bold text-xs transition-colors shrink-0 cursor-pointer"
                                :class="deleteFileIds.includes(file.id) 
                                    ? 'bg-slate-200 text-slate-700 hover:bg-slate-300' 
                                    : 'bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200'"
                            >
                                {{ deleteFileIds.includes(file.id) ? '削除取消' : '削除対象にする' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 新規ファイル追加 (file[]) -->
                <div class="space-y-2 border-t border-slate-100 pt-5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        ファイルを追加添付 <span class="text-xs text-slate-400 font-normal">（1ファイルあたり最大10MB）</span>
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

                    <!-- 選択済み新規追加ファイル一覧 -->
                    <div v-if="newFiles.length > 0" class="space-y-1.5 pt-2">
                        <p class="text-xs font-bold text-slate-600">追加予定のファイル ({{ newFiles.length }}件):</p>
                        <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl bg-slate-50/50 overflow-hidden">
                            <div
                                v-for="(file, index) in newFiles"
                                :key="index"
                                class="flex items-center justify-between p-2.5 text-xs text-slate-700"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <span>✨</span>
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
    </div>
    <!-- メディアライブラリ モーダル -->
    <MediaLibraryModal
        v-if="showMediaModal"
        @close="showMediaModal = false"
        @select="handleSelectImage"
    />
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api.js'
import TiptapEditor from '../../components/TiptapEditor.vue'
import MediaLibraryModal from '../../components/MediaLibraryModal.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const submitting = ref(false)
const errorMessage = ref('')
const errors = ref({})
// リッチテキスト＆モーダル用の ref
const editorRef = ref(null)
const showMediaModal = ref(false)

const categories = ref([])
const rolesList = ref([])

// 既存ファイル・削除フラグ
const existingFiles = ref([])
const deleteFileIds = ref([])
const deleteAllFiles = ref(false)

// 新規追加ファイル
const newFiles = ref([])

// フォームデータ
const form = reactive({
    category_id: '',
    title: '',
    body: '',
    published_at: '',
    committee_name: '',
    roles: []
})

// datetime-local 用 (YYYY-MM-DDTHH:mm) への変換関数
const formatToDatetimeLocal = (dateStr) => {
    if (!dateStr) return ''
    const str = String(dateStr)
    const match = str.match(/^(\d{4}-\d{2}-\d{2})[T\s](\d{2}:\d{2})/)
    if (match) {
        return `${match[1]}T${match[2]}`
    }
    return ''
}

// データの読み込み＆フォームへの初期値バインド関数
const fetchData = async () => {
    loading.value = true
    try {
        const id = route.params.id
        const [noticeRes, catRes, roleRes] = await Promise.all([
            api.get(`/admin/notices/${id}`),
            api.get('/admin/notice-categories'),
            api.get('/admin/roles')
        ])

        const res = noticeRes.data

        // レスポンス構造に合わせて抽出場所を指定
        const notice = res?.item || {}
        const noticeRoles = res?.roles || notice.roles || []

        categories.value = catRes.data?.data || catRes.data || []
        rolesList.value = roleRes.data?.data || roleRes.data || []

        if (notice) {
            // カテゴリID
            form.category_id = notice.category_id ?? notice.notice_category_id ?? ''

            // テキスト項目
            form.title = notice.title ?? ''
            form.body = notice.body ?? ''
            form.committee_name = notice.committee_name ?? ''

            // 公開日時 (published_at)
            form.published_at = formatToDatetimeLocal(notice.published_at)

            // 既存ファイル
            existingFiles.value = notice.files || notice.attachments || notice.notice_files || []
        }

        // ロール選択のセット（res.roles から抽出）
        if (Array.isArray(noticeRoles)) {
            form.roles = noticeRoles.map(r => typeof r === 'object' ? r.id : Number(r))
        } else {
            form.roles = []
        }

    } catch (error) {
        console.error('データの取得に失敗しました:', error)
        errorMessage.value = 'お知らせデータの読み込みに失敗しました。'
    } finally {
        loading.value = false
    }
}

// メディアライブラリで画像が選択された時の処理
const handleSelectImage = (file) => {
    if (file && file.url) {
        editorRef.value?.insertImage(file.url)
    }
    showMediaModal.value = false
}

// 個別ファイルの削除フラグ切替
const toggleDeleteFile = (fileId) => {
    const index = deleteFileIds.value.indexOf(fileId)
    if (index > -1) {
        deleteFileIds.value.splice(index, 1)
    } else {
        deleteFileIds.value.push(fileId)
    }
}

// 一括削除フラグ切替
const toggleDeleteAllFiles = () => {
    deleteAllFiles.value = !deleteAllFiles.value
    if (deleteAllFiles.value) {
        deleteFileIds.value = []
    }
}

// 新規ファイル追加
const handleFileChange = (e) => {
    const files = Array.from(e.target.files || [])
    files.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`ファイル「${file.name}」は10MBを超えているため追加できません。`)
            return
        }
        newFiles.value.push(file)
    })
    e.target.value = ''
}

// 新規ファイル取り消し
const removeNewFile = (index) => {
    newFiles.value.splice(index, 1)
}

// ファイルサイズ表記
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

// 送信処理（更新）
const handleSubmit = async () => {
    submitting.value = true
    errorMessage.value = ''
    errors.value = {}

    const id = route.params.id
    const formData = new FormData()

    // 💡 Laravelなどの疑似メソッド指定（PATCH）
    formData.append('_method', 'PATCH')

    if (form.category_id) formData.append('category_id', form.category_id)
    if (form.title) formData.append('title', form.title)
    formData.append('body', form.body || '')

    if (form.published_at) {
        const formattedPublishedAt = form.published_at.replace('T', ' ') + ':00'
        formData.append('published_at', formattedPublishedAt)
    }

    if (form.committee_name !== undefined) {
        formData.append('committee_name', form.committee_name || '')
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
        // 💡 サーバーのルーティングに合わせて api.post ではなく api.patch または api.post を調整
        // FormData でファイルアップロードを行う場合、_method: PATCH を入れた上で POST 送信するのが一般的です
        await api.post(`/admin/notices/${id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        alert('お知らせを更新しました！')
        router.push('/admin/notices')
    } catch (error) {
        console.error('更新処理に失敗しました:', error)

        // 💡 もし POST + _method: PATCH でも 405 になる場合は api.patch で直接送信を試すフォールバック処理
        if (error.response?.status === 405) {
            try {
                await api.patch(`/admin/notices/${id}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                alert('お知らせを更新しました！')
                router.push('/admin/notices')
                return
            } catch (patchError) {
                console.error('PATCHでの直接送信も失敗:', patchError)
            }
        }

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
    router.back()
}

onMounted(() => {
    fetchData()
})
</script>