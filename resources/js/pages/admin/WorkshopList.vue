<template>
  <div class="max-w-6xl mx-auto p-4 md:p-6 space-y-6">
    <!-- ヘッダーエリア -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">研修会管理一覧</h1>
        <p class="text-sm text-slate-500 mt-1">開催予定の研修会・説明会の登録・管理が行えます。</p>
      </div>

      <!-- 右上アクションボタン（新規作成 ＆ ダッシュボードへ戻る） -->
      <div class="flex items-center gap-3 self-start md:self-auto">
        <router-link
          v-if="storeUrl"
          :to="storeUrl"
          class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl shadow-sm hover:shadow transition-all active:scale-95"
        >
          <span>➕</span>
          <span>新規研修会作成</span>
        </router-link>

        <router-link
          to="/admin/dashboard"
          class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm transition-all active:scale-95"
        >
          <span>← ダッシュボード</span>
        </router-link>
      </div>
    </div>

    <!-- ローディング表示 -->
    <div v-if="isLoading" class="text-center py-12 text-slate-500">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-300 border-t-blue-600 mb-2"></div>
      <p class="text-sm">データを読み込み中...</p>
    </div>

    <!-- 研修会リスト -->
    <div v-else-if="workshops.length > 0" class="grid gap-4">
      <div
        v-for="workshop in workshops"
        :key="workshop.id"
        class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow p-5 flex flex-col md:flex-row justify-between gap-4"
      >
        <!-- メイン情報 -->
        <div class="space-y-3 flex-1">
          <!-- タイトル ＆ 開催形態バッジ -->
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 flex-wrap">
              <span
                :class="[
                  'text-[11px] font-bold px-2.5 py-0.5 rounded-full border',
                  getLocationBadgeStyle(workshop.location)
                ]"
              >
                {{ getLocationType(workshop.location) }}
              </span>
              <h2 class="text-base font-bold text-slate-800">
                {{ workshop.title }}
              </h2>
            </div>

            <!-- モバイル用詳細・編集ボタン -->
            <router-link
              v-if="workshop.show_url"
              :to="workshop.show_url"
              class="md:hidden bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200 hover:border-blue-200 px-2.5 py-1 rounded-lg text-xs font-semibold transition-all active:scale-95 flex items-center gap-1"
            >
              <span>✏️</span>
              <span>詳細・編集</span>
            </router-link>
          </div>

          <!-- 概要テキスト -->
          <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
            {{ workshop.description }}
          </p>

          <!-- 詳細メタ情報（日時・場所・講師） -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-xs text-slate-600 pt-2 border-t border-slate-100">
            <!-- 日時 -->
            <div class="flex items-center gap-1.5">
              <span class="text-slate-400 font-medium">日時:</span>
              <span class="font-mono text-slate-700 font-semibold">
                {{ formatDateTimeRange(workshop.start_at, workshop.end_at) }}
              </span>
            </div>

            <!-- 場所 -->
            <div class="flex items-center gap-1.5">
              <span class="text-slate-400 font-medium">場所:</span>
              <span class="text-slate-700 truncate" :title="workshop.location">
                {{ workshop.location }}
              </span>
            </div>

            <!-- 講師 -->
            <div class="flex items-center gap-1.5 sm:col-span-2 md:col-span-1">
              <span class="text-slate-400 font-medium">講師:</span>
              <span class="text-slate-700 truncate" :title="workshop.lecture">
                {{ workshop.lecture }}
              </span>
            </div>
          </div>
        </div>

        <!-- PC用詳細・編集ボタン（右側配置） -->
        <div v-if="workshop.show_url" class="hidden md:flex items-center border-l border-slate-100 pl-4 shrink-0">
          <router-link
            :to="workshop.show_url"
            class="bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-600 border border-slate-200 hover:border-blue-300 px-3 py-2 rounded-xl text-xs font-bold transition-all active:scale-95 flex items-center gap-1.5 shadow-sm"
          >
            <span>✏️</span>
            <span>詳細・編集</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- 件数ゼロの場合 -->
    <div v-else class="text-center py-12 bg-white rounded-xl border border-slate-200 text-slate-500">
      現在登録されている研修会はありません。
    </div>

    <!-- ページネーション -->
    <div v-if="lastPage > 1" class="flex justify-center items-center gap-2 pt-4">
      <button
        @click="fetchWorkshops(currentPage - 1)"
        :disabled="currentPage === 1"
        class="px-3 py-1.5 rounded border border-slate-300 text-xs font-medium text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50"
      >
        前へ
      </button>

      <span class="text-xs text-slate-600 font-mono px-2">
        {{ currentPage }} / {{ lastPage }}
      </span>

      <button
        @click="fetchWorkshops(currentPage + 1)"
        :disabled="currentPage === lastPage"
        class="px-3 py-1.5 rounded border border-slate-300 text-xs font-medium text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50"
      >
        次へ
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../api.js'

const workshops = ref([])
const storeUrl = ref('/admin/workshops/create') // フォールバック用初期値
const totalWorkshops = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const isLoading = ref(false)

// APIから研修会一覧を取得（管理者用API endpoint）
const fetchWorkshops = async (page = 1) => {
  isLoading.value = true
  try {
    const response = await api.get(`/admin/workshops?page=${page}`)
    const resData = response.data

    workshops.value = resData.data || []
    currentPage.value = resData.current_page || 1
    lastPage.value = resData.last_page || 1
    totalWorkshops.value = resData.total || 0

    // store_url (新規作成用URL) の取得
    if (resData.store_url) {
      storeUrl.value = resData.store_url.includes('/create') 
        ? resData.store_url 
        : `${resData.store_url}/create`
    }
  } catch (error) {
    console.error('研修会データの取得に失敗しました:', error)
    workshops.value = []
  } finally {
    isLoading.value = false
  }
}

// 日時表示整形用の関数 (例: "2026/09/24(木) 17:13 〜 19:13")
const formatDateTimeRange = (startStr, endStr) => {
  if (!startStr || !endStr) return ''

  const startDate = new Date(startStr.replace(/-/g, '/'))
  const endDate = new Date(endStr.replace(/-/g, '/'))

  const dayOfWeekNames = ['日', '月', '火', '水', '木', '金', '土']
  const year = startDate.getFullYear()
  const month = String(startDate.getMonth() + 1).padStart(2, '0')
  const day = String(startDate.getDate()).padStart(2, '0')
  const dayOfWeek = dayOfWeekNames[startDate.getDay()]

  const startHours = String(startDate.getHours()).padStart(2, '0')
  const startMinutes = String(startDate.getMinutes()).padStart(2, '0')
  const endHours = String(endDate.getHours()).padStart(2, '0')
  const endMinutes = String(endDate.getMinutes()).padStart(2, '0')

  return `${year}/${month}/${day}(${dayOfWeek}) ${startHours}:${startMinutes}〜${endHours}:${endMinutes}`
}

// 開催場所に応じた種別テキストの抽出
const getLocationType = (location) => {
  if (!location) return '対面'
  if (location.includes('ハイブリッド')) return '現地＋Web'
  if (location.includes('Zoom') || location.includes('Web')) return 'オンライン'
  return '現地開催'
}

// 開催種別バッジのスタイル切り替え
const getLocationBadgeStyle = (location) => {
  const type = getLocationType(location)
  switch (type) {
    case '現地＋Web':
      return 'bg-purple-50 text-purple-700 border-purple-200'
    case 'オンライン':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    default:
      return 'bg-blue-50 text-blue-700 border-blue-200'
  }
}

onMounted(() => {
  fetchWorkshops()
})
</script>