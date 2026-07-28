<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-7xl mx-auto space-y-6">

        <!-- 1. ページヘッダー（新規登録ボタン） -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-4 gap-4">
            <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <span>🏛️</span> スケジュール管理
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">会議室ごとの予定確認および登録・編集</p>
            </div>

            <div class="flex items-center gap-3 self-start md:self-auto">
            <!-- ダッシュボード戻るボタン -->
            <router-link
                to="/admin/dashboard"
                class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-xs transition-all active:scale-95"
            >
                <span>← ダッシュボード</span>
            </router-link>


            </div>
        </div>

        <!-- 2. 月移動ナビゲーション -->
        <div v-if="scheduleData" class="bg-white p-4 shadow-xs border border-slate-200/80 space-y-3 rounded-xl">
            <div class="flex items-center justify-between">
            <button
                @click="changeMonth(scheduleData.month_links.prev)"
                class="inline-flex items-center gap-1 text-xs md:text-sm font-bold text-slate-600 hover:text-blue-600 bg-slate-100 hover:bg-blue-50 px-3 py-2 transition-colors rounded-lg cursor-pointer"
            >
                <span>←</span>
                <span>{{ formatYearMonth(scheduleData.month_links.prev) }}</span>
            </button>

            <h2 class="text-lg md:text-xl font-extrabold text-slate-800 font-mono">
                {{ formatYearMonth(scheduleData.month_links.current) }}
            </h2>

            <button
                @click="changeMonth(scheduleData.month_links.next)"
                class="inline-flex items-center gap-1 text-xs md:text-sm font-bold text-slate-600 hover:text-blue-600 bg-slate-100 hover:bg-blue-50 px-3 py-2 transition-colors rounded-lg cursor-pointer"
            >
                <span>{{ formatYearMonth(scheduleData.month_links.next) }}</span>
                <span>→</span>
            </button>
            </div>

            <div v-if="scheduleData.year_links?.length" class="flex items-center gap-1.5 overflow-x-auto pb-1 pt-2 border-t border-slate-100">
            <button
                v-for="ym in scheduleData.year_links"
                :key="ym"
                @click="changeMonth(ym)"
                :class="[
                'px-2.5 py-1 text-xs font-bold transition-all shrink-0 font-mono rounded-md cursor-pointer',
                ym === scheduleData.month_links.current
                    ? 'bg-blue-600 text-white shadow-xs'
                    : 'bg-slate-50 text-slate-600 hover:bg-slate-200 border border-slate-200/60'
                ]"
            >
                {{ formatMonthOnly(ym) }}
            </button>
            </div>
        </div>

        <!-- 3. ローディング表示 -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
            <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-medium">スケジュールを読み込み中…</p>
        </div>

        <!-- 4. メインコンテンツ（カレンダー表） -->
        <div v-else-if="allDatesInMonth.length" class="bg-white shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left min-w-[700px]">
                <thead>
                <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 text-xs font-bold">
                    <th class="p-3 w-32 border-r border-slate-200/80 sticky left-0 bg-slate-100 z-10">
                    日付 / 会議室
                    </th>
                    <th
                    v-for="room in rooms"
                    :key="room.id"
                    class="p-3 border-r border-slate-200/80 last:border-r-0 text-center min-w-[180px]"
                    >
                    <div class="flex items-center justify-center gap-1">
                        <span>📍</span>
                        <span>{{ room.name }}</span>
                    </div>
                    </th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-200/80 text-xs">
                <tr
                    v-for="dateInfo in allDatesInMonth"
                    :key="dateInfo.date"
                    :class="[
                    'transition-colors',
                    (dateInfo.isHoliday || dateInfo.dayOfWeekIdx === 0) ? 'bg-red-100/70 hover:bg-red-100' :
                    dateInfo.dayOfWeekIdx === 6 ? 'bg-blue-100/70 hover:bg-blue-100' :
                    'hover:bg-slate-50'
                    ]"
                >
                    <!-- ★ 修正箇所：日付セルをクリック可能にし、新規登録関数を登録 -->
                    <td
                    class="p-3 border-r border-slate-200/80 sticky left-0 font-mono font-bold align-top z-10 cursor-pointer hover:bg-blue-200/60 transition-colors group/date"
                    :class="[
                        (dateInfo.isHoliday || dateInfo.dayOfWeekIdx === 0) ? 'bg-red-100' :
                        dateInfo.dayOfWeekIdx === 6 ? 'bg-blue-100' : 'bg-white'
                    ]"
                    @click="openCreateFormByDate(dateInfo.date)"
                    title="クリックしてこの日付で予定を登録"
                    >
                    <div class="flex flex-col">
                        <div class="flex items-center justify-between">
                        <span class="text-slate-800 text-sm group-hover/date:text-blue-700 transition-colors">
                            {{ dateInfo.formattedDate }}
                        </span>
                        <!-- ホバー時に「＋」アイコンを表示 -->
                        <span class="opacity-0 group-hover/date:opacity-100 text-blue-600 font-extrabold text-xs transition-opacity">
                            ＋
                        </span>
                        </div>

                        <div class="flex items-center gap-1 flex-wrap mt-0.5">
                        <span
                            :class="[
                            'text-xs font-extrabold',
                            (dateInfo.isHoliday || dateInfo.dayOfWeekIdx === 0) ? 'text-red-700' :
                            dateInfo.dayOfWeekIdx === 6 ? 'text-blue-700' : 'text-slate-400'
                            ]"
                        >
                            ({{ dateInfo.dayOfWeek }})
                        </span>
                        <span
                            v-if="dateInfo.isHoliday"
                            class="inline-block bg-red-600 text-white text-[10px] font-bold px-1 py-0.2 rounded"
                        >
                            {{ dateInfo.holidayName }}
                        </span>
                        </div>
                    </div>
                    </td>

                    <td
                    v-for="room in rooms"
                    :key="room.id"
                    class="p-2 border-r border-slate-200/80 last:border-r-0 align-top"
                    >
                    <div
                        v-if="getEventsByDateAndRoom(dateInfo.date, room.id).length"
                        class="space-y-1.5"
                    >
                        <!-- 各イベントカード（詳細・編集画面へのリンク） -->
                        <router-link
                        v-for="item in getEventsByDateAndRoom(dateInfo.date, room.id)"
                        :key="item.id"
                        :to="item.show_url"
                        class="block bg-white p-2.5 border shadow-2xs hover:shadow-md transition-all space-y-1 hover:border-blue-400 group rounded-xs"
                        :class="getCategoryBorderClass(item.schedule?.category?.slug)"
                        >
                        <div class="flex items-center justify-between gap-1 flex-wrap">
                            <span
                            :class="[
                                'px-1.5 py-0.2 rounded text-[10px] font-bold border',
                                getCategoryBadgeClass(item.schedule?.category?.slug)
                            ]"
                            >
                            {{ item.schedule?.category?.name || '行事' }}
                            </span>

                            <span class="font-mono text-[10px] font-bold text-slate-500">
                            {{ formatTime(item.start_at) }}~{{ formatTime(item.end_at) }}
                            </span>
                        </div>

                        <p class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors leading-tight text-xs">
                            {{ item.schedule?.title }}
                        </p>
                        </router-link>
                    </div>

                    <div v-else class="h-full min-h-[36px] flex items-center justify-center text-slate-300 opacity-60">
                        -
                    </div>
                    </td>
                </tr>
                </tbody>
            </table>
            </div>
        </div>

        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import holidayJp from '@holiday-jp/holiday_jp'
