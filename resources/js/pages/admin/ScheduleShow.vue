<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- 1. ページヘッダー -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-4 gap-4">
                <div class="flex items-center gap-3">
                    <button
                        @click="goBack"
                        type="button"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition-all active:scale-95 shadow-xs"
                        title="戻る"
                    >
                        ←
                    </button>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                            <span>📅</span> スケジュール詳細
                        </h1>
                        <p class="text-xs md:text-sm text-slate-500 mt-0.5">登録されたスケジュール情報と開催日の管理</p>
                    </div>
                </div>

                <!-- アクションボタン（編集 / 削除） -->
                <div v-if="schedule" class="flex items-center gap-2 flex-wrap self-start md:self-auto">
                    <router-link
                        v-if="schedule.update_url || schedule.id"
                        :to="getEditUrl(schedule)"
                        class="inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-slate-700 hover:text-blue-600 bg-white border border-slate-200 hover:border-blue-200 px-4 py-2 rounded-xl shadow-xs transition-all active:scale-95"
                    >
                        <span>✏️</span>
                        <span>編集</span>
                    </router-link>

                    <button
                        type="button"
                        @click="handleDeleteSchedule"
                        class="inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-4 py-2 rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer"
                    >
                        <span>🗑️</span>
                        <span>削除</span>
                    </button>
                </div>
            </div>

            <!-- 2. ローディング表示 -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
                <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm font-medium">スケジュール情報を取得中…</p>
            </div>

            <!-- 3. メインコンテンツ -->
            <div v-else-if="schedule" class="space-y-6">

                <!-- メインカード：基本情報 -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-6">
                    
                    <!-- タイトル & バッジ -->
                    <div class="space-y-3 border-b border-slate-100 pb-5">
                        <div class="flex items-center flex-wrap gap-2 text-xs font-bold">
                            <!-- カテゴリバッジ -->
                            <span v-if="schedule.category?.name" class="bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-lg">
                                📂 {{ schedule.category.name }}
                            </span>
                            <!-- 会場・会議室バッジ -->
                            <span v-if="schedule.room?.name" class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-lg">
                                🏢 {{ schedule.room.name }}
                            </span>
                        </div>

                        <h2 class="text-xl md:text-2xl font-black text-slate-800 leading-snug">
                            {{ schedule.title }}
                        </h2>
                    </div>

                    <!-- 詳細スペック項目 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs md:text-sm">
                        <!-- 補足場所 -->
                        <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-100">
                            <span class="text-xs text-slate-400 font-bold block mb-1">📍 補足場所</span>
                            <span class="font-medium text-slate-700">{{ schedule.location || '設定なし' }}</span>
                        </div>

                        <!-- 外部URL -->
                        <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-100">
                            <span class="text-xs text-slate-400 font-bold block mb-1">🔗 関連URL</span>
                            <a 
                                v-if="schedule.url" 
                                :href="schedule.url" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="text-blue-600 hover:underline font-medium break-all"
                            >
                                {{ schedule.url }}
                            </a>
                            <span v-else class="text-slate-400">設定なし</span>
                        </div>
                    </div>

                    <!-- 繰り返し設定 (Recurrences) -->
                    <div v-if="schedule.recurrences && schedule.recurrences.length > 0" class="bg-amber-50/60 border border-amber-200/80 rounded-xl p-4 space-y-2">
                        <h3 class="text-xs font-bold text-amber-800 flex items-center gap-1.5">
                            <span>🔄</span> 繰り返しルール設定
                        </h3>
                        <ul class="text-xs text-amber-900 space-y-1 font-medium pl-5 list-disc">
                            <li v-for="rec in schedule.recurrences" :key="rec.id">
                                {{ formatRecurrence(rec) }}
                            </li>
                        </ul>
                    </div>

                    <!-- 登録日時 / 更新日時 -->
                    <div class="flex flex-wrap items-center justify-between text-[11px] text-slate-400 font-mono pt-2 border-t border-slate-100 gap-2">
                        <span>作成日: {{ formatDateTime(schedule.created_at) }}</span>
                        <span>最終更新日: {{ formatDateTime(schedule.updated_at) }}</span>
                    </div>
                </div>

                <!-- 開催予定日時一覧 (Occurrences) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <span>🗓️</span> 開催予定日程一覧 
                            <span class="text-xs bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded-full font-mono">
                                {{ schedule.occurrences?.length || 0 }}件
                            </span>
                        </h3>
                    </div>

                    <div v-if="schedule.occurrences && schedule.occurrences.length > 0" class="divide-y divide-slate-100">
                        <div 
                            v-for="occ in schedule.occurrences" 
                            :key="occ.id"
                            class="py-3 flex items-center justify-between hover:bg-slate-50/80 px-2 rounded-xl transition-colors gap-4"
                        >
                            <div class="flex items-center gap-3 text-xs md:text-sm">
                                <span class="font-bold text-slate-700 font-mono">
                                    📅 {{ formatDateRange(occ.start_at, occ.end_at) }}
                                </span>
                                <span 
                                    v-if="occ.type"
                                    class="text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wider"
                                    :class="occ.type === 'generated' ? 'bg-slate-100 text-slate-500' : 'bg-blue-100 text-blue-700'"
                                >
                                    {{ occ.type }}
                                </span>
                            </div>

                            <!-- 日程ごとの個別削除ボタン -->
                            <button
                                v-if="occ.destroy_url"
                                type="button"
                                @click="handleDeleteOccurrence(occ)"
                                class="text-slate-400 hover:text-rose-600 hover:bg-rose-50 p-1.5 rounded-lg transition-all text-xs font-bold"
                                title="この開催日程を取り消す"
                            >
                                ✕ 削除
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-center py-8 text-slate-400 text-xs">
                        登録された開催日程はありません。
                    </div>
                </div>

            </div>

            <!-- 4. エラー / データ未存在表示 -->
            <div v-else class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400">
                <span class="text-3xl block mb-2">⚠️</span>
                <p class="text-sm font-medium">指定されたスケジュールが見つかりませんでした。</p>
                <button
                    @click="goBack"
                    type="button"
                    class="mt-4 px-4 py-2 text-xs font-bold bg-slate-800 hover:bg-slate-900 text-white rounded-xl shadow-xs transition-all"
                >
                    一覧へ戻る
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api.js' // API設定ファイルへの相対パス

