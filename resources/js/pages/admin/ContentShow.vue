<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
    <div class="max-w-5xl mx-auto space-y-6">

      <!-- 1. ページヘッダー＆アクションエリア -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-4 gap-4">
        <div class="flex items-center gap-3">
          <!-- index_url を活用した一覧に戻るボタン -->
          <router-link
            :to="contentData?.index_url || '/admin/contents'"
            class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-2xs hover:bg-slate-50 transition-all active:scale-95"
          >
            <span>← 一覧へ戻る</span>
          </router-link>
          <h1 class="text-xl md:text-2xl font-extrabold text-slate-800 tracking-tight">
            コンテンツ詳細
          </h1>
        </div>

        <!-- 管理操作（編集・削除）ボタン -->
        <div v-if="contentData?.item" class="flex items-center gap-2 self-end sm:self-auto">
          <!-- update_url を活用した編集ボタン -->
          <router-link
            :to="`${contentData.update_url}/edit`"
            class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 px-3.5 py-2 rounded-xl shadow-2xs transition-all active:scale-95"
          >
            ✏️ 編集
          </router-link>

          <!-- delete_url を活用した削除ボタン -->
          <button
            @click="handleDelete"
            :disabled="deleting"
            class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-100 px-3.5 py-2 rounded-xl shadow-2xs transition-all active:scale-95 disabled:opacity-50 cursor-pointer"
          >
            <span v-if="deleting">削除中…</span>
            <span v-else>🗑️ 削除</span>
          </button>
        </div>
      </div>

      <!-- 2. ローディング表示 -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
        <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm font-medium">コンテンツデータを読み込み中…</p>
      </div>

      <!-- 3. メインコンテンツ -->
      <div v-else-if="contentData?.item" class="space-y-6">

        <!-- メインカード -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">

          <!-- メタヘッダー領域 -->
          <div class="p-6 border-b border-slate-100 bg-slate-50/50 space-y-3">
            <div class="flex items-center justify-between flex-wrap gap-2">

              <!-- カテゴリ・サブカテゴリの階層表示 -->
              <div class="flex items-center gap-2 flex-wrap">
                <span
                  v-if="contentData.item.category"
                  class="px-2.5 py-0.5 rounded-full text-xs font-bold border bg-blue-50 text-blue-700 border-blue-200"
                >
                  {{ contentData.item.category.name }}
                </span>

                <span v-if="contentData.item.category && contentData.item.subcategory" class="text-slate-300 text-xs">
                  &gt;
                </span>

                <span
                  v-if="contentData.item.subcategory"
                  class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200"
                >
                  {{ contentData.item.subcategory.name }}
                </span>
              </div>

              <!-- 開催日 ＆ 公開日時 -->
              <div class="flex items-center gap-3 text-xs font-mono text-slate-500">
                <span v-if="contentData.item.meeting_date" class="inline-flex items-center gap-1 font-bold text-slate-700 bg-amber-50 border border-amber-200/80 px-2 py-0.5 rounded-md">
                  📅 開催日: {{ contentData.item.meeting_date }}
                </span>
                <span>公開日時: {{ formatDate(contentData.item.published_at) }}</span>
              </div>
            </div>

            <!-- タイトル -->
            <h2 class="text-2xl font-extrabold text-slate-800 leading-snug pt-1">
              {{ contentData.item.title }}
            </h2>
          </div>

          <!-- 本文 -->
          <div class="p-6 md:p-8 overflow-hidden">
            <div
              class="prose prose-slate max-w-none text-sm md:text-base text-slate-700 leading-relaxed break-words [word-break:break-word] [&_img]:max-w-full [&_img]:h-auto [&_table]:block [&_table]:max-w-full [&_table]:overflow-x-auto [&_iframe]:max-w-full [&_pre]:max-w-full [&_pre]:overflow-x-auto"
              v-html="contentData.item.body"
            ></div>
          </div>

          <!-- 添付ファイル（ある場合） -->
          <div v-if="contentData.item.files && contentData.item.files.length > 0" class="p-6 bg-slate-50 border-t border-slate-100 space-y-3">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
              📎 添付ファイル
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <button
                v-for="file in contentData.item.files"
                :key="file.id"
                @click="downloadFile(file)"
                :disabled="downloadingId === file.id"
                type="button"
                class="flex items-center justify-between p-3 bg-white rounded-xl border border-slate-200 hover:border-blue-400 hover:shadow-2xs transition-all group text-left cursor-pointer disabled:opacity-50"
              >
                <div class="flex items-center gap-2 min-w-0">
                  <span class="text-slate-400 group-hover:text-blue-600">📄</span>
                  <span class="text-xs font-bold text-slate-700 group-hover:text-blue-600 truncate">
                    {{ file.name || '添付ファイル' }}
                  </span>
                </div>
                <span class="text-xs text-blue-600 font-semibold shrink-0">
                  <span v-if="downloadingId === file.id">DL中…</span>
                  <span v-else class="opacity-0 group-hover:opacity-100 transition-opacity">⬇️ ダウンロード</span>
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- サイド情報・管理用メタデータ（ロール対象・作成者・作成更新日時） -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <!-- 公開対象ロール（追加・削除機能付き） -->
          <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80 space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                👥 閲覧許可ロール
              </h3>
              <button
                @click="showAddRoleModal = true"
                type="button"
                class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded-md transition-all cursor-pointer"
              >
                ＋ ロール追加
              </button>
            </div>

            <!-- 設定中ロールのバッジ一覧（destroy_url があれば除外ボタン表示） -->
            <div class="flex flex-wrap gap-1.5 pt-1">
              <div
                v-for="role in (contentData.roles || contentData.item.roles)"
                :key="role.id"
                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200 group"
              >
                <span>{{ role.name }}</span>
                <!-- 削除用ボタン -->
                <button
                  v-if="role.destroy_url"
                  @click="removeRole(role)"
                  :disabled="processingRoleId === role.id"
                  type="button"
                  title="このロールを削除"
                  class="text-slate-400 hover:text-rose-600 font-bold ml-0.5 cursor-pointer disabled:opacity-50"
                >
                  ×
                </button>
              </div>
            </div>
          </div>

          <!-- 作成者・システム管理情報 -->
          <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80 space-y-2 text-xs text-slate-500">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
              ℹ️ システム管理情報
            </h3>
            <div class="space-y-1.5 font-mono pt-1">
              <div v-if="contentData.item.creator" class="flex justify-between border-b border-slate-100 pb-1">
                <span>作成者:</span>
                <span class="text-slate-800 font-bold">
                  {{ contentData.item.creator.name }} ({{ contentData.item.creator.email }})
                </span>
              </div>
              <div class="flex justify-between">
                <span>作成日時:</span>
                <span class="text-slate-700 font-bold">{{ formatDate(contentData.item.created_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span>更新日時:</span>
                <span class="text-slate-700 font-bold">{{ formatDate(contentData.item.updated_at) }}</span>
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>

    <!-- ロール追加モーダル -->
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

const contentData = ref(null)
const loading = ref(true)
const deleting = ref(false)
const downloadingId = ref(null)

// ロール操作用 state
const showAddRoleModal = ref(false)
const selectedRoleId = ref('')
const addingRole = ref(false)
const processingRoleId = ref(null)

// APIから動的に取得するシステム全体のロールマスタ
const masterRoles = ref([])

// まだ割り当てられていないロールのみ抽出
const availableRoles = computed(() => {
  if (!contentData.value) return []
  const currentRoles = contentData.value.roles || contentData.value.item?.roles || []
  const currentRoleIds = currentRoles.map(r => r.id)
  return masterRoles.value.filter(r => !currentRoleIds.includes(r.id))
})

// システム全体のロールマスタ取得 API
const fetchMasterRoles = async () => {
  try {
    const res = await api.get('/admin/roles') // プロジェクトのロール一覧エンドポイントを指定
    masterRoles.value = res.data?.data || res.data || []
  } catch (error) {
    console.error('ロールマスタ一覧の取得に失敗しました:', error)
  }
}

// コンテンツ詳細データ取得 API
const fetchContentDetail = async () => {
  loading.value = true
  try {
    const id = route.params.id
    const res = await api.get(`/admin/contents/${id}`)
    contentData.value = res.data
  } catch (error) {
    console.error('コンテンツ詳細の取得に失敗しました:', error)
  } finally {
    loading.value = false
  }
}

// ロールの追加処理
const addRole = async () => {
  if (!selectedRoleId.value) return

  addingRole.value = true
  try {
    const targetUrl = contentData.value.role_targetable_url || `/admin/contents/${contentData.value.item.id}/roles`

    await api.post(targetUrl, {
      targetable_id: contentData.value.item.id,
      targetable_type: 'App\\Models\\Content',
      role_id: Number(selectedRoleId.value)
    })

    alert('ロールを追加しました。')
    showAddRoleModal.value = false
    selectedRoleId.value = ''

    // 最新状態を取得して再描画
    await fetchContentDetail()
  } catch (error) {
    console.error('ロールの追加に失敗しました:', error.response?.data || error)
    const msg = error.response?.data?.message || 'ロールの追加に失敗しました。'
    alert(`失敗しました: ${msg}`)
  } finally {
    addingRole.value = false
  }
}

// ロールの削除処理 (destroy_url を活用)
const removeRole = async (role) => {
  if (!confirm(`ロール「${role.name}」を除外しますか？`)) return

  processingRoleId.value = role.id
  try {
    const deleteUrl = role.destroy_url || `/admin/contents/${contentData.value.item.id}/roles/${role.id}`
    await api.delete(deleteUrl)

    alert('ロールを削除しました。')
    // 最新状態を取得して再描画
    await fetchContentDetail()
  } catch (error) {
    console.error('ロールの削除に失敗しました:', error.response?.data || error)
    const msg = error.response?.data?.message || 'ロールの削除に失敗しました。'
    alert(`失敗しました: ${msg}`)
  } finally {
    processingRoleId.value = null
  }
}

// ファイルダウンロード関数
const downloadFile = async (file) => {
  if (!file) return

  const fileUrl = file.download_url || file.url || `/admin/files/${file.id}/download`
  const fileName = file.name || 'download'

  downloadingId.value = file.id

  try {
    const response = await api.get(fileUrl, {
      responseType: 'blob'
    })

    if (response.data.type === 'application/json') {
      const text = await response.data.text()
      const errorJson = JSON.parse(text)
      throw new Error(errorJson.message || 'ファイルの取得に失敗しました。')
    }

    const blob = new Blob([response.data], { type: response.headers['content-type'] })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = fileName

    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    window.URL.revokeObjectURL(link.href)
  } catch (error) {
    console.error('ファイルのダウンロードに失敗しました:', error)
    alert(error.message || 'ファイルのダウンロードに失敗しました。')
  } finally {
    downloadingId.value = null
  }
}

// 削除処理
const handleDelete = async () => {
  if (!confirm('このコンテンツを削除してもよろしいですか？')) return

  deleting.value = true
  try {
    const deleteEndpoint = contentData.value.delete_url
    await api.delete(deleteEndpoint)

    alert('削除が完了しました。')
    router.push(contentData.value.index_url || '/admin/contents')
  } catch (error) {
    console.error('削除に失敗しました:', error)
    alert('削除に失敗しました。')
  } finally {
    deleting.value = false
  }
}

// 日時整形ヘルパー
const formatDate = (isoString) => {
  if (!isoString) return '-'
  const date = new Date(isoString)
  return date.toLocaleString('ja-JP', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  fetchMasterRoles() // ロールマスタの取得
  fetchContentDetail()
})
</script>