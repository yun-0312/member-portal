<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-5xl mx-auto space-y-6">

        <!-- 1. ヘッダーナビゲーション & アクション -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <span>🎬</span>
                <span>研修会動画</span>
            </h1>
            </div>

            <!-- 編集・削除アクション -->
            <div v-if="video" class="flex items-center gap-2 self-start sm:self-auto">
            <router-link
                :to="indexUrl"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold text-xs md:text-sm rounded-xl shadow-2xs transition-all active:scale-95"
            >
                <span>←</span>
                <span>動画一覧に戻る</span>
            </router-link>
            <router-link
                v-if="editUrl"
                :to="editUrl"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs md:text-sm rounded-xl shadow-2xs transition-all active:scale-95"
            >
                <span>✏️</span>
                <span>編集する</span>
            </router-link>

            <button
                @click="deleteVideo"
                :disabled="deleting"
                type="button"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold text-xs md:text-sm rounded-xl transition-all active:scale-95 cursor-pointer disabled:opacity-50"
            >
                <span>🗑️</span>
                <span v-if="deleting">削除中…</span>
                <span v-else>削除</span>
            </button>
            </div>
        </div>

        <!-- 2. ローディング表示 -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
            <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-medium">動画情報を読み込み中…</p>
        </div>

        <!-- 3. メインコンテンツ -->
        <div v-else-if="video" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- 左側・メインエリア (プレイヤー & 概要) -->
            <div class="lg:col-span-2 space-y-6">

            <!-- 📹 動画プレイヤー -->
            <div class="bg-slate-900 rounded-2xl overflow-hidden shadow-md">
                <div class="relative aspect-video flex items-center justify-center">
                <!-- iframe埋め込み (YouTube / Vimeo) -->
                <iframe
                    v-if="embedUrl"
                    :src="embedUrl"
                    class="w-full h-full border-0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>

                <!-- 外部リンク誘導表示 (Zoom等) -->
                <div v-else class="p-8 text-center text-white space-y-4 max-w-md">
                    <div class="text-4xl">🔒</div>
                    <p class="text-xs md:text-sm font-medium leading-relaxed">
                    この動画は外部サイト（Zoom録画等）で配信されています。直接リンク先にてご視聴ください。
                    </p>
                    <a
                    :href="video.external_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs md:text-sm rounded-xl shadow-xs transition-all active:scale-95"
                    >
                    <span>🎥 動画視聴ページへ移動</span>
                    <span>↗</span>
                    </a>
                </div>
                </div>
            </div>

            <!-- 📄 動画タイトル & 詳細説明 -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <div class="flex items-start justify-between gap-3">
                <h2 class="text-xl font-bold text-slate-800 leading-snug">
                    {{ video.title }}
                </h2>
                <span
                    :class="[
                    'shrink-0 px-2.5 py-1 text-xs font-bold rounded-lg text-white border',
                    providerBadge.bgClass
                    ]"
                >
                    {{ providerBadge.label }}
                </span>
                </div>

                <div class="border-t border-slate-100 pt-4 overflow-hidden">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">概要・説明</h3>
                    <div
                        v-if="video.description"
                        v-html="video.description"
                        class="prose prose-slate prose-sm max-w-none text-slate-700 leading-relaxed break-words [word-break:break-word] [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-xl [&_a]:text-blue-600 [&_a]:underline [&_table]:block [&_table]:max-w-full [&_table]:overflow-x-auto [&_iframe]:max-w-full [&_pre]:max-w-full [&_pre]:overflow-x-auto"
                    ></div>
                    <p v-else class="text-sm text-slate-400 italic">説明はありません。</p>
                </div>

                <!-- 📎 添付ファイル一覧 -->
                <div v-if="video.files && video.files.length > 0" class="border-t border-slate-100 pt-4 space-y-2">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">添付資料・配布ファイル</h3>
                <div class="flex flex-wrap gap-2">
                    <a
                    v-for="file in video.files"
                    :key="file.id"
                    :href="getFileUrl(file.path)"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-blue-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 hover:text-blue-600 transition-colors"
                    >
                    <span>📎</span>
                    <span>{{ file.name }}</span>
                    </a>
                </div>
                </div>
            </div>

            </div>

            <!-- 右側・サイドバーエリア (ロール設定 & メタ情報) -->
            <div class="space-y-6">

            <!-- 👥 閲覧許可ロール設定 -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-1.5">
                    <span>👥</span>
                    <span>閲覧許可ロール</span>
                </h3>
                <button
                    @click="showAddRoleModal = true"
                    type="button"
                    class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-lg transition-all cursor-pointer"
                >
                    ＋ 追加
                </button>
                </div>

                <!-- 設定済みロールリスト -->
                <div class="space-y-2">
                <div v-if="rolesList.length > 0" class="flex flex-wrap gap-1.5">
                    <span
                    v-for="role in rolesList"
                    :key="role.id"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200"
                    >
                    <span>{{ role.name }}</span>
                    <button
                        @click="removeRole(role)"
                        :disabled="processingRoleId === role.id"
                        type="button"
                        title="ロールを除外"
                        class="text-slate-400 hover:text-rose-600 font-bold ml-1 cursor-pointer disabled:opacity-50"
                    >
                        ×
                    </button>
                    </span>
                </div>
                <p v-else class="text-xs text-slate-400 italic">閲覧許可ロールが設定されていません</p>
                </div>
            </div>

            <!-- ℹ️ メタデータ・管理情報 -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-4 text-xs">
                <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3 flex items-center gap-1.5">
                <span>📋</span>
                <span>配信・管理情報</span>
                </h3>

                <dl class="space-y-3">
                <div>
                    <dt class="text-slate-400 font-bold">📅 公開開始日時</dt>
                    <dd class="font-mono text-slate-700 font-semibold mt-0.5">{{ formatDate(video.published_at) }}</dd>
                </div>

                <div>
                    <dt class="text-slate-400 font-bold">⏳ 掲載終了日時</dt>
                    <dd class="font-mono text-slate-700 font-semibold mt-0.5">
                    {{ video.expired_at ? formatDate(video.expired_at) : '無期限' }}
                    </dd>
                </div>

                <div v-if="video.creator" class="border-t border-slate-100 pt-3">
                    <dt class="text-slate-400 font-bold">👤 登録担当者</dt>
                    <dd class="text-slate-700 font-medium mt-0.5">
                    {{ video.creator.name }}
                    <span class="text-slate-400 text-[11px]">({{ video.creator.email }})</span>
                    </dd>
                </div>

                <div class="border-t border-slate-100 pt-3 flex justify-between text-[11px] text-slate-400 font-mono">
                    <span>登録: {{ formatDate(video.created_at) }}</span>
                    <span>更新: {{ formatDate(video.updated_at) }}</span>
                </div>
                </dl>
            </div>

            </div>

        </div>

        </div>

        <!-- 👥 ロール追加モーダル -->
        <div v-if="showAddRoleModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-sm w-full p-6 space-y-4">
            <h3 class="text-base font-bold text-slate-800">閲覧許可ロールの追加</h3>

            <div class="space-y-2">
            <label class="text-xs font-bold text-slate-500">追加するロール</label>
            <select
                v-model="selectedRoleId"
                class="w-full text-xs p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-hidden focus:border-blue-500"
            >
                <option value="" disabled>ロールを選択してください</option>
                <option v-for="r in availableRoles" :key="r.id" :value="r.id">
                {{ r.name }} (ID: {{ r.id }})
                </option>
            </select>
            </div>

            <div class="flex justify-end gap-2 pt-2">
            <button
                @click="showAddRoleModal = false"
                type="button"
                class="text-xs font-bold text-slate-600 hover:bg-slate-100 px-3 py-2 rounded-xl border border-slate-200 cursor-pointer"
            >
                キャンセル
            </button>
            <button
                @click="addRole"
                :disabled="!selectedRoleId || addingRole"
                type="button"
                class="text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 px-4 py-2 rounded-xl shadow-2xs cursor-pointer"
            >
                <span v-if="addingRole">追加中…</span>
                <span v-else>追加する</span>
            </button>
            </div>
        </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api.js'