const route = useRoute()
const router = useRouter()

const schedule = ref(null)
const loading = ref(true)

// スケジュール詳細の取得
const fetchSchedule = async () => {
    loading.value = true
    try {
        const id = route.params.id
        const res = await api.get(`/admin/schedules/${id}`)
        schedule.value = res.data?.schedule || res.data
    } catch (error) {
        console.error('スケジュールの取得に失敗しました:', error)
    } finally {
        loading.value = false
    }
}

// スケジュールの全体削除
const handleDeleteSchedule = async () => {
    if (!schedule.value) return
    
    if (!confirm(`スケジュール「${schedule.value.title}」を削除してもよろしいですか？\n関連する全日程も削除されます。`)) {
        return
    }

    const deleteUrl = schedule.value.destroy_url || `/admin/schedules/${schedule.value.id}`

    try {
        await api.delete(deleteUrl)
        alert('スケジュールを削除しました。')
        router.push('/admin/schedules')
    } catch (error) {
        console.error('スケジュールの削除に失敗しました:', error)
        alert('スケジュールの削除に失敗しました。')
    }
}

// 開催日程（Occurrence）の個別に削除
const handleDeleteOccurrence = async (occ) => {
    const formattedDate = formatDateRange(occ.start_at, occ.end_at)
    if (!confirm(`「${formattedDate}」の日程を削除してもよろしいですか？`)) {
        return
    }

    const deleteUrl = occ.destroy_url || `/admin/schedule-occurrences/${occ.id}`

    try {
        await api.delete(deleteUrl)
        // ローカルの occurrences 配列から除外して即時反映
        schedule.value.occurrences = schedule.value.occurrences.filter(o => o.id !== occ.id)
    } catch (error) {
        console.error('日程の削除に失敗しました:', error)
        alert('日程の削除に失敗しました。')
    }
}

