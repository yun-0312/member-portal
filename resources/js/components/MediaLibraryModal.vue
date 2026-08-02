<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="bg-white w-full max-w-4xl h-[80vh] rounded-2xl shadow-xl flex flex-col overflow-hidden border border-slate-200">

            <!-- モーダルヘッダー -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span>🖼️</span> メディアライブラリ
                </h2>
                <button
                    type="button"
                    @click="$emit('close')"
                    class="text-slate-400 hover:text-slate-600 font-bold p-1 rounded-lg transition-colors cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- タブ切替 -->
            <div class="flex border-b border-slate-200 bg-white px-6">
                <button
                    type="button"
                    @click="activeTab = 'list'"
                    class="py-3 px-4 text-xs font-bold border-b-2 transition-all cursor-pointer"
                    :class="activeTab === 'list' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    メディア一覧から選択
                </button>
                <button
                    type="button"
                    @click="activeTab = 'upload'"
                    class="py-3 px-4 text-xs font-bold border-b-2 transition-all cursor-pointer"
                    :class="activeTab === 'upload' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    新規ファイルをアップロード
                </button>
            </div>

            <!-- モーダルコンテンツエリア -->
            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50">

                <!-- TAB 1: 画像一覧表示 -->
                <div v-if="activeTab === 'list'">
                    <div v-if="loading" class="text-center py-12 text-slate-400 text-xs">
                        読み込み中…
                    </div>
                    <div v-else-if="files.length === 0" class="text-center py-12 text-slate-400 text-xs">
                        アップロードされた画像がありません
                    </div>
                    <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                        <div
                            v-for="file in files"
                            :key="file.id"
                            @click="selectedFile = file"
                            class="group aspect-square rounded-xl overflow-hidden border-2 cursor-pointer transition-all relative bg-white"
                            :class="selectedFile?.id === file.id ? 'border-blue-600 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300'"
                        >
                            <img :src="getFileUrl(file)" :alt="file.name" class="w-full h-full object-cover" />
                            
                            <!-- 選択時のチェックマーク表示 -->
                            <div
                                v-if="selectedFile?.id === file.id"
                                class="absolute inset-0 bg-blue-600/20 flex items-center justify-center text-white font-bold pointer-events-none"
                            >
                                ✓
                            </div>

                            <!-- 🗑️ 削除ボタン（ホバー時に表示） -->
                            <button
                                type="button"
                                @click.stop="deleteMedia(file)"
                                class="absolute top-1 right-1 w-6 h-6 rounded-lg bg-red-600/80 hover:bg-red-600 text-white flex items-center justify-center text-[10px] opacity-0 group-hover:opacity-100 transition-opacity shadow-sm z-10"
                                title="画像を削除"
                            >
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: ファイルアップロード -->
                <div v-if="activeTab === 'upload'" class="h-full flex items-center justify-center">
                    <label class="flex flex-col items-center justify-center w-full max-w-lg h-48 border-2 border-slate-300 border-dashed rounded-2xl cursor-pointer bg-white hover:bg-slate-50 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <span class="text-3xl mb-2">📤</span>
                            <p class="text-xs text-slate-700 font-bold mb-1">クリックして画像を選択</p>
                            <p class="text-[10px] text-slate-400">JPEG, PNG, GIF, WEBP (最大10MB)</p>
                        </div>
                        <input type="file" accept="image/*" class="hidden" @change="handleFileUpload" :disabled="uploading" />
                    </label>
                </div>

            </div>

            <!-- モーダル フッター -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 bg-slate-50">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-all cursor-pointer"
                >
                    キャンセル
                </button>
                <button
                    type="button"
                    @click="handleSelect"
                    :disabled="!selectedFile"
                    class="px-5 py-2 text-xs font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer"
                >
                    この画像を挿入
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api.js'

const emit = defineEmits(['close', 'select'])

const activeTab = ref('list')
const loading = ref(false)
const uploading = ref(false)
const files = ref([])
const selectedFile = ref(null)

// DBのpathやレスポンス形式に合わせてURLを整形
const getFileUrl = (file) => {
    if (file.url) return file.url
    if (file.path) {
        return `/storage/${file.path.replace(/^public\//, '')}`
    }
    return ''
}

// メディア一覧の取得
const fetchMediaList = async () => {
    loading.value = true
    try {
        const res = await api.get('/admin/files/media')
        files.value = res.data?.data || res.data?.files || res.data || []
    } catch (error) {
        console.error('メディア一覧の取得に失敗しました:', error)
    } finally {
        loading.value = false
    }
}

// 画像の削除処理
const deleteMedia = async (file) => {
    const isConfirmed = confirm(
        `「${file.name || 'この画像'}」を削除しますか？\n\n` +
        `⚠️注意: 過去の記事などでこの画像が使用されている場合、画像が表示されなくなります（リンク切れ）。`
    )
    if (!isConfirmed) return

    try {
        // バックエンドの削除エンドポイントへリクエスト
        await api.delete(`/admin/files/media/${file.id}`)

        // クライアント側の一覧から除去
        files.value = files.value.filter(f => f.id !== file.id)

        // 削除した画像が選択状態だった場合は解除
        if (selectedFile.value?.id === file.id) {
            selectedFile.value = null
        }

        alert('画像を削除しました。')
    } catch (error) {
        console.error('画像の削除エラー:', error)
        alert('画像の削除に失敗しました。')
    }
}

// アップロード処理
const handleFileUpload = async (e) => {
    const file = e.target.files?.[0]
    if (!file) return

    uploading.value = true
    const formData = new FormData()
    formData.append('file', file)

    try {
        const res = await api.post('/admin/files/media', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })

        alert('アップロードしました！')
        const newFile = res.data?.file || res.data
        files.value.unshift(newFile)
        selectedFile.value = newFile
        activeTab.value = 'list'
    } catch (error) {
        console.error('アップロードエラー:', error)
        alert('画像のアップロードに失敗しました。')
    } finally {
        uploading.value = false
        e.target.value = ''
    }
}

// 決定ボタン押下時
const handleSelect = () => {
    if (!selectedFile.value) return
    const fileUrl = getFileUrl(selectedFile.value)
    emit('select', { ...selectedFile.value, url: fileUrl })
}

onMounted(() => {
    fetchMediaList()
})
</script>