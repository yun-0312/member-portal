<template>
    <div class="max-w-2xl mx-auto p-4 md:p-6 space-y-6">
        <!-- ヘッダー -->
        <div class="border-b border-slate-200 pb-4">
        <h1 class="text-2xl font-bold text-slate-800">【管理者】医療機関情報の編集</h1>
        <p class="text-sm text-slate-500 mt-1">医療機関の基本情報および代表者の変更を行えます。</p>
        </div>

        <!-- ローディング表示 -->
        <div v-if="isLoading" class="text-center py-12 text-slate-500">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-300 border-t-blue-600 mb-2"></div>
        <p class="text-sm">データを読み込み中...</p>
        </div>

        <!-- フォームカード -->
        <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form @submit.prevent="handleSubmit" class="space-y-4">

            <!-- 全体エラーメッセージ -->
            <div
            v-if="errorMessage"
            class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-start gap-2"
            >
            <svg class="w-4 h-4 text-rose-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>{{ errorMessage }}</div>
            </div>

            <!-- 医療機関名 (管理者用のため required なし) -->
            <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                医療機関名
            </label>
            <input
                v-model="form.name"
                type="text"
                placeholder="例: 石田消化器内科"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                :class="errors.name ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300'"
            />
            <p v-if="errors.name" class="text-xs text-rose-600 mt-1">{{ errors.name[0] }}</p>
            </div>

            <!-- 郵便番号 & 電話番号 (横並び) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- 郵便番号 -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">郵便番号</label>
                <input
                v-model="form.postcode"
                type="text"
                placeholder="531-5166"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition-all font-mono"
                :class="errors.postcode ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300'"
                />
                <p v-if="errors.postcode" class="text-xs text-rose-600 mt-1">{{ errors.postcode[0] }}</p>
            </div>

            <!-- 電話番号 -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">電話番号</label>
                <input
                v-model="form.phone"
                type="text"
                placeholder="03-7988-2186"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition-all font-mono"
                :class="errors.phone ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300'"
                />
                <p v-if="errors.phone" class="text-xs text-rose-600 mt-1">{{ errors.phone[0] }}</p>
            </div>
            </div>

            <!-- 所在地 -->
            <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">所在地</label>
            <input
                v-model="form.address"
                type="text"
                placeholder="例: 青森県坂本市宮沢町笹田6-4-6"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                :class="errors.address ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300'"
            />
            <p v-if="errors.address" class="text-xs text-rose-600 mt-1">{{ errors.address[0] }}</p>
            </div>

            <hr class="border-slate-100 my-4" />

            <!-- 代表者 (管理者用のため required なし & 未選択OK) -->
            <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                代表者
            </label>
            <select
                v-model="form.representative_user_id"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition-all bg-white cursor-pointer"
                :class="errors.representative_user_id ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300'"
            >
                <option :value="null">代表者なし（指定なし）</option>
                <option
                v-for="user in userList"
                :key="user.id"
                :value="user.id"
                >
                {{ user.name }} (ID: {{ user.id }})
                </option>
            </select>
            <p v-if="errors.representative_user_id" class="text-xs text-rose-600 mt-1">
                {{ errors.representative_user_id[0] }}
            </p>
            </div>

            <!-- ボタンエリア -->
            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button
                type="button"
                @click="handleCancel"
                class="px-4 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer"
            >
                キャンセル
            </button>
            <button
                type="submit"
                :disabled="isSubmitting"
                class="inline-flex items-center px-4 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm disabled:opacity-50 cursor-pointer"
            >
                <span v-if="isSubmitting" class="inline-block animate-spin mr-1.5 h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                {{ isSubmitting ? '更新中...' : '変更を保存' }}
            </button>
            </div>
        </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api.js'

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')
const errors = ref({})

// 代表者選択肢用ユーザーリスト
const userList = ref([])

const form = reactive({
    name: '',
    postcode: '',
    address: '',
    phone: '',
    representative_user_id: null
})

const institutionId = route.params.id || route.params.medicalInstitutionId

// データの並行取得
const loadData = async () => {
    if (!institutionId) return

    isLoading.value = true
    try {
        const [detailRes, usersRes] = await Promise.all([
            api.get(`/admin/medical-institutions/${institutionId}`),
            api.get('/admin/users/representatives', {
                params: {
                    medical_institution_id: institutionId
                }
            })
        ])

        // 1. 医療機関詳細データのセット
        const inst = detailRes.data?.institution || detailRes.data
        if (inst) {
            form.name = inst.name || ''
            form.postcode = inst.postcode || ''
            form.address = inst.address || ''
            form.phone = inst.phone || ''
            form.representative_user_id = inst.representative_user_id || null
        }

        // 2. 代表者選択肢のユーザーリストをセット
        const rawUsers = usersRes.data?.data || usersRes.data?.users || usersRes.data || []
        userList.value = rawUsers

    } catch (error) {
        console.error('データ取得失敗:', error)
        errorMessage.value = 'データの読み込みに失敗しました。'
    } finally {
        isLoading.value = false
    }
}

// キャンセル処理
const handleCancel = () => {
    router.back()
}

// フォーム送信処理 (PUT /admin/medical-institutions/{id})
const handleSubmit = async () => {
    isSubmitting.value = true
    errorMessage.value = ''
    errors.value = {}

    try {
        const response = await api.put(`/admin/medical-institutions/${institutionId}`, form)
        alert(response.data?.message || '医療機関情報を更新しました。')
        router.back()
    } catch (error) {
        console.error('更新失敗:', error)

        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {}
            errorMessage.value = error.response.data.message || '入力内容をご確認ください。'
        } else {
            errorMessage.value = error.response?.data?.message || '更新に失敗しました。'
        }
    } finally {
        isSubmitting.value = false
    }
}

onMounted(() => {
    loadData()
})
</script>