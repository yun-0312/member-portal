    <template>
    <div class="min-h-screen bg-slate-50 text-slate-800 p-6 md:p-10">
        <div class="max-w-7xl mx-auto space-y-8">

        <!-- ページヘッダータイトル -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <span>📊</span> ダッシュボード
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">会員向け情報・各種コンテンツへアクセスできます</p>
            </div>
        </div>

        <!-- ローディング表示 -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-slate-400 gap-3">
            <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-medium">データを読み込み中…</p>
        </div>

        <!-- メインコンテンツ -->
        <div v-else-if="home" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- 1. 事務局レター -->
            <section class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition-all duration-200 flex flex-col">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base">📢</span>
                        <h3 class="font-bold text-slate-800 text-base">最新のお知らせ</h3>
                    </div>
                    <!-- Controllerから渡された URL を指定 -->
                    <router-link
                    v-if="home.letter_url"
                    :to="home.letter_url"
                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-0.5 transition-colors"
                    >
                        <span>すべて見る</span>
                        <span>›</span>
                    </router-link>
                </div>
                <ul class="space-y-3 flex-1">
                    <li v-for="l in home.letter" :key="l.id">
                    <router-link
                        :to="l.url"
                        class="group block text-xs md:text-sm hover:bg-slate-50 p-2 rounded-lg transition-colors border border-transparent hover:border-slate-200"
                    >
                        <span class="text-xs font-semibold text-blue-600 block mb-0.5">{{ l.date }}</span>
                        <span class="text-slate-700 group-hover:text-blue-600 transition-colors font-medium line-clamp-2">{{ l.title }}</span>
                    </router-link>
                    </li>
                    <li v-if="!home.letter?.length" class="text-xs text-slate-400 italic p-2">お知らせはありません</li>
                </ul>
            </section>

            <!-- 2. 回覧 -->
            <section class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition-all duration-200 flex flex-col">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base">🔄</span>
                    <h3 class="font-bold text-slate-800 text-base">回覧</h3>
                    </div>
                    <!-- Controllerから渡された URL を指定 -->
                    <router-link
                    v-if="home.circulate_url"
                    :to="home.circulate_url"
                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-0.5 transition-colors"
                    >
                    <span>すべて見る</span>
                    <span>›</span>
                    </router-link>
                </div>
                <ul class="space-y-2.5 flex-1">
                    <li v-for="c in home.circulate" :key="c.date">
                    <router-link
                        :to="c.url"
                        class="flex items-center justify-between text-xs md:text-sm p-2.5 rounded-lg bg-slate-50 hover:bg-indigo-50/60 hover:text-indigo-700 transition-all font-medium border border-slate-100"
                    >
                        <span class="text-slate-600 font-mono">{{ c.date }}</span>
                        <span class="bg-indigo-100 text-indigo-700 text-xs px-2.5 py-0.5 rounded-full font-bold">{{ c.count }}件</span>
                    </router-link>
                    </li>
                    <li v-if="!home.circulate?.length" class="text-xs text-slate-400 italic p-2">現在回覧はありません</li>
                </ul>
            </section>

            <!-- 3. スケジュール -->
            <section class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition-all duration-200 flex flex-col">
                <div class="flex items-center gap-2.5 mb-4 border-b border-slate-100 pb-3">
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base">📅</span>
                    <h3 class="font-bold text-slate-800 text-base">スケジュール</h3>
                </div>
                <div class="grid grid-cols-1 gap-3 flex-1">
                    <router-link
                    v-if="home.schedule?.schedule"
                    :to="home.schedule.schedule"
                    class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 hover:bg-emerald-50/70 hover:border-emerald-200 border border-slate-200/60 transition-all text-xs md:text-sm font-bold text-slate-700 hover:text-emerald-800 group"
                    >
                    <span class="flex items-center gap-2">📆 スケジュール一覧</span>
                    <span class="text-slate-400 group-hover:translate-x-1 transition-transform">→</span>
                    </router-link>
                    <router-link
                    v-if="home.schedule?.workshop"
                    :to="home.schedule.workshop"
                    class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 hover:bg-emerald-50/70 hover:border-emerald-200 border border-slate-200/60 transition-all text-xs md:text-sm font-bold text-slate-700 hover:text-emerald-800 group"
                    >
                    <span class="flex items-center gap-2">🎓 研修会一覧</span>
                    <span class="text-slate-400 group-hover:translate-x-1 transition-transform">→</span>
                    </router-link>
                </div>
            </section>

            <!-- 4. 書式ダウンロード -->
            <section class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition-all duration-200 lg:col-span-2">
                <div class="flex items-center gap-2.5 mb-4 border-b border-slate-100 pb-3">
                    <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base">📁</span>
                    <h3 class="font-bold text-slate-800 text-base">書式ダウンロード</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <router-link
                    v-for="d in home.downloads"
                    :key="d.key"
                    :to="d.url"
                    class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-amber-50/60 hover:border-amber-200 border border-slate-100 transition-all text-xs md:text-sm font-medium text-slate-700 hover:text-amber-800 group"
                    >
                    <span class="truncate pr-2">📄 {{ d.label }}</span>
                    <span class="text-slate-300 group-hover:text-amber-600 transition-colors text-xs font-bold">開く</span>
                    </router-link>
                </div>
            </section>

            <!-- 5. その他カテゴリ -->
            <section class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition-all duration-200 flex flex-col">
                <div class="flex items-center gap-2.5 mb-4 border-b border-slate-100 pb-3">
                    <span class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-base">🗂️</span>
                    <h3 class="font-bold text-slate-800 text-base">その他</h3>
                </div>
                <div class="space-y-2 flex-1">
                    <router-link
                    v-for="c in home.categories"
                    :key="c.key"
                    :to="c.url"
                    class="flex items-center justify-between p-2.5 rounded-lg hover:bg-sky-50/60 hover:text-sky-700 transition-all text-xs md:text-sm font-medium text-slate-700 border border-transparent hover:border-sky-100"
                    >
                    <span>📌 {{ c.label }}</span>
                    <span class="text-xs text-slate-400">→</span>
                    </router-link>
                </div>
            </section>

            <!-- 6. 理事会専用（権限がある場合のみカードとして表示） -->
            <section
            v-if="home.director_only"
            class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-6 shadow-md border border-slate-800 lg:col-span-3 flex flex-col sm:flex-row items-center justify-between gap-4"
            >
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-xl">
                    👑
                    </div>
                    <div>
                    <h3 class="font-bold text-base text-white">理事会専用エリア</h3>
                    <p class="text-xs text-indigo-200/80 mt-0.5">理事専用の各種ドキュメント</p>
                    </div>
                </div>
                <router-link
                    :to="home.director_only"
                    class="w-full sm:w-auto inline-flex items-center justify-center bg-indigo-500 hover:bg-indigo-400 text-white font-bold text-xs md:text-sm px-6 py-3 rounded-xl transition-all shadow-sm active:scale-95"
                >
                    理事会専用ページへ進む →
                </router-link>
            </section>

        </div>

        <!-- エラー時表示 -->
        <div v-else class="bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl p-6 text-center text-sm font-medium">
            ⚠️ データを読み込めませんでした。時間をおいて再読み込みしてください。
        </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../api.js";

const home = ref(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const res = await api.get("/home");
        home.value = res.data.data || res.data;
    } catch (error) {
        console.error("API Error:", error);
    } finally {
        loading.value = false;
    }
});
</script>
