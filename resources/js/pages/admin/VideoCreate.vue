<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- 1. ページヘッダー -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                        <span>🎬</span> 研修会動画新規作成
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">新しい研修会動画コンテンツを登録します</p>
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

                <!-- タイトル (title) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        動画タイトル <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        v-model="form.title"
                        maxlength="255"
                        required
                        placeholder="研修会動画のタイトルを入力"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.title }"
                    />
                    <p v-if="errors.title" class="text-xs text-rose-500 font-medium">{{ errors.title[0] }}</p>
                </div>

                <!-- 外部URL (external_url) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        動画URL (YouTube/外部配信URL) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="url"
                        v-model="form.external_url"
                        required
                        placeholder="https://www.youtube.com/watch?v=... など"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.external_url }"
                    />
                    <p v-if="errors.external_url" class="text-xs text-rose-500 font-medium">{{ errors.external_url[0] }}</p>
                </div>

                <!-- フォームグループ：2列グリッド（公開開始日時 & 公開終了日時） -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- 公開開始日時 (published_at) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            公開開始日時 <span class="text-rose-500">*</span>
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

                    <!-- 公開終了日時 (expired_at) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            公開終了日時 <span class="text-xs text-slate-400 font-normal">（任意・未指定で無期限）</span>
                        </label>
                        <input
                            type="datetime-local"
                            v-model="form.expired_at"
                            :min="form.published_at"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono"
                            :class="{ 'border-rose-400 bg-rose-50/30': errors.expired_at }"
                        />
                        <p v-if="errors.expired_at" class="text-xs text-rose-500 font-medium">{{ errors.expired_at[0] }}</p>
                    </div>

                </div>

                <!-- 動画概要・説明 (description) -->
                <div class="space-y-1.5">
                    <label class="block text-xs md:text-sm font-bold text-slate-700">
                        動画概要・説明 <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="6"
                        required
                        placeholder="研修会動画のアジェンダや概要・補足情報などを入力してください"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all leading-relaxed"
                        :class="{ 'border-rose-400 bg-rose-50/30': errors.description }"
                    ></textarea>
                    <p v-if="errors.description" class="text-xs text-rose-500 font-medium">{{ errors.description[0] }}</p>
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

                <!-- 添付資料管理 (file[]) -->
                <div class="space-y-4 border-t border-slate-100 pt-5">
                    <div class="space-y-2">
                        <label class="block text-xs md:text-sm font-bold text-slate-700">
                            レジュメ・配布資料を添付 <span class="text-xs text-slate-400 font-normal">（任意 / 1ファイル最大10MB）</span>
                        </label>

                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-slate-300 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <span class="text-xl mb-1">📁</span>
                                    <p class="text-xs text-slate-600 font-medium">クリックして添付ファイルを選択（複数選択可）</p>
                                </div>
                                <input
                                    type="file"
                                    multiple
                                    class="hidden"
                                    @change="handleFileChange"
                                />
                            </label>
                        </div>

                        <!-- 選択中のファイル一覧 -->
                        <div v-if="selectedFiles.length > 0" class="space-y-1.5 pt-2">
                            <p class="text-xs font-bold text-slate-600">選択中の資料 ({{ selectedFiles.length }}件):</p>
                            <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl bg-slate-50/50 overflow-hidden">
                                <div
                                    v-for="(file, index) in selectedFiles"
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
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api.js'

const router = useRouter()

const submitting = ref(false)
const errorMessage = ref('')
const errors = ref({})

const rolesList = ref([])
const selectedFiles = ref([])

// 今日の現在時刻より前の直近00分 (YYYY-MM-DDTHH:00) を初期値として設定
const getCurrentDatetimeLocal = () => {
    const d = new Date()
    const yyyy = d.getFullYear()
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    const hh = String(d.getHours()).padStart(2, '0')
    // 分(mm)を固定で '00' に設定
    return `${yyyy}-${mm}-${dd}T${hh}:00`
}

// フォーム初期値
const form = reactive({
    title: '',
    description: '',
    external_url: '',
    published_at: getCurrentDatetimeLocal(),
    expired_at: '',
    roles: []
})

// ロール一覧取得
const fetchRoles = async () => {
    try {
        const response = await api.get('/admin/roles')
        rolesList.value = response.data?.roles || response.data?.data || response.data || []
    } catch (error) {
        console.error('ロール一覧の取得に失敗しました:', error)
    }
}

// ファイル添付ハンドラ（10MB上限チェック付き）
const handleFileChange = (e) => {
    const files = Array.from(e.target.files || [])
    files.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`ファイル「${file.name}」は10MBを超えているため追加できません。`)
            return
        }
        selectedFiles.value.push(file)
    })
    e.target.value = ''
}

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1)
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

// 送信処理（FormData形式）
const handleSubmit = async () => {
    submitting.value = true
    errorMessage.value = ''
    errors.value = {}

    const formData = new FormData()

    formData.append('title', form.title)
    formData.append('description', form.description)
    formData.append('external_url', form.external_url)

    if (form.published_at) {
        formData.append('published_at', form.published_at.replace('T', ' ') + ':00')
    }

    if (form.expired_at) {
        formData.append('expired_at', form.expired_at.replace('T', ' ') + ':00')
    }

    // roles[]
    form.roles.forEach(roleId => {
        formData.append('roles[]', roleId)
    })

    // file[]
    selectedFiles.value.forEach(file => {
        formData.append('file[]', file)
    })

    try {
        const response = await api.post('/admin/videos', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        alert('研修会動画を作成しました！')

        const createdId = response.data?.item?.id || response.data?.id || response.data?.data?.id
        if (createdId) {
            router.push(`/admin/videos/${createdId}`)
        } else {
            router.push('/admin/videos')
        }

    } catch (error) {
        console.error('作成処理に失敗しました:', error)
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {}
            errorMessage.value = '入力内容に不備があります。エラー項目を確認してください。'
        } else {
            errorMessage.value = error.response?.data?.message || '作成処理中にエラーが発生しました。'
        }
    } finally {
        submitting.value = false
    }
}

const goBack = () => {
    router.push('/admin/videos')
}

onMounted(() => {
    fetchRoles()
})
</script>