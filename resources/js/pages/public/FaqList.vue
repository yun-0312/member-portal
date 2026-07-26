<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
    <div class="max-w-6xl mx-auto space-y-6">

      <!-- 1. ページヘッダー -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-4 gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
            <span>❓</span> FAQ・よくある質問
          </h1>
          <p class="text-xs md:text-sm text-slate-500 mt-1">コールセンターへの質問と回答の一覧</p>
        </div>

        <div class="flex items-center gap-2 self-start md:self-auto">
          <!-- CSVダウンロードボタン -->
          <button
            v-if="exportUrl"
            type="button"
            @click="handleExport"
            :disabled="exporting"
            class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 hover:text-blue-600 bg-white border border-slate-200 hover:border-blue-200 px-3 py-2 rounded-xl shadow-sm transition-all active:scale-95 disabled:opacity-50"
          >
            <span>📥</span>
            <span>{{ exporting ? '出力中…' : 'CSV出力' }}</span>
          </button>

          <router-link
            to="/dashboard"
            class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm transition-all active:scale-95"
          >
            <span>← ダッシュボードへ戻る</span>
          </router-link>
        </div>
      </div>

      <!-- 🔍 検索フォーム (カテゴリ選択 + キーワード検索) -->
      <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
        <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row items-center gap-2">

          <!-- カテゴリ選択ドロップダウン -->
          <div class="w-full sm:w-48 shrink-0">
            <select
              v-model="categoryInput"
              class="w-full px-3 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-700"
            >
              <option value="">すべてのカテゴリ</option>
              <option
                v-for="cat in categoryOptions"
                :key="cat.id"
                :value="cat.id"
              >
                {{ cat.name }}
              </option>
            </select>
          </div>

          <!-- キーワード入力 -->
          <div class="relative flex-1 w-full">
            <input
              v-model="keywordInput"
              type="text"
              placeholder="キーワードで検索 (質問、回答)..."
              class="w-full pl-9 pr-3 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
            />
            <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
          </div>

          <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
            <button
              type="submit"
              class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs md:text-sm py-2 px-4 rounded-xl shadow-sm transition-all active:scale-95"
            >
              検索
            </button>

            <button
              v-if="route.query.keyword || route.query.category"
              type="button"
              @click="clearFilters"
              class="flex-1 sm:flex-none bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium text-xs py-2 px-3 rounded-xl transition-all"
              title="検索条件をクリア"
            >
              クリア
            </button>
          </div>
        </form>
      </div>

      <!-- 2. ローディング表示 -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
        <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm font-medium">FAQを読み込み中…</p>
      </div>

      <!-- 3. メインコンテンツ -->
      <div v-else-if="faqList.length > 0" class="space-y-4">

        <!-- FAQ一覧 (常時表示) -->
        <div class="space-y-4">
          <article
            v-for="faq in faqList"
            :key="faq.id"
            class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-5 md:p-6 space-y-4 transition-all duration-200 hover:border-slate-300"
          >
            <!-- 質問エリア -->
            <div class="space-y-2 border-b border-slate-100 pb-4">
              <div class="flex items-center flex-wrap gap-2 text-xs">
                <!-- 診療区分 (カテゴリバッジ) -->
                <span
                  v-if="faq.category?.name"
                  class="bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-md font-bold"
                >
                  {{ faq.category.name }}
                </span>

                <!-- 受付日（作成日） -->
                <time class="text-slate-400 font-mono ml-auto">
                  📅 受付日: {{ formatDate(faq.created_at) }}
                </time>
              </div>

              <!-- 質問テキスト (Q.) -->
              <div class="flex items-start gap-2.5 pt-1">
                <span class="text-blue-600 font-black text-lg md:text-xl leading-none shrink-0">Q.</span>
                <h2 class="text-base md:text-lg font-bold text-slate-800 leading-snug">
                  {{ faq.question }}
                </h2>
              </div>
            </div>

            <!-- 回答エリア (A.) -->
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-100">
              <div class="flex items-start gap-2.5">
                <span class="text-rose-500 font-black text-lg md:text-xl leading-none shrink-0">A.</span>
                <p
                  class="text-xs md:text-sm text-slate-700 leading-relaxed whitespace-pre-wrap flex-1"
                  v-html="formatBodyWithLinks(faq.answer)"
                ></p>
              </div>
            </div>
          </article>
        </div>

        <!-- 4. ページネーション -->
        <div v-if="paginationLinks.length > 0 && lastPage > 1" class="flex items-center justify-center gap-1.5 pt-6">
          <button
            v-for="(link, idx) in paginationLinks"
            :key="idx"
            @click="changePage(link.url)"
            :disabled="!link.url || link.active"
            v-html="formatPaginationLabel(link.label)"
            :class="[
              'px-3.5 py-2 rounded-xl text-xs font-bold transition-all border',
              link.active
                ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                : link.url
                ? 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'
                : 'bg-slate-100 text-slate-300 border-transparent cursor-not-allowed'
            ]"
          />
        </div>

      </div>

      <!-- 5. 件数ゼロの時 -->
      <div v-else class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400">
        <span class="text-3xl block mb-2">📭</span>
        <p class="text-sm font-medium">条件に一致するFAQはありません</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api.js'

