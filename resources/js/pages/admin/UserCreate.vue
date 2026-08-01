<template>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- ヘッダーエリア -->
        <div class="flex items-center gap-3 border-b border-slate-200 pb-5">
            <button
                @click="$router.back()"
                class="inline-flex items-center gap-1 px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-all active:scale-95 shadow-2xs cursor-pointer shrink-0"
            >
                <span>‹</span>
                <span>戻る</span>
            </button>
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                    ユーザー新規登録
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    新しいユーザーアカウントを作成します
                </p>
            </div>
        </div>

        <!-- アラート表示 (成功・全体エラー) -->
        <div v-if="successMessage" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium flex items-center justify-between">
            <span>{{ successMessage }}</span>
        </div>
        <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm font-medium flex items-center justify-between">
            <span>{{ errorMessage }}</span>
        </div>

        <!-- 入力フォーム -->
        <form @submit.prevent="submitForm" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs space-y-6">

            <!-- 氏名 (name) -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-2">
                    氏名 <span class="text-rose-500">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="山田 太郎"
                    class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
                    :class="errors.name ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200'"
                />
                <p v-if="errors.name" class="mt-1.5 text-xs text-rose-500 font-medium">{{ errors.name[0] }}</p>
            </div>

            <!-- メールアドレス (email) -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-2">
                    メールアドレス <span class="text-rose-500">*</span>
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    required
                    placeholder="user@example.com"
                    class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
                    :class="errors.email ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200'"
                />
                <p v-if="errors.email" class="mt-1.5 text-xs text-rose-500 font-medium">{{ errors.email[0] }}</p>
            </div>

            <!-- パスワード & パスワード確認 (2列レイアウト) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- パスワード (password) -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-2">
                        パスワード (8文字以上) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
                        :class="errors.password ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200'"
                    />
                    <p v-if="errors.password" class="mt-1.5 text-xs text-rose-500 font-medium">{{ errors.password[0] }}</p>
                </div>

                <!-- 🔑 確認用パスワード (password_confirmation) -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-2">
                        パスワード確認 <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all"
                        :class="errors.password_confirmation ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200'"
                    />
                    <p v-if="errors.password_confirmation" class="mt-1.5 text-xs text-rose-500 font-medium">{{ errors.password_confirmation[0] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- 権限ロール (role_id) -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-2">
                        権限 (Role) <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-model="form.role_id"
                        required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all cursor-pointer"
                        :class="errors.role_id ? 'border-rose-400 bg-rose-50/20' : ''"
                    >
                        <option value="" disabled>権限を選択してください</option>
                        <option v-for="role in roles" :key="role.id" :value="role.id">
                            {{ role.name }}
                        </option>
                    </select>
                    <p v-if="errors.role_id" class="mt-1.5 text-xs text-rose-500 font-medium">{{ errors.role_id[0] }}</p>
                </div>

                <!-- 所属医療機関 (medical_institution_id) -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-2">
                        所属医療機関
                    </label>
                    <select
                        v-model="form.medical_institution_id"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all cursor-pointer"
                        :class="errors.medical_institution_id ? 'border-rose-400 bg-rose-50/20' : ''"
                    >
                        <option :value="null">所属なし (指定なし)</option>
                        <option v-for="institution in medicalInstitutions" :key="institution.id" :value="institution.id">
                            {{ institution.name }}
                        </option>
                    </select>
                    <p v-if="errors.medical_institution_id" class="mt-1.5 text-xs text-rose-500 font-medium">{{ errors.medical_institution_id[0] }}</p>
                </div>
            </div>

            <!-- ステータス (status) -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-2">
                    ステータス
                </label>
                <div class="flex flex-wrap gap-4 pt-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" v-model.number="form.status" :value="1" class="text-indigo-600 focus:ring-indigo-500" />
                        <span class="text-sm font-medium text-slate-700">承認済み (1)</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" v-model.number="form.status" :value="0" class="text-indigo-600 focus:ring-indigo-500" />
                        <span class="text-sm font-medium text-slate-700">承認待ち (0)</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" v-model.number="form.status" :value="2" class="text-indigo-600 focus:ring-indigo-500" />
                        <span class="text-sm font-medium text-slate-700">却下 (2)</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" v-model.number="form.status" :value="3" class="text-indigo-600 focus:ring-indigo-500" />
                        <span class="text-sm font-medium text-slate-700">退職済み (3)</span>
                    </label>
                </div>
                <p v-if="errors.status" class="mt-1.5 text-xs text-rose-500 font-medium">{{ errors.status[0] }}</p>
            </div>

            <!-- ボタンエリア -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button
                    type="button"
                    @click="$router.back()"
                    class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition-all cursor-pointer"
                >
                    キャンセル
                </button>
                <button
                    type="submit"
                    :disabled="submitting"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-300 text-white text-sm font-semibold rounded-xl shadow-sm transition-all cursor-pointer flex items-center gap-2"
                >
                    <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ submitting ? '登録中...' : 'ユーザーを登録する' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api'

const router = useRouter()

// フォームの入力初期状態
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '', // 👈 追加
    role_id: '',
    status: 1,
    medical_institution_id: null,
})

// ドロップダウン用データ
const roles = ref([])
const medicalInstitutions = ref([])

// 状態管理
const submitting = ref(false)
const errors = ref({})
const errorMessage = ref(null)
const successMessage = ref(null)

// ロール一覧＆医療機関一覧を取得
const fetchFormOptions = async () => {
    try {
        const res = await api.get('/admin/users/options')
        roles.value = res.data.roles || []
        medicalInstitutions.value = res.data.medical_institutions || []
    } catch (err) {
        console.error('マスターデータの取得失敗:', err)
        errorMessage.value = 'フォーム用データの読み込みに失敗しました。'
    }
}

// フォーム送信
const submitForm = async () => {
    errors.value = {}
    errorMessage.value = null
    successMessage.value = null

    // 🔒 フロント側でパスワード一致チェック
    if (form.value.password !== form.value.password_confirmation) {
        errors.value.password_confirmation = ['パスワードが一致しません。']
        return
    }

    submitting.value = true

    try {
        await api.post('/admin/users', form.value)

        successMessage.value = 'ユーザーの登録が完了しました。'

        setTimeout(() => {
            router.push('/admin/users')
        }, 1500)

    } catch (err) {
        console.error('ユーザー登録エラー:', err)
        if (err.response && err.response.status === 422) {
            errors.value = err.response.data.errors || {}
        } else {
            errorMessage.value = '登録処理中にエラーが発生しました。入力内容を確認してください。'
        }
    } finally {
        submitting.value = false
    }
}

onMounted(() => {
    fetchFormOptions()
})
</script>