import api from '../../api.js'

const route = useRoute()
const router = useRouter()

const scheduleData = ref(null)
const loading = ref(true)

// スケジュールデータ取得
const fetchSchedules = async (month = null) => {
    loading.value = true
    try {
        const params = {}
        if (month) params.month = month

        const res = await api.get('/admin/schedules', { params })
        scheduleData.value = res.data
    } catch (error) {
        console.error('スケジュールの取得に失敗しました:', error)
    } finally {
        loading.value = false
    }
}

// store_url からフロント側のルートを取得するヘルパー
const getCreateRoute = (storeUrl) => {
    return '/admin/schedules/create'
}

// ★ 修正箇所：日付セルクリック時に新規作成画面へ遷移する関数
const openCreateFormByDate = (dateStr) => {
    router.push({
        path: '/admin/schedules/create',
        query: {
        date: dateStr // 例: ?date=2026-07-28
        }
    })
}

const changeMonth = (monthStr) => {
    if (!monthStr) return
    router.push({
        query: {
        ...route.query,
        month: monthStr
        }
    })
}

const OTHER_ROOM_ID = 'other_unassigned'

const rooms = computed(() => {
    if (!scheduleData.value?.calendar) return []

    const monthKey = scheduleData.value.month_links?.current
    const calendarObj = scheduleData.value.calendar[monthKey] || {}

    const roomMap = new Map()

    Object.values(calendarObj).forEach(events => {
        events.forEach(event => {
        const room = event.schedule?.room
        if (room && room.id && !roomMap.has(room.id)) {
            roomMap.set(room.id, room)
        }
        })
    })

    const sortedRooms = Array.from(roomMap.values()).sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0))

    sortedRooms.push({
        id: OTHER_ROOM_ID,
        name: 'その他',
        sort_order: 9999
    })

    return sortedRooms
})

