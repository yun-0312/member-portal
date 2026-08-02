<template>
    <header
        v-if="header"
        class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-xl sticky top-0 z-50 border-b border-indigo-500/20"
    >
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
        >
            <!-- 左側：ロゴ・サイト名エリア -->
            <router-link
                to="/admin/dashboard"
                class="flex items-center gap-2.5 sm:gap-3 group transition-all duration-200 hover:opacity-90 active:scale-98 shrink-0"
            >
                <!-- 管理者用アイコン枠 -->
                <div
                    class="w-8 h-8 sm:w-9 sm:h-9 bg-indigo-500/20 backdrop-blur-md rounded-xl flex items-center justify-center border border-indigo-400/30 shadow-inner text-indigo-300 group-hover:bg-indigo-500/30 transition-colors"
                >
                    <span class="text-base sm:text-lg">⚙️</span>
                </div>
                <div class="flex items-center gap-2">
                    <div>
                        <span
                            class="text-sm sm:text-base md:text-lg font-bold tracking-tight block leading-tight text-slate-100 whitespace-nowrap"
                            >管理者専用</span
                        >
                        <span
                            class="text-[9px] sm:text-[10px] text-indigo-300/80 font-medium tracking-widest uppercase block whitespace-nowrap"
                            >Admin Control Panel</span
                        >
                    </div>
                </div>
            </router-link>

            <!-- 右側：PC用ナビゲーション（md以上で表示） -->
            <div class="hidden md:flex items-center gap-3 sm:gap-5">
                <!-- ユーザー名（管理用タグ風） -->
                <div
                    class="flex items-center gap-1.5 bg-slate-800/80 border border-slate-700 text-xs px-3 py-1.5 rounded-full text-slate-200"
                >
                    <span
                        class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"
                    ></span>
                    <span class="font-semibold">{{
                        header?.user?.name || "管理者"
                    }}</span>
                </div>

                <!-- メニュー -->
                <nav>
                    <ul class="flex items-center gap-2 sm:gap-3 text-xs md:text-sm font-medium">
                        <li v-for="item in header.menu" :key="item.label">
                            <!-- 1. 一般サイト（ホーム）へ戻るボタン -->
                            <router-link
                                v-if="item.url === '/dashboard'"
                                :to="item.url"
                                class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 hover:border-slate-500 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm active:scale-95 whitespace-nowrap"
                            >
                                <span>🌐 {{ item.label }}</span>
                            </router-link>

                            <!-- 2. 管理画面トップ（主導線ボタン） -->
                            <router-link
                                v-else-if="item.url === '/admin/management'"
                                :to="item.url"
                                active-class="!bg-indigo-600 !border-indigo-400 !text-white shadow-indigo-500/20"
                                class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 hover:border-slate-500 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm active:scale-95 whitespace-nowrap"
                            >
                                <span>📊 {{ item.label }}</span>
                            </router-link>

                            <!-- 3. その他通常URL（汎用） -->
                            <router-link
                                v-else-if="item.url"
                                :to="item.url"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs md:text-sm font-medium text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition-all duration-200 whitespace-nowrap"
                            >
                                {{ item.label }}
                            </router-link>

                            <!-- 4. ログアウトボタン -->
                            <button
                                v-else-if="item.action === 'logout'"
                                @click="logout"
                                class="inline-flex items-center gap-1 bg-rose-500/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 hover:border-rose-500 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 active:scale-95 ml-1 whitespace-nowrap"
                            >
                                <span>🚪 {{ item.label }}</span>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- スマホ用：ハンバーガーボタン（md未満で表示） -->
            <button
                @click="isMobileMenuOpen = !isMobileMenuOpen"
                class="md:hidden p-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-200 focus:outline-none border border-slate-700"
                aria-label="メニューを開く"
            >
                <svg
                    v-if="!isMobileMenuOpen"
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>
                <svg
                    v-else
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- スマホ用スライドメニュー -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="isMobileMenuOpen"
                class="md:hidden bg-slate-900/95 backdrop-blur-md border-t border-slate-800 px-4 pt-3 pb-6 space-y-4 shadow-2xl"
            >
                <!-- スマホ用：管理者ユーザー情報 -->
                <div
                    class="flex items-center justify-between bg-slate-800/80 border border-slate-700 px-3.5 py-2.5 rounded-xl text-xs text-slate-200"
                >
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="font-bold text-slate-100">{{ header?.user?.name || "管理者" }}</span>
                    </div>
                    <span class="text-[10px] bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2 py-0.5 rounded-full font-medium">SYSTEM ADMIN</span>
                </div>

                <!-- スマホ用：メニューリンク集 -->
                <nav class="space-y-2">
                    <div v-for="item in header.menu" :key="item.label">
                        <!-- 一般サイトへ -->
                        <router-link
                            v-if="item.url === '/dashboard'"
                            :to="item.url"
                            @click="isMobileMenuOpen = false"
                            class="flex items-center gap-2 w-full py-2.5 px-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm border border-slate-700 transition-colors"
                        >
                            <span>🌐</span>
                            <span>{{ item.label }}</span>
                        </router-link>

                        <!-- 管理画面トップ -->
                        <router-link
                            v-else-if="item.url === '/admin/management'"
                            :to="item.url"
                            @click="isMobileMenuOpen = false"
                            active-class="!bg-indigo-600 !border-indigo-400 !text-white"
                            class="flex items-center gap-2 w-full py-2.5 px-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm border border-slate-700 transition-colors"
                        >
                            <span>📊</span>
                            <span>{{ item.label }}</span>
                        </router-link>

                        <!-- その他汎用URL -->
                        <router-link
                            v-else-if="item.url"
                            :to="item.url"
                            @click="isMobileMenuOpen = false"
                            class="flex items-center gap-2 w-full py-2.5 px-3.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 font-medium text-sm border border-white/10 transition-colors"
                        >
                            <span>{{ item.label }}</span>
                        </router-link>

                        <!-- ログアウト -->
                        <button
                            v-else-if="item.action === 'logout'"
                            @click="() => { isMobileMenuOpen = false; logout(); }"
                            class="flex items-center justify-between w-full py-2.5 px-3.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 font-bold text-sm border border-rose-500/30 transition-colors mt-3"
                        >
                            <span class="flex items-center gap-2">
                                <span>🚪</span>
                                <span>{{ item.label }}</span>
                            </span>
                            <span class="text-xs">➔</span>
                        </button>
                    </div>
                </nav>
            </div>
        </transition>
    </header>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../api.js";

const router = useRouter();
const isMobileMenuOpen = ref(false); // スマホメニューの開閉フラグ
const header = ref({
    user: { name: "管理者" },
    menu: [],
});

onMounted(async () => {
    try {
        const res = await api.get("/admin/header");
        header.value = {
            user: res.data?.user || { name: "管理者" },
            menu: res.data?.menu || [],
        };
    } catch (error) {
        console.error("管理者ヘッダー取得失敗", error);
        header.value = {
            user: { name: "管理者" },
            menu: [],
        };
    }
});

const logout = async () => {
    try {
        await api.post("/logout");

        localStorage.removeItem("user");
        localStorage.removeItem("token");

        window.dispatchEvent(new Event("user-updated"));

        router.push("/");
    } catch (error) {
        console.error("ログアウト失敗", error);
    }
};
</script>