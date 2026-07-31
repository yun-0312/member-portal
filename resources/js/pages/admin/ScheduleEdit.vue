<template>
    <div class="max-w-3xl mx-auto p-4 md:p-6 space-y-6">
        <!-- ヘッダー -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">スケジュール編集</h1>
                <p class="text-xs md:text-sm text-gray-500 mt-1">登録済みのスケジュール情報変更</p>
            </div>
            <router-link
                :to="indexUrl"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold text-xs md:text-sm rounded-xl shadow-2xs transition-all active:scale-95"
            >
                <span>← 一覧に戻る</span>
            </router-link>
        </div>

        <!-- ローディング状態 -->
        <div v-if="loading" class="bg-white rounded-2xl border border-gray-200 p-12 text-center text-gray-500">
            <div class="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
            <p class="text-sm font-bold">データを読み込み中…</p>
        </div>

        <!-- フォーム本体 -->
        <form v-else @submit.prevent="submitForm" class="bg-white rounded-2xl border border-gray-200 shadow-2xs p-6 md:p-8 space-y-6">

            <!-- 1. 基本情報 -->
            <div class="space-y-4">
                <h2 class="text-base font-bold text-gray-800 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <span>📌</span> 基本情報
                </h2>

                <!-- タイトル -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        タイトル <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        required
                        placeholder="例: 総務庶務委員会"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"
                        :class="{ 'border-rose-500 bg-rose-50/30': errors.title }"
                    />
                    <p v-if="errors.title" class="text-xs text-rose-600 mt-1">{{ errors.title[0] }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- スケジュールカテゴリ -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            カテゴリ <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.schedule_category_id"
                            required
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                            :class="{ 'border-rose-500 bg-rose-50/30': errors.schedule_category_id }"
                        >
                            <option :value="null" disabled>選択してください</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                        <p v-if="errors.schedule_category_id" class="text-xs text-rose-600 mt-1">{{ errors.schedule_category_id[0] }}</p>
                    </div>

                    <!-- 会議室（Room） -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">会議室</label>
                        <select
                            v-model="form.room_id"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                            :class="{ 'border-rose-500 bg-rose-50/30': errors.room_id }"
                        >
                            <option :value="null">指定なし</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">
                                {{ room.name }}
                            </option>
                        </select>
                        <p v-if="errors.room_id" class="text-xs text-rose-600 mt-1">{{ errors.room_id[0] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- 場所（テキスト） -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">場所（任意）</label>
                        <input
                            v-model="form.location"
                            type="text"
                            placeholder="例: オンライン、または補足場所"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"
                            :class="{ 'border-rose-500 bg-rose-50/30': errors.location }"
                        />
                        <p v-if="errors.location" class="text-xs text-rose-600 mt-1">{{ errors.location[0] }}</p>
                    </div>

                    <!-- 関連URL -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">関連URL（任意）</label>
                        <input
                            v-model="form.url"
                            type="url"
                            placeholder="https://example.com"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"
                            :class="{ 'border-rose-500 bg-rose-50/30': errors.url }"
                        />
                        <p v-if="errors.url" class="text-xs text-rose-600 mt-1">{{ errors.url[0] }}</p>
                    </div>
                </div>
            </div>

            <!-- 2. スケジュールタイプ選択（単発 or 定期繰り返し） -->
            <div class="space-y-4 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                        <span>🕒</span> 日時・定期設定
                    </h2>

                    <!-- 切り替えスイッチ -->
                    <div class="inline-flex p-1 bg-gray-100 rounded-xl">
                        <button
                            type="button"
                            @click="isRecurring = false"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all cursor-pointer"
                            :class="!isRecurring ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-500 hover:text-gray-700'"
                        >
                            単発予定
                        </button>
                        <button
                            type="button"
                            @click="isRecurring = true"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all cursor-pointer"
                            :class="isRecurring ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-500 hover:text-gray-700'"
                        >
                            🔄 繰り返し（定期）
                        </button>
                    </div>
                </div>

                <!-- 【A】単発予定の場合の入力フォーム -->
                <div v-if="!isRecurring" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50/50 p-4 rounded-xl border border-gray-200">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            開始日時 <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.start_at"
                            type="datetime-local"
                            required
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                            :class="{ 'border-rose-500 bg-rose-50/30': errors.start_at }"
                        />
                        <p v-if="errors.start_at" class="text-xs text-rose-600 mt-1">{{ errors.start_at[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            終了日時 <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.end_at"
                            type="datetime-local"
                            required
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                            :class="{ 'border-rose-500 bg-rose-50/30': errors.end_at }"
                        />
                        <p v-if="errors.end_at" class="text-xs text-rose-600 mt-1">{{ errors.end_at[0] }}</p>
                    </div>
                </div>

                <!-- 【B】繰り返し（定期）予定の場合の入力フォーム -->
                <div v-else class="space-y-4 bg-blue-50/40 p-4 md:p-5 rounded-xl border border-blue-100">

                    <!-- 変更範囲の選択 -->
                    <div class="p-3 bg-white rounded-xl border border-blue-200">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">
                            変更の適用範囲 <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex flex-wrap gap-4 text-xs font-bold">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" v-model="updateMode" value="future" class="text-blue-600 focus:ring-blue-500" />
                                <span>これ以降の予定を変更 </span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" v-model="updateMode" value="all" class="text-blue-600 focus:ring-blue-500" />
                                <span>すべての予定を変更</span>
                            </label>
                        </div>
                    </div>

                    <!-- 繰り返し開始日 & 時間帯（開始/終了時刻） -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-3 border-b border-blue-100">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                繰り返し開始日 <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.recurrence.dtstart"
                                type="date"
                                required
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                                :class="{ 'border-rose-500 bg-rose-50/30': errors['recurrence.dtstart'] }"
                            />
                            <p v-if="errors['recurrence.dtstart']" class="text-xs text-rose-600 mt-1">
                                {{ errors['recurrence.dtstart'][0] }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                開始時刻 <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.recurrence.start_time"
                                type="time"
                                required
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                                :class="{ 'border-rose-500 bg-rose-50/30': errors['recurrence.start_time'] }"
                            />
                            <p v-if="errors['recurrence.start_time']" class="text-xs text-rose-600 mt-1">
                                {{ errors['recurrence.start_time'][0] }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                終了時刻 <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.recurrence.end_time"
                                type="time"
                                required
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                                :class="{ 'border-rose-500 bg-rose-50/30': errors['recurrence.end_time'] }"
                            />
                            <p v-if="errors['recurrence.end_time']" class="text-xs text-rose-600 mt-1">
                                {{ errors['recurrence.end_time'][0] }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- 頻度 (frequency) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                頻度 <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.recurrence.frequency"
                                @change="handleFrequencyChange"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                                :class="{ 'border-rose-500 bg-rose-50/30': errors['recurrence.frequency'] }"
                            >
                                <option value="daily">毎日 (daily)</option>
                                <option value="weekly">毎週 (weekly)</option>
                                <option value="monthly">毎月 (monthly)</option>
                                <option value="yearly">毎年 (yearly)</option>
                            </select>
                            <p v-if="errors['recurrence.frequency']" class="text-xs text-rose-600 mt-1">
                                {{ errors['recurrence.frequency'][0] }}
                            </p>
                        </div>

                        <!-- 間隔 (interval) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                繰り返す間隔 <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    v-model.number="form.recurrence.interval"
                                    type="number"
                                    min="1"
                                    required
                                    class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                                    :class="{ 'border-rose-500 bg-rose-50/30': errors['recurrence.interval'] }"
                                />
                                <span class="text-xs text-gray-600 font-bold whitespace-nowrap">
                                    {{ frequencyUnitText }}ごとに繰り返す
                                </span>
                            </div>
                            <p v-if="errors['recurrence.interval']" class="text-xs text-rose-600 mt-1">
                                {{ errors['recurrence.interval'][0] }}
                            </p>
                        </div>

                        <!-- 第N週目 (bysetpos) ★ monthly のみ表示 -->
                        <div v-if="form.recurrence.frequency === 'monthly'">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                毎月の週位置 <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model.number="form.recurrence.bysetpos"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                                :class="{ 'border-rose-500 bg-rose-50/30': errors['recurrence.bysetpos'] }"
                            >
                                <option :value="1">第 1 週目</option>
                                <option :value="2">第 2 週目</option>
                                <option :value="3">第 3 週目</option>
                                <option :value="4">第 4 週目</option>
                                <option :value="-1">最終週</option>
                            </select>
                            <p v-if="errors['recurrence.bysetpos']" class="text-xs text-rose-600 mt-1">
                                {{ errors['recurrence.bysetpos'][0] }}
                            </p>
                        </div>
                    </div>

                    <!-- 曜日選択 (byweekday) ★ daily, yearly 以外で表示 -->
                    <div v-if="form.recurrence.frequency !== 'daily' && form.recurrence.frequency !== 'yearly'">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">
                            曜日 {{ form.recurrence.frequency === 'monthly' ? '（1つ選択）' : '' }} <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-for="day in weekDaysOptions"
                                :key="day.value"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold cursor-pointer transition-all select-none"
                                :class="form.recurrence.byweekday.includes(day.value)
                                    ? 'bg-blue-600 text-white border-blue-600 shadow-2xs'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                            >
                                <input
                                    type="checkbox"
                                    :value="day.value"
                                    :checked="form.recurrence.byweekday.includes(day.value)"
                                    @change="toggleWeekday(day.value)"
                                    class="sr-only"
                                />
                                <span>{{ day.label }}</span>
                            </label>
                        </div>
                        <p v-if="errors['recurrence.byweekday']" class="text-xs text-rose-600 mt-1">
                            {{ errors['recurrence.byweekday'][0] }}
                        </p>
                    </div>

                    <!-- 終了日 (until) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">繰り返し終了日</label>
                        <input
                            v-model="form.recurrence.until"
                            type="date"
                            class="w-full md:w-1/2 px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white"
                            :class="{ 'border-rose-500 bg-rose-50/30': errors['recurrence.until'] }"
                        />
                        <p v-if="errors['recurrence.until']" class="text-xs text-rose-600 mt-1">
                            {{ errors['recurrence.until'][0] }}
                        </p>
                        <p v-else class="text-2xs text-gray-500 mt-1">未指定の場合は継続的に発生します。</p>
                    </div>
                </div>
            </div>

            <!-- 送信ボタンエリア -->
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <router-link
                    :to="indexUrl"
                    class="px-5 py-2.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold text-xs md:text-sm rounded-xl transition-all"
                >
                    キャンセル
                </router-link>
                <button
                    type="submit"
                    :disabled="submitting"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs md:text-sm rounded-xl shadow-2xs transition-all active:scale-95 disabled:opacity-50 cursor-pointer"
                >
                    <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ submitting ? '更新中…' : '更新する' }}</span>
                </button>
            </div>

        </form>

        <!-- スキップ通知用モーダル -->
        <div v-if="showSkippedModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
                <div class="flex items-center gap-3 text-amber-600">
                    <span class="text-2xl">⚠️</span>
                    <h3 class="font-bold text-lg text-gray-900">一部の予定がスキップされました</h3>
                </div>
                <p class="text-sm text-gray-600">
                    会議室の予定重複のため、以下の日時の予定は更新されませんでした。
                </p>
                <div class="max-h-48 overflow-y-auto bg-gray-50 p-3 rounded-xl border border-gray-200 text-xs font-mono text-gray-700 space-y-1">
                    <div v-for="(date, idx) in skippedDates" :key="idx" class="flex items-center gap-2">
                        <span class="text-rose-500">•</span>
                        <span>{{ date }}</span>
                    </div>
                </div>
                <div class="pt-2 flex justify-end">
                    <button
                        type="button"
                        @click="closeSkippedModal"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs md:text-sm rounded-xl transition-all cursor-pointer"
                    >
                        確認して完了
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const props = defineProps({
    indexUrl: {
        type: String,
        default: '/admin/schedules',
    },
})

const router = useRouter()
const route = useRoute()
const currentOccurrenceId = ref(route.params.id)

// モード選択（false: 単発, true: 定期繰り返し）
const isRecurring = ref(false)
const loading = ref(true)
const submitting = ref(false)
const errors = ref({})

const targetScheduleId = ref(null)

// モード選択（単発時は 'single'、繰り返し時は 'future' または 'all' を指定）
const updateMode = ref('all') // 'single' | 'future' | 'all'

// マスターデータ用リスト
const categories = ref([])
const rooms = ref([])

const skippedDates = ref([])
const showSkippedModal = ref(false)

const closeSkippedModal = () => {
    showSkippedModal.value = false
    router.push(props.indexUrl)
}

// 曜日選択のオプション
const weekDaysOptions = [
    { label: '月 (MO)', value: 'MO' },
    { label: '火 (TU)', value: 'TU' },
    { label: '水 (WE)', value: 'WE' },
    { label: '木 (TH)', value: 'TH' },
    { label: '金 (FR)', value: 'FR' },
    { label: '土 (SA)', value: 'SA' },
    { label: '日 (SU)', value: 'SU' },
]

// フォームデータ
const form = reactive({
    title: '',
    schedule_category_id: null,
    room_id: null,
    location: '',
    url: '',
    // 単発用
    start_at: '',
    end_at: '',
    // 繰り返し用
    recurrence: {
        dtstart: '',     // 繰り返し開始日 (YYYY-MM-DD)
        start_time: '',  // 開始時刻 (HH:mm)
        end_time: '',    // 終了時刻 (HH:mm)
        frequency: 'monthly',
        byweekday: ['TU'],
        bysetpos: 3,
        interval: 1,
        until: null,
    },
})

// 間隔のラベル表示用算出プロパティ
const frequencyUnitText = computed(() => {
    switch (form.recurrence.frequency) {
        case 'daily': return '日'
        case 'weekly': return '週'
        case 'monthly': return 'ヶ月'
        case 'yearly': return '年'
        default: return '回'
    }
})

// 頻度変更時の初期化処理
const handleFrequencyChange = () => {
    const freq = form.recurrence.frequency

    if (freq === 'daily' || freq === 'yearly') {
        form.recurrence.bysetpos = null
        form.recurrence.byweekday = null
    } else if (freq === 'weekly') {
        form.recurrence.bysetpos = null
        if (!Array.isArray(form.recurrence.byweekday) || form.recurrence.byweekday.length === 0) {
            form.recurrence.byweekday = ['MO']
        }
    } else if (freq === 'monthly') {
        form.recurrence.bysetpos = form.recurrence.bysetpos || 3
        if (!Array.isArray(form.recurrence.byweekday) || form.recurrence.byweekday.length === 0) {
            form.recurrence.byweekday = ['TU']
        } else if (form.recurrence.byweekday.length > 1) {
            form.recurrence.byweekday = [form.recurrence.byweekday[0]]
        }
    }
}

// 曜日トグル処理
const toggleWeekday = (val) => {
    const freq = form.recurrence.frequency

    if (freq === 'monthly') {
        form.recurrence.byweekday = [val]
    } else {
        if (!Array.isArray(form.recurrence.byweekday)) {
            form.recurrence.byweekday = []
        }
        const idx = form.recurrence.byweekday.indexOf(val)
        if (idx > -1) {
            form.recurrence.byweekday.splice(idx, 1)
        } else {
            form.recurrence.byweekday.push(val)
        }
    }
}

// datetime-local 用 (YYYY-MM-DDTHH:mm)
const formatDateTimeLocal = (dateStr) => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return ''

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')

    return `${year}-${month}-${day}T${hours}:${minutes}`
}

// date 用 (YYYY-MM-DD)
const formatDateOnly = (dateStr) => {
    if (!dateStr) return null
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return null

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
}

// time 用 (HH:mm)
const formatTimeOnly = (dateStr) => {
    if (!dateStr) return '00:00'
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return '00:00'

    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')

    return `${hours}:${minutes}`
}

// 初期データおよび編集対象スケジュールの取得
onMounted(async () => {
    loading.value = true
    try {
        const [categoriesRes, roomsRes, scheduleRes] = await Promise.all([
            axios.get('/api/admin/schedule-categories', { withCredentials: true }),
            axios.get('/api/admin/rooms', { withCredentials: true }),
            axios.get(`/api/admin/schedule-occurrences/${currentOccurrenceId.value}`, { withCredentials: true }),
        ])

        categories.value = Array.isArray(categoriesRes.data) ? categoriesRes.data : categoriesRes.data.data || []
        rooms.value = Array.isArray(roomsRes.data) ? roomsRes.data : roomsRes.data.data || []

        const resData = scheduleRes.data.data || scheduleRes.data
        const schedule = resData.schedule || resData
        const occurrence = resData.occurrence || null

        targetScheduleId.value = schedule.id

        if (occurrence && occurrence.id) {
            currentOccurrenceId.value = occurrence.id
        }

        // 基本情報のセット
        form.title = schedule.title || ''
        form.schedule_category_id = schedule.schedule_category_id || null
        form.room_id = schedule.room_id || null
        form.location = schedule.location || ''
        form.url = schedule.url || ''

        // 単発用の開始・終了日時を取得して保持
        const startAt = occurrence?.start_at || schedule.start_at
        const endAt = occurrence?.end_at || schedule.end_at
        form.start_at = formatDateTimeLocal(startAt)
        form.end_at = formatDateTimeLocal(endAt)

        // recurrences の判定
        const recurrences = schedule.recurrences || []
        const rawRecurrence = Array.isArray(recurrences) && recurrences.length > 0 ? recurrences[0] : null

        if (rawRecurrence) {
            isRecurring.value = true

            let byweekdayArr = rawRecurrence.byweekday || []
            if (typeof byweekdayArr === 'string') {
                byweekdayArr = byweekdayArr.split(',')
            }

            form.recurrence = {
                dtstart: formatDateOnly(rawRecurrence.start_after || startAt),
                start_time: formatTimeOnly(startAt),
                end_time: formatTimeOnly(endAt),
                frequency: rawRecurrence.frequency || 'monthly',
                byweekday: Array.isArray(byweekdayArr) ? byweekdayArr : null,
                bysetpos: rawRecurrence.bysetpos !== undefined && rawRecurrence.bysetpos !== null ? Number(rawRecurrence.bysetpos) : null,
                interval: Number(rawRecurrence.interval ?? 1),
                until: formatDateOnly(rawRecurrence.until),
            }
        } else {
            isRecurring.value = false
        }

    } catch (err) {
        console.error('データの取得に失敗しました:', err)
        if (err.response && err.response.status === 404) {
            alert('指定された予定はすでに削除されているか、存在しません。')
            router.push(props.indexUrl)
        } else {
            alert('スケジュールの読み込みに失敗しました。')
        }
    } finally {
        loading.value = false
    }
})

// 更新処理
const submitForm = async () => {
    submitting.value = true
    errors.value = {}

    // 1. スケジュール本体（Schedule）更新用のデータ
    const schedulePayload = {
        title: form.title,
        schedule_category_id: form.schedule_category_id,
        room_id: form.room_id || null,
        location: form.location || null,
        url: form.url || null,
    }

    // 2. 発生回（Occurrence / Recurrence）更新用のデータ
    const mode = isRecurring.value ? updateMode.value : 'single'
    const occurrencePayload = {
        mode: mode,
    }

    if (mode === 'single') {
        occurrencePayload.start_at = form.start_at
        occurrencePayload.end_at = form.end_at
    } else {
        const freq = form.recurrence.frequency

        occurrencePayload.recurrence = {
            dtstart: form.recurrence.dtstart,
            start_time: form.recurrence.start_time,
            end_time: form.recurrence.end_time,
            frequency: freq,
            interval: form.recurrence.interval || 1,
            until: form.recurrence.until || null,
            byweekday: (freq !== 'daily' && freq !== 'yearly') ? form.recurrence.byweekday : null,
            bysetpos: freq === 'monthly' ? form.recurrence.bysetpos : null,
        }
    }

    try {
        // ① スケジュール本体の更新
        await axios.put(`/api/admin/schedules/${targetScheduleId.value}`, schedulePayload, {
            withCredentials: true,
            headers: { 'Accept': 'application/json' },
        })

        // 💡 修正3: 動的に保持している currentOccurrenceId.value を使う
        const res = await axios.put(`/api/admin/schedule-occurrences/${currentOccurrenceId.value}`, occurrencePayload, {
            withCredentials: true,
            headers: { 'Accept': 'application/json' },
        })

        // スキップされた予定のチェック
        if (res.data.skipped && res.data.skipped.length > 0) {
            skippedDates.value = res.data.skipped
            showSkippedModal.value = true
        } else {
            // 💡 一覧画面へ遷移
            router.push(props.indexUrl)
        }
    } catch (err) {
        if (err.response && err.response.status === 422) {
            const resData = err.response.data
            if (resData.message) {
                alert(resData.message)
            }
            errors.value = resData.errors || {}
        } else if (err.response && err.response.status === 404) {
            // 💡 万が一対象Occurrenceが消えていた場合は、スケジュール全体更新のみ成功として一覧へ遷移
            alert('対象の予定情報が変更されたため、一覧画面へ戻ります。')
            router.push(props.indexUrl)
        } else {
            console.error('更新エラー:', err)
            alert('更新処理に失敗しました。')
        }
    } finally {
        submitting.value = false
    }
}
</script>