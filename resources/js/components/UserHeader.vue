<template>
    <header
        class="bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 text-white shadow-lg sticky top-0 z-50"
    >
        <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
        >
        <!-- 左側：ロゴ・サイト名エリア -->
        <router-link
            to="/dashboard"
            class="flex items-center gap-2.5 sm:gap-3 group transition-all duration-200 hover:opacity-90 active:scale-98 shrink-0"
        >
            <div
            class="w-8 h-8 sm:w-9 sm:h-9 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20 shadow-inner group-hover:bg-white/20 transition-colors"
            >
            <span class="text-lg sm:text-xl">🏥</span>
            </div>
            <div>
            <span
                class="text-base sm:text-lg font-bold tracking-tight block leading-tight group-hover:text-blue-100 transition-colors whitespace-nowrap"
                >会員専用サイト</span
            >
            <span
                class="text-[9px] sm:text-[10px] text-blue-200 font-medium tracking-widest uppercase block whitespace-nowrap"
                >Member Portal</span
            >
            </div>
        </router-link>

        <!-- PC用ナビゲーション（sm以上で表示） -->
        <div class="hidden sm:flex items-center gap-5">
            <div
            class="flex items-center gap-1.5 bg-white/10 border border-white/20 text-xs px-3 py-1.5 rounded-full text-blue-50 shadow-inner"
            >
            <span class="opacity-75">👤</span>
            <span class="font-semibold">{{
                header?.user?.name || "ユーザー"
            }}</span>
            様
            </div>

            <nav>
            <ul class="flex items-center gap-4 text-sm font-medium">
                <li v-for="item in header?.menu || []" :key="item.label">
                <router-link
                    v-if="item.url"
                    :to="item.url"
                    class="hover:text-blue-200 transition-colors py-1 border-b-2 border-transparent hover:border-blue-200"
                >
                    {{ item.label }}
                </router-link>

                <button
                    v-else-if="item.action === 'logout'"
                    @click="logout"
                    class="bg-rose-500/90 hover:bg-rose-600 text-white text-xs font-bold px-3.5 py-1.5 rounded-lg shadow transition-all duration-200 active:scale-95 flex items-center gap-1 border border-rose-400/30"
                >
                    <span>ログアウト</span>
                </button>
                </li>
            </ul>
            </nav>
        </div>

        <!-- スマホ用：ハンバーガーボタン（sm未満で表示） -->
        <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="sm:hidden p-2 rounded-lg bg-white/10 hover:bg-white/20 text-white focus:outline-none border border-white/20"
            aria-label="メニューを開く"
        >
            <!-- 開いてない時の三本線アイコン -->
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
            <!-- 開いている時の「×」アイコン -->
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

        <!-- スマホ用スライドメニュー（オーバーレイ） -->
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
            class="sm:hidden bg-blue-800/95 backdrop-blur-md border-t border-blue-600/50 px-4 pt-3 pb-6 space-y-4 shadow-2xl"
        >
            <!-- スマホ用：ユーザー情報表示 -->
            <div
            class="flex items-center gap-2 bg-white/10 px-3 py-2 rounded-xl text-sm border border-white/10"
            >
            <span>👤</span>
            <span class="font-bold">{{ header?.user?.name || "ユーザー" }}</span>
            <span class="text-xs text-blue-200">様でログイン中</span>
            </div>

            <!-- スマホ用：メニュー一覧 -->
            <nav class="space-y-2">
            <div v-for="item in header?.menu || []" :key="item.label">
                <router-link
                v-if="item.url"
                :to="item.url"
                @click="isMobileMenuOpen = false"
                class="block py-2.5 px-3 rounded-lg hover:bg-white/10 text-base font-medium transition-colors"
                >
                {{ item.label }}
                </router-link>

                <button
                v-else-if="item.action === 'logout'"
                @click="
                    () => {
                    isMobileMenuOpen = false;
                    logout();
                    }
                "
                class="w-full text-left py-2.5 px-3 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-200 font-bold text-base transition-colors flex items-center justify-between border border-rose-500/30 mt-2"
                >
                <span>ログアウト</span>
                <span class="text-sm">➔</span>
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
const isMobileMenuOpen = ref(false); // スマホメニューの開閉状態
const header = ref({
    user: { name: "ユーザー" },
    menu: [],
});

onMounted(async () => {
    try {
        const res = await api.get("/header");
        header.value = {
        user: res.data?.user || { name: "ユーザー" },
        menu: res.data?.menu || [],
        };
    } catch (error) {
        console.error("ヘッダー取得失敗", error);
        header.value = {
        user: { name: "ユーザー" },
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