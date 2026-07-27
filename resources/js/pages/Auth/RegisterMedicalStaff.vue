<template>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4 md:p-8 text-slate-800">
        <div class="max-w-md w-full bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-6">

        <!-- 1. ヘッダー -->
        <div class="text-center space-y-2">
            <div class="text-3xl">🏥</div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">
            医療機関スタッフ会員登録
            </h1>
            <p class="text-xs text-slate-500">
            必要事項を入力して新規アカウントを作成してください
            </p>
        </div>

        <!-- 2. 通知メッセージ -->
        <!-- 登録完了メッセージ -->
        <div v-if="successMessage" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs md:text-sm font-medium space-y-2">
            <div class="flex items-center gap-2 font-bold text-emerald-800">
            <span>✅</span>
            <span>仮登録が完了しました</span>
            </div>
            <p class="text-xs leading-relaxed">
            {{ successMessage }}<br>
            ご登録いただいたメールアドレスへ確認メールを送信しました。メール内のリンクからメール認証を行ってください。
            </p>
            <div class="pt-2">
            <router-link to="/" class="inline-block px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition-colors">
                ログイン画面へ移動
            </router-link>
            </div>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs md:text-sm font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ errorMessage }}</span>
        </div>

        <!-- 3. 登録フォーム -->
        <form v-if="!successMessage" @submit.prevent="handleSubmit" class="space-y-4">

            <!-- お名前 -->
            <div class="space-y-1">
            <label for="name" class="block text-xs font-bold text-slate-700">
                お名前 <span class="text-rose-500">*</span>
            </label>
            <input
                id="name"
                v-model="form.name"
                type="text"
                autocomplete="name"
                required
                placeholder="例: 医療 太郎"
                class="w-full px-3.5 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
            />
            <p v-if="errors.name" class="text-[11px] text-rose-500 font-medium pt-0.5">
                {{ errors.name[0] }}
            </p>
            </div>

            <!-- メールアドレス -->
            <div class="space-y-1">
            <label for="email" class="block text-xs font-bold text-slate-700">
                メールアドレス <span class="text-rose-500">*</span>
            </label>
            <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                placeholder="example@hospital.or.jp"
                class="w-full px-3.5 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
            />
            <p v-if="errors.email" class="text-[11px] text-rose-500 font-medium pt-0.5">
                {{ errors.email[0] }}
            </p>
            </div>

            <!-- 所属医療機関（検索機能付きセレクター） -->
            <div class="space-y-1 relative">
            <label class="block text-xs font-bold text-slate-700">
                所属医療機関 <span class="text-rose-500">*</span>
            </label>

            <!-- 検索インプット / 選択結果表示 -->
            <div class="relative">
                <input
                v-model="institutionSearch"
                type="text"
                autocomplete="off"
                placeholder="医療機関名を入力して検索..."
                @focus="isDropdownOpen = true"
                class="w-full px-3.5 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                />
                <button
                v-if="form.medical_institution_id"
                type="button"
                @click="clearInstitutionSelection"
                class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold"
                >
                ✕
                </button>
            </div>

            <!-- 医療機関ドロップダウンリスト -->
            <div
                v-if="isDropdownOpen"
                class="absolute z-20 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg divide-y divide-slate-100"
            >
                <div v-if="loadingInstitutions" class="p-3 text-center text-xs text-slate-400">
                医療機関一覧を読み込み中…
                </div>

                <template v-else-if="filteredInstitutions.length > 0">
                <button
                    v-for="item in filteredInstitutions"
                    :key="item.id"
                    type="button"
                    @click="selectInstitution(item)"
                    class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-sky-50 transition-colors flex items-center justify-between"
                    :class="{ 'bg-sky-50/50 font-bold text-sky-600': form.medical_institution_id === item.id }"
                >
                    <span>{{ item.name }}</span>
                    <span v-if="form.medical_institution_id === item.id" class="text-xs">✓</span>
                </button>
                </template>

                <div v-else class="p-3 text-center text-xs text-slate-400">
                該当する医療機関が見つかりません
                </div>
            </div>

            <p v-if="errors.medical_institution_id" class="text-[11px] text-rose-500 font-medium pt-0.5">
                {{ errors.medical_institution_id[0] }}
            </p>
            </div>

            <!-- パスワード -->
            <div class="space-y-1">
            <label for="password" class="block text-xs font-bold text-slate-700">
                パスワード <span class="text-rose-500">*</span>
            </label>
            <input
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                required
                placeholder="••••••••"
                class="w-full px-3.5 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
            />
            <p v-if="errors.password" class="text-[11px] text-rose-500 font-medium pt-0.5">
                {{ errors.password[0] }}
            </p>
            </div>

            <!-- パスワード確認 -->
            <div class="space-y-1">
            <label for="password_confirmation" class="block text-xs font-bold text-slate-700">
                パスワード（確認） <span class="text-rose-500">*</span>
            </label>
            <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                placeholder="••••••••"
                class="w-full px-3.5 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
            />
            </div>

            <!-- 送信ボタン -->
            <div class="pt-2">
            <button
                type="submit"
                :disabled="submitting"
                class="w-full bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs md:text-sm py-2.5 px-4 rounded-xl shadow-sm transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span>{{ submitting ? '送信中…' : '利用申請を送信する' }}</span>
            </button>
            </div>
        </form>

        <!-- ログイン画面リンク -->
        <div v-if="!successMessage" class="text-center pt-2 border-t border-slate-100">
            <router-link to="/" class="text-xs text-sky-600 hover:underline">
            すでにアカウントをお持ちの方はこちら（ログイン）
            </router-link>
        </div>

        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import api from '../../api.js'