const route = useRoute()
const router = useRouter()

const faqsData = ref(null)
const categoryOptions = ref([]) // カテゴリ一覧
const loading = ref(true)
const exporting = ref(false)

const keywordInput = ref(route.query.keyword || '')
const categoryInput = ref(route.query.category || '')

// データの安全な算出プロパティ
const faqList = computed(() => faqsData.value?.data || [])
const paginationLinks = computed(() => faqsData.value?.links || [])
const lastPage = computed(() => faqsData.value?.last_page || 1)
const exportUrl = computed(() => faqsData.value?.export_url || null)

const handleExport = async () => {
  if (!exportUrl.value || exporting.value) return

  exporting.value = true
  try {
    const response = await api.get(exportUrl.value, {
      responseType: 'blob'
    })

    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')

    link.href = url
    link.setAttribute('download', 'faq.csv')
    document.body.appendChild(link)
    link.click()

    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('CSVのダウンロードに失敗しました:', error)
    alert('CSVのダウンロードに失敗しました。')
  } finally {
    exporting.value = false
  }
}

// 検索実行処理
const handleSearch = () => {
  const query = { ...route.query }

  if (keywordInput.value.trim()) {
    query.keyword = keywordInput.value.trim()
  } else {
    delete query.keyword
  }

  if (categoryInput.value) {
    query.category = categoryInput.value
  } else {
    delete query.category
  }

  delete query.page
  router.push({ query })
}

// 検索条件クリア
const clearFilters = () => {
  keywordInput.value = ''
  categoryInput.value = ''
  const query = { ...route.query }
  delete query.keyword
  delete query.category
  delete query.page
  router.push({ query })
}

// カテゴリ一覧の取得
const fetchFaqs = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page: page,
      ...route.query
    }
    const res = await api.get('/faqs', { params })

    // レスポンスの判定処理
    const data = res.data?.data ? res.data : res

    faqsData.value = data

    // バックエンドから渡ってきた categories をセット
    if (data.categories) {
      categoryOptions.value = data.categories
    }
  } catch (error) {
    console.error('FAQの取得に失敗しました:', error)
  } finally {
    loading.value = false
  }
}

const changePage = (url) => {
  if (!url) return
  const urlParams = new URLSearchParams(url.split('?')[1])
  const page = urlParams.get('page') || 1

  router.push({
    query: {
      ...route.query,
      page: page
    }
  })
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const formatPaginationLabel = (label) => {
  if (label.includes('previous') || label.includes('Previous')) return '前へ'
  if (label.includes('next') || label.includes('Next')) return '次へ'
  return label
}

const formatBodyWithLinks = (text) => {
  if (!text) return ''

  const escapedText = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')

  const urlRegex = /(https?:\/\/[^\s<]+)/g

  return escapedText.replace(urlRegex, (url) => {
    return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline font-medium break-all" onclick="event.stopPropagation()">${url}</a>`
  })
}

// URLクエリ監視
watch(
  () => route.query,
  (newQuery) => {
    keywordInput.value = newQuery.keyword || ''
    categoryInput.value = newQuery.category || ''
    fetchFaqs(newQuery.page || 1)
  }
)

onMounted(() => {
  fetchFaqs(route.query.page || 1)
})
</script>