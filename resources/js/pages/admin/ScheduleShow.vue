<template>
  <div class="max-w-4xl mx-auto p-4 md:p-6 space-y-6">
    <!-- 1. ローディング表示 -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 space-y-3">
      <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-sm font-medium text-gray-500">データを読み込んでいます…</p>
    </div>

    <!-- 2. エラー表示 -->
    <div v-else-if="error" class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-center text-sm">
      {{ error }}
    </div>

    <!-- 3. メイン表示エリア（データ取得完了後） -->
    <template v-else-if="scheduleData">
      <!-- トップアクションバー -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span
              v-if="schedule?.category"
              class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-blue-50 text-blue-700 rounded-md"
            >
              {{ schedule.category.name }}
            </span>
            <span
              v-if="isRecurring"
              class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-600 rounded-md"
            >
              🔄 定期スケジュール
            </span>
          </div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
            {{ schedule?.title }}
          </h1>
        </div>

        <!-- アクションボタン群 -->
        <div class="flex items-center gap-2 self-start sm:self-auto">
          <!-- 一覧に戻る -->
          <router-link
            :to="indexUrl"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold text-xs md:text-sm rounded-xl shadow-2xs transition-all active:scale-95"
          >
            <span>←</span>
            <span>スケジュール一覧に戻る</span>
          </router-link>

          <!-- 編集する -->
          <router-link
            v-if="editUrl"
            :to="editUrl"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs md:text-sm rounded-xl shadow-2xs transition-all active:scale-95"
          >
            <span>✏️</span>
            <span>編集する</span>
          </router-link>

          <!-- メイン削除ボタン -->
          <button
            @click="openDeleteModal(occurrence?.id)"
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

      <!-- 詳細メイン情報カード -->
      <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs p-6 md:p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- 日時（選択中のOccurrence） -->
          <div class="flex items-start gap-3">
            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl text-xl">🕒</div>
            <div>
              <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">日時</div>
              <div class="text-base md:text-lg font-bold text-gray-900">
                {{ formatDate(occurrence?.start_at) }}
              </div>
              <div class="text-sm font-medium text-gray-600">
                {{ formatTime(occurrence?.start_at) }} 〜 {{ formatTime(occurrence?.end_at) }}
              </div>
            </div>
          </div>

          <!-- 場所・会議室 -->
          <div class="flex items-start gap-3">
            <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl text-xl">📍</div>
            <div>
              <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">場所・会議室</div>
              <div class="text-base md:text-lg font-bold text-gray-900">
                {{ schedule?.room?.name || schedule?.location || '指定なし' }}
              </div>
            </div>
          </div>

          <!-- 関連URL -->
          <div v-if="schedule?.url" class="flex items-start gap-3 md:col-span-2">
            <div class="p-2.5 bg-purple-50 text-purple-600 rounded-xl text-xl">🔗</div>
            <div>
              <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">関連リンク</div>
              <a
                :href="schedule.url"
                target="_blank"
                rel="noopener noreferrer"
                class="text-blue-600 hover:underline font-medium break-all"
              >
                {{ schedule.url }}
              </a>
            </div>
          </div>
        </div>

        <!-- 今後の全発生回（Occurrences）リスト -->
        <div v-if="schedule?.occurrences?.length" class="pt-6 border-t border-gray-100">
          <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
            <span>📅</span>
            <span>この予定の全開催スケジュール</span>
          </h3>
          <div class="max-h-60 overflow-y-auto rounded-xl border border-gray-100 bg-gray-50/50 p-2 space-y-1">
            <div
              v-for="occ in schedule.occurrences"
              :key="occ.id"
              class="flex items-center justify-between p-2.5 rounded-lg text-xs md:text-sm transition-all bg-white hover:shadow-2xs"
              :class="{ 'bg-blue-50/60 border border-blue-200': occ.id === occurrence?.id }"
            >
              <!-- 日時表示 -->
              <div class="flex items-center gap-2">
                <span class="font-medium text-gray-800">
                  {{ formatDate(occ.start_at) }} {{ formatTime(occ.start_at) }}〜{{ formatTime(occ.end_at) }}
                </span>
                <span v-if="occ.id === occurrence?.id" class="text-2xs text-blue-600 font-bold px-1.5 py-0.5 bg-white rounded border border-blue-200">
                  表示中
                </span>
              </div>

              <!-- アクションボタン群（編集 / 削除） -->
              <div class="flex items-center gap-1.5">
                <router-link
                  :to="occ.update_url"
                  class="px-2.5 py-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-md transition-colors font-bold"
                >
                  ✏️ 編集
                </router-link>

                <button
                  @click="openDeleteModal(occ.id)"
                  type="button"
                  class="px-2 py-1 text-xs bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-md transition-colors font-medium cursor-pointer"
                >
                  削除
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- 4. 削除選択モーダル -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-5 shadow-xl">
        <div class="flex items-center gap-3 text-rose-600">
          <span class="text-2xl">🗑️</span>
          <h3 class="font-bold text-lg text-gray-900">予定の削除</h3>
        </div>

        <p class="text-sm text-gray-600">
          削除する方法を選択してください。
        </p>

        <!-- 単発予定の場合 -->
        <div v-if="!isRecurring" class="space-y-3">
          <p class="text-xs text-gray-500">この予定を削除します。</p>
          <div class="flex justify-end gap-2">
            <button
              type="button"
              @click="closeDeleteModal"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl"
            >
              キャンセル
            </button>
            <button
              type="button"
              @click="executeDelete('single')"
              :disabled="deleting"
              class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl"
            >
              削除する
            </button>
          </div>
        </div>

        <!-- 定期予定（繰り返す予定）の場合 -->
        <div v-else class="space-y-3">
          <button
            type="button"
            @click="executeDelete('single')"
            :disabled="deleting"
            class="w-full text-left p-3.5 rounded-xl border border-gray-200 hover:border-blue-500 hover:bg-blue-50/50 transition-all group"
          >
            <div class="font-bold text-sm text-gray-800 group-hover:text-blue-600">この予定のみ削除</div>
            <div class="text-2xs text-gray-500">選択した特定の日の予定だけを削除します。</div>
          </button>

          <button
            type="button"
            @click="executeDelete('future')"
            :disabled="deleting"
            class="w-full text-left p-3.5 rounded-xl border border-gray-200 hover:border-blue-500 hover:bg-blue-50/50 transition-all group"
          >
            <div class="font-bold text-sm text-gray-800 group-hover:text-blue-600">これ以降の予定をすべて削除</div>
            <div class="text-2xs text-gray-500">選択した日以降の繰り返し予定を削除します。</div>
          </button>

          <button
            type="button"
            @click="executeDelete('all')"
            :disabled="deleting"
            class="w-full text-left p-3.5 rounded-xl border border-rose-200 hover:border-rose-500 hover:bg-rose-50/50 transition-all group"
          >
            <div class="font-bold text-sm text-rose-700">すべての予定（繰り返し全体）を削除</div>
            <div class="text-2xs text-rose-500">過去・未来含め、この繰り返しのすべての予定を削除します。</div>
          </button>

          <div class="pt-2 flex justify-end">
            <button
              type="button"
              @click="closeDeleteModal"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl"
            >
              キャンセル
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const props = defineProps({
  indexUrl: {
    type: String,
    default: '/admin/schedules',
  },
})