// 編集ページURL生成
const getEditUrl = (sched) => {
    return `/admin/schedules/${sched.id}/edit`
}

// 戻るボタン
const goBack = () => {
    router.back()
}

// 日時フォーマット (YYYY-MM-DD HH:mm)
const formatDateTime = (dateStr) => {
    if (!dateStr) return ''
    const d = new Date(dateStr)
    if (isNaN(d.getTime())) return dateStr

    // UTC時間から日本時間(JST: UTC+9)を正確に生成
    const jst = new Date(d.getTime() + 9 * 60 * 60 * 1000)

    const yyyy = jst.getUTCFullYear()
    const mm = String(jst.getUTCMonth() + 1).padStart(2, '0')
    const dd = String(jst.getUTCDate()).padStart(2, '0')
    const hh = String(jst.getUTCHours()).padStart(2, '0')
    const min = String(jst.getUTCMinutes()).padStart(2, '0')

    return `${yyyy}-${mm}-${dd} ${hh}:${min}`
}

// 日時範囲フォーマット (例: 2026-01-06(火) 20:00 〜 21:00)
const formatDateRange = (startStr, endStr) => {
    if (!startStr) return ''

    // 日時文字列を分解してJST(+9時間)の日時オブジェクトを生成する内部関数
    const parseJST = (str) => {
        if (!str) return null
        const d = new Date(str)
        if (isNaN(d.getTime())) return null

        // タイムゾーンの解釈ズレを防ぐため、UTCミリ秒に9時間(+9 * 3600sec)を加算
        const jst = new Date(d.getTime() + 9 * 60 * 60 * 1000)

        const dayNames = ['日', '月', '火', '水', '木', '金', '土']
        return {
            yyyy: jst.getUTCFullYear(),
            mm: String(jst.getUTCMonth() + 1).padStart(2, '0'),
            dd: String(jst.getUTCDate()).padStart(2, '0'),
            hh: String(jst.getUTCHours()).padStart(2, '0'),
            min: String(jst.getUTCMinutes()).padStart(2, '0'),
            day: dayNames[jst.getUTCDay()],
            rawDate: `${jst.getUTCFullYear()}-${jst.getUTCMonth() + 1}-${jst.getUTCDate()}`
        }
    }

    const start = parseJST(startStr)
    const end = parseJST(endStr)

    if (!start) return startStr

    let result = `${start.yyyy}-${start.mm}-${start.dd}(${start.day}) ${start.hh}:${start.min}`

    if (end) {
        // 同日かどうかの判定
        if (start.rawDate === end.rawDate) {
            result += ` 〜 ${end.hh}:${end.min}`
        } else {
            result += ` 〜 ${end.mm}-${end.dd}(${end.day}) ${end.hh}:${end.min}`
        }
    }

    return result
}
// 繰り返しルールのテキスト変換
const formatRecurrence = (rec) => {
    const freqMap = {
        daily: '毎日',
        weekly: '毎週',
        monthly: '毎月',
        yearly: '毎年'
    }

    const dayMap = {
        MO: '月曜日', TU: '火曜日', WE: '水曜日', TH: '木曜日', FR: '金曜日', SA: '土曜日', SU: '日曜日'
    }

    let text = freqMap[rec.frequency] || rec.frequency

    if (rec.interval && rec.interval > 1) {
        text = `${rec.interval}${rec.frequency === 'monthly' ? 'ヶ月' : '日'}ごと`
    }

    if (rec.bysetpos) {
        text += ` 第${rec.bysetpos}`
    }

    if (rec.byweekday && rec.byweekday.length > 0) {
        const days = rec.byweekday.map(d => dayMap[d] || d).join('・')
        text += ` ${days}`
    }

    return text
}

onMounted(() => {
    fetchSchedule()
})
</script>