// フォームデータ
const form = reactive({
    name: '',
    email: '',
    medical_institution_id: null,
    password: '',
    password_confirmation: ''
})

// UI状態
const institutions = ref([])
const institutionSearch = ref('')
const isDropdownOpen = ref(false)
const loadingInstitutions = ref(false)
const submitting = ref(false)

const successMessage = ref('')
const errorMessage = ref('')
const errors = ref({})

// 医療機関のリアルタイム絞り込み検索
const filteredInstitutions = computed(() => {
    if (!institutionSearch.value.trim()) {
        return institutions.value
    }
    const query = institutionSearch.value.toLowerCase()
    return institutions.value.filter(item => 
        item.name.toLowerCase().includes(query)
    )
})

// 医療機関API一覧の取得 (`AuthController@medicalInstitutions`)
const fetchMedicalInstitutions = async () => {
    loadingInstitutions.value = true
    try {
        const res = await api.get('/medical-institutions') // ルートに合わせて調整してください
        institutions.value = res.data?.data || res.data || []
    } catch (error) {
        console.error('医療機関一覧の取得に失敗しました:', error)
    } finally {
        loadingInstitutions.value = false
    }
}

// 医療機関の選択処理
const selectInstitution = (item) => {
    form.medical_institution_id = item.id
    institutionSearch.value = item.name
    isDropdownOpen.value = false
}

// 選択のクリア
const clearInstitutionSelection = () => {
    form.medical_institution_id = null
    institutionSearch.value = ''
    isDropdownOpen.value = true
}

// ドロップダウンの外側をクリックした時に閉じる処理
const handleClickOutside = (event) => {
    const el = event.target
    if (!el.closest('.relative')) {
        isDropdownOpen.value = false
    }
}

// フォーム送信（`registerMedicalStaff` エンドポイントへ）
const handleSubmit = async () => {
    submitting.value = true
    successMessage.value = ''
    errorMessage.value = ''
    errors.value = {}

    if (!form.medical_institution_id) {
        errors.value = { medical_institution_id: ['所属医療機関を選択してください。'] }
        submitting.value = false
        return
    }

    try {
        const res = await api.post('/register-medical-staff', form)
        successMessage.value = res.data?.message || '登録が完了しました。承認をお待ちください。'
    } catch (error) {
        console.error('会員登録エラー:', error)
        if (error.response?.status === 422 && error.response?.data?.errors) {
        errors.value = error.response.data.errors
        } else {
        errorMessage.value = error.response?.data?.message || '登録処理に失敗しました。入力内容をご確認ください。'
        }
    } finally {
        submitting.value = false
    }
}

onMounted(() => {
    fetchMedicalInstitutions()
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>