const route = useRoute()
const router = useRouter()

const scheduleData = ref(null)
const loading = ref(true)
const error = ref(null)
const deleting = ref(false)

// 削除モーダル関連の状態
const showDeleteModal = ref(false)
const targetOccurrenceId = ref(null)

// データを取得する
const fetchScheduleData = async (id) => {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get(`/api/admin/occurrences/${id}`, {
      headers: {
        'Accept': 'application/json',
      }
    })
    scheduleData.value = response.data
  } catch (err) {
    console.error('データ取得エラー:', err)
    error.value = 'スケジュールデータの取得に失敗しました。'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (route.params.id) {
    fetchScheduleData(route.params.id)
  } else {
    error.value = 'IDが指定されていません。'
    loading.value = false
  }
})

watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      fetchScheduleData(newId)
    }
  }
)

const occurrence = computed(() => scheduleData.value?.occurrence)
const schedule = computed(() => scheduleData.value?.schedule)

// JSON内の update_url を利用して編集画面へ遷移（優先度: occurrence.update_url > schedule.update_url）
const editUrl = computed(() => {

  const baseUrl = occurrence.value?.update_url || schedule.value?.update_url
  if (!baseUrl) return null

  // すでに /edit が含まれていればそのまま、なければ付与
  return baseUrl.endsWith('/edit') ? baseUrl : `${baseUrl}/edit`
})

const isRecurring = computed(() => {
  return (schedule.value?.recurrences?.length > 0)
})

// 削除モーダルを開く
const openDeleteModal = (targetId) => {
  targetOccurrenceId.value = targetId || occurrence.value?.id
  showDeleteModal.value = true
}

// 削除モーダルを閉じる
const closeDeleteModal = () => {
  showDeleteModal.value = false
  targetOccurrenceId.value = null
}


const executeDelete = async (mode) => {
  if (!targetOccurrenceId.value) return

  deleting.value = true
  try {
    // 削除対象のOccurrenceを検索
    const targetOcc = schedule.value?.occurrences?.find(o => o.id === targetOccurrenceId.value)

    // JSONの destroy_url があればそれを優先、無ければデフォルトパスを指定
    const deleteApiUrl = targetOcc?.destroy_url || `/api/admin/occurrences/${targetOccurrenceId.value}`

    await axios.delete(deleteApiUrl, {
      data: { mode: mode },
      headers: { 'Accept': 'application/json' }
    })

    closeDeleteModal()

    // 単発予定（!isRecurring）の場合、または全体削除・未来一括削除・表示中アイテムの削除の場合
    if (!isRecurring.value || mode === 'all' || mode === 'future' || targetOccurrenceId.value === occurrence.value?.id) {
      // 削除後は一覧画面へ逃がす（データ再取得による404エラーを防ぐ）
      router.push(props.indexUrl)
    } else {
      // リスト内の「別の発生回」のみを削除した場合はデータを再読み込み
      fetchScheduleData(occurrence.value.id)
    }
  } catch (err) {
    console.error('削除失敗:', err)
    alert(err.response?.data?.message || '削除処理に失敗しました。')
  } finally {
    deleting.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('ja-JP', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    weekday: 'short',
  }).format(date)
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('ja-JP', {
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)
}
</script>