const route = useRoute()
const router = useRouter()

const responseData = ref(null)
const loading = ref(true)
const deleting = ref(false)

// ロール追加モーダル用 state
const showAddRoleModal = ref(false)
const selectedRoleId = ref('')
const addingRole = ref(false)
const processingRoleId = ref(null)

// APIから動的に取得するシステム全体のロールマスタ
const masterRoles = ref([])

// JSONから各種データの算出
const video = computed(() => responseData.value?.item || null)
const indexUrl = computed(() => responseData.value?.index_url || '/admin/videos')
const editUrl = computed(() => responseData.value?.update_url ? `${responseData.value.update_url}/edit` : null)
const deleteUrl = computed(() => responseData.value?.delete_url || null)
const roleTargetableUrl = computed(() => responseData.value?.role_targetable_url || null)

// 動画に設定されている閲覧許可ロール一覧
const rolesList = computed(() => {
    return responseData.value?.roles || video.value?.roles || []
})

// ★ マスターロールから「設定済みロール」を除外した、追加可能ロール一覧
const availableRoles = computed(() => {
    const currentRoleIds = rolesList.value.map(r => r.id)
    return masterRoles.value.filter(r => !currentRoleIds.includes(r.id))
})

// ==========================================
// プロバイダー判定 & 埋め込みURL構築
// ==========================================