const allDatesInMonth = computed(() => {
    if (!scheduleData.value?.month_links?.current) return []

    const monthStr = scheduleData.value.month_links.current
    const [year, month] = monthStr.split('-').map(Number)

    const daysInMonth = new Date(year, month, 0).getDate()
    const dayOfWeekNames = ['日', '月', '火', '水', '木', '金', '土']

    const dates = []

    for (let day = 1; day <= daysInMonth; day++) {
        const formattedDay = String(day).padStart(2, '0')
        const formattedMonth = String(month).padStart(2, '0')
        const dateStr = `${year}-${formattedMonth}-${formattedDay}`

        const dateObj = new Date(year, month - 1, day)
        const dayOfWeekIdx = dateObj.getDay()

        const holidays = holidayJp.between(dateObj, dateObj)
        const isHoliday = holidays.length > 0
        const holidayName = isHoliday ? holidays[0].name : ''

        dates.push({
        date: dateStr,
        formattedDate: `${formattedMonth}/${formattedDay}`,
        dayOfWeek: dayOfWeekNames[dayOfWeekIdx],
        dayOfWeekIdx: dayOfWeekIdx,
        isHoliday: isHoliday,
        holidayName: holidayName
        })
    }

    return dates
})

const getEventsByDateAndRoom = (dateStr, roomId) => {
    if (!scheduleData.value?.calendar) return []
    const monthKey = scheduleData.value.month_links?.current
    const events = scheduleData.value.calendar[monthKey]?.[dateStr] || []

    if (roomId === OTHER_ROOM_ID) {
        return events.filter(e => !e.schedule?.room_id && !e.schedule?.room)
    }

    return events.filter(e => e.schedule?.room_id === roomId || e.schedule?.room?.id === roomId)
}

const formatYearMonth = (ymStr) => {
    if (!ymStr) return ''
    const [y, m] = ymStr.split('-')
    return `${y}年${parseInt(m, 10)}月`
}

const formatMonthOnly = (ymStr) => {
    if (!ymStr) return ''
    const [, m] = ymStr.split('-')
    return `${parseInt(m, 10)}月`
}

// 時刻フォーマット (例: 11:00 UTC -> 20:00 JST)
const formatTime = (isoString) => {
    if (!isoString) return ''
    const d = new Date(isoString)
    if (isNaN(d.getTime())) return isoString

    // UTCミリ秒に9時間を加算してJST時刻を計算
    const jst = new Date(d.getTime() + 9 * 60 * 60 * 1000)

    const hh = String(jst.getUTCHours()).padStart(2, '0')
    const min = String(jst.getUTCMinutes()).padStart(2, '0')

    return `${hh}:${min}`
}

const getCategoryBorderClass = (slug) => {
    switch (slug) {
        case 'important': return 'border-rose-200 bg-rose-50/30'
        case 'lecture': return 'border-amber-200 bg-amber-50/30'
        case 'committee': return 'border-blue-200 bg-blue-50/30'
        default: return 'border-slate-200'
    }
}

const getCategoryBadgeClass = (slug) => {
    switch (slug) {
        case 'important': return 'bg-rose-100 text-rose-700 border-rose-200'
        case 'lecture': return 'bg-amber-100 text-amber-700 border-amber-200'
        case 'committee': return 'bg-blue-100 text-blue-700 border-blue-200'
        default: return 'bg-slate-100 text-slate-700 border-slate-200'
    }
}

watch(
    () => route.query.month,
    (newMonth) => { fetchSchedules(newMonth) }
    )

onMounted(() => {
    fetchSchedules(route.query.month)
})
</script>