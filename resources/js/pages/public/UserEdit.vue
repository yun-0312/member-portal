<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
    <div class="max-w-2xl mx-auto space-y-6">

      <!-- 1. ヘッダーエリア -->
      <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
            <span>👤</span> アカウント設定
          </h1>
          <p class="text-xs md:text-sm text-slate-500 mt-1">登録情報（お名前・メールアドレス）の変更ができます</p>
        </div>

        <router-link
          to="/dashboard"
          class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm transition-all active:scale-95"
        >
          <span>← ダッシュボードへ戻る</span>
        </router-link>
      </div>

      <!-- 2. ローディング表示 -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
        <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm font-medium">ユーザー情報を読み込み中…</p>
      </div>

      <!-- 3. 編集フォーム -->
      <div v-else class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6 md:p-8 space-y-6">

        <!-- アラート通知メッセージ -->
        <div v-if="successMessage" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs md:text-sm font-medium flex items-center gap-2">
          <span>✅</span>
          <span>{{ successMessage }}</span>
        </div>

        <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs md:text-sm font-medium flex items-center gap-2">
          <span>⚠️</span>
          <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- お名前 -->
          <div class="space-y-1.5">
            <label for="name" class="block text-xs md:text-sm font-bold text-slate-700">
              お名前 <span class="text-rose-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              placeholder="例: 山田 太郎"
              class="w-full px-4 py-2.5 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-800"
            />
            <p v-if="errors.name" class="text-xs text-rose-500 font-medium pt-1">
              {{ errors.name[0] }}
            </p>
          </div>

          <!-- メールアドレス -->
          <div class="space-y-1.5">
            <label for="email" class="block text-xs md:text-sm font-bold text-slate-700">
              メールアドレス <span class="text-rose-500">*</span>
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              placeholder="例: example@clinic.or.jp"
              class="w-full px-4 py-2.5 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-800"
            />
            <p v-if="errors.email" class="text-xs text-rose-500 font-medium pt-1">
              {{ errors.email[0] }}
            </p>
          </div>

          <!-- ボタンエリア -->
          <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <button
              type="button"
              @click="router.back()"
              class="px-4 py-2.5 text-xs md:text-sm font-bold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all"
            >
              キャンセル
            </button>

            <button
              type="submit"
              :disabled="saving"
              class="inline-flex items-center gap-2 px-6 py-2.5 text-xs md:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <span>{{ saving ? '保存中…' : '変更内容を保存' }}</span>
            </button>
          </div>
        </form>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api.js' // プロジェクトのaxiosインスタンスパスに合わせて調整してください

const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const errors = ref({})

// フォームの入力状態
const form = reactive({
  name: '',
  email: ''
})

// 初期表示時に現在のユーザー情報を取得
const fetchUserProfile = async () => {
  loading.value = true
  try {
    // ログイン中のユーザー情報を取得するエンドポイント (例: /user または /me)
    const res = await api.get('/user')
    const user = res.data?.data || res.data

    form.name = user.name || ''
    form.email = user.email || ''
  } catch (error) {
    console.error('ユーザー情報の取得に失敗しました:', error)
    errorMessage.value = 'ユーザー情報の読み込みに失敗しました。'
  } finally {
    loading.value = false
  }
}

// ユーザー情報の更新リクエスト
const handleSubmit = async () => {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''
  errors.value = {}

  try {
    // PUT /user または /user/profile 等へ名前とメールアドレスのみ送信
    const response = await api.put('/user', {
      name: form.name,
      email: form.email
    })

    successMessage.value = 'ユーザー情報を正常に更新しました！'
    
    // バックエンドから返ってきた最新データを反映
    if (response.data) {
      const updatedUser = response.data.data || response.data
      form.name = updatedUser.name || form.name
      form.email = updatedUser.email || form.email
    }
  } catch (error) {
    console.error('ユーザー情報の更新に失敗しました:', error)
    
    // バリデーションエラー (422) の処理
    if (error.response?.status === 422 && error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || '更新処理に失敗しました。時間をおいて再度お試しください。'
    }
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchUserProfile()
})
</script>