const providerBadge = computed(() => {
    const url = video.value?.external_url || ''
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        return { label: 'YouTube', bgClass: 'bg-red-600 border-red-700' }
    }
    if (url.includes('vimeo.com')) {
        return { label: 'Vimeo', bgClass: 'bg-sky-500 border-sky-600' }
    }
    if (url.includes('zoom.us')) {
        return { label: 'Zoom録画', bgClass: 'bg-blue-600 border-blue-700' }
    }
    return { label: '外部動画', bgClass: 'bg-slate-600 border-slate-700' }
})

const embedUrl = computed(() => {
    const url = video.value?.external_url
    if (!url) return null

    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/
        const match = url.match(regExp)
        const videoId = (match && match[2].length === 11) ? match[2] : null
        return videoId ? `https://www.youtube.com/embed/${videoId}` : null
    }

    if (url.includes('vimeo.com')) {
        const match = url.match(/vimeo\.com\/(?:video\/)?([0-9]+)/)
        const videoId = match ? match[1] : null
        return videoId ? `https://player.vimeo.com/video/${videoId}` : null
    }

    return null
})

// ==========================================
// ロール追加・削除処理
// ==========================================

const addRole = async () => {
    if (!selectedRoleId.value || !roleTargetableUrl.value) return

    addingRole.value = true
    try {
        await api.post(roleTargetableUrl.value, {
            targetable_id: video.value.id,
            targetable_type: 'App\\Models\\Video',
            role_id: Number(selectedRoleId.value)
        })

        alert('閲覧許可ロールを追加しました。')
        showAddRoleModal.value = false
        selectedRoleId.value = ''
        await fetchVideoDetail() // 情報を再取得して同期
    } catch (error) {
        console.error('ロール追加失敗:', error.response?.data || error)
        const msg = error.response?.data?.message || 'ロールの追加に失敗しました。'
        alert(`エラー: ${msg}`)
    } finally {
        addingRole.value = false
    }
}

const removeRole = async (role) => {
    if (!confirm(`ロール「${role.name}」の閲覧許可を解除しますか？`)) return

    processingRoleId.value = role.id
    try {
        const targetDestroyUrl = role.destroy_url || `${roleTargetableUrl.value}/${role.id}`
        await api.delete(targetDestroyUrl)

        alert('ロールを削除しました。')
        await fetchVideoDetail() // 情報を再取得して同期
    } catch (error) {
        console.error('ロール削除失敗:', error.response?.data || error)
        const msg = error.response?.data?.message || 'ロールの削除に失敗しました。'
        alert(`エラー: ${msg}`)
    } finally {
        processingRoleId.value = null
    }
}

// ==========================================
// 🗑️ 動画削除・詳細取得・API通信
// ==========================================

// ★ システム全体のロールマスタを取得する関数
const fetchMasterRoles = async () => {
    try {
        const res = await api.get('/admin/roles') // プロジェクトのロール一覧エンドポイントを指定
        masterRoles.value = res.data?.data || res.data || []
    } catch (error) {
        console.error('ロールマスタ一覧の取得に失敗しました:', error)
    }
}

const fetchVideoDetail = async () => {
    loading.value = true
    try {
        const videoId = route.params.id
        const res = await api.get(`/admin/videos/${videoId}`)
        responseData.value = res.data?.data || res.data
    } catch (error) {
        console.error('動画詳細情報の取得に失敗しました:', error)
        alert('動画詳細の取得に失敗しました。一覧画面に戻ります。')
        router.push('/admin/videos')
    } finally {
        loading.value = false
    }
}

const deleteVideo = async () => {
    if (!deleteUrl.value) return
    if (!confirm(`「${video.value.title}」を削除してもよろしいですか？`)) return

    deleting.value = true
    try {
        await api.delete(deleteUrl.value)
        alert('動画を削除しました。')
        router.push(indexUrl.value)
    } catch (error) {
        console.error('動画の削除に失敗しました:', error)
        alert('動画の削除に失敗しました。')
    } finally {
        deleting.value = false
    }
}

const getFileUrl = (filePath) => {
    if (!filePath) return '#'
    if (filePath.startsWith('http://') || filePath.startsWith('https://')) return filePath
    const baseURL = api.defaults.baseURL || '/storage'
    const cleanBase = baseURL.endsWith('/') ? baseURL.slice(0, -1) : baseURL
    const cleanPath = filePath.startsWith('/') ? filePath : `/${filePath}`
    return `${cleanBase}/${cleanPath}`
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return dateString
    return date.toLocaleString('ja-JP', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

onMounted(() => {
    fetchMasterRoles() // ロール一覧の取得
    fetchVideoDetail()
})
</script>