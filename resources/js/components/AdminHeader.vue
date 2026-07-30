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
                to="/dashboard"
                class="flex items-center gap-3 group transition-all duration-200 hover:opacity-90 active:scale-98"
            >
                <!-- 管理者用アイコン枠 -->
                <div
                    class="w-9 h-9 bg-indigo-500/20 backdrop-blur-md rounded-xl flex items-center justify-center border border-indigo-400/30 shadow-inner text-indigo-300"
                >
                    <span class="text-lg">⚙️</span>
                </div>
                <div class="flex items-center gap-2">
                    <div>
                        <span
                            class="text-base md:text-lg font-bold tracking-tight block leading-tight text-slate-100"
                            >管理者専用</span
                        >
                        <span
                            class="text-[10px] text-indigo-300/80 font-medium tracking-widest uppercase block"
                            >Admin Control Panel</span
                        >
                    </div>
                </div>
            </router-link>

            <!-- 右側：ユーザー情報 & ナビゲーション -->
            <div class="flex items-center gap-3 sm:gap-5">
                <!-- ユーザー名（管理用タグ風） -->
                <div
                    class="hidden md:flex items-center gap-1.5 bg-slate-800/80 border border-slate-700 text-xs px-3 py-1.5 rounded-full text-slate-200"
                >
                    <span
                        class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"
                    ></span>
                    <span class="font-semibold">{{
                        header?.user?.name || "管理者"
                    }}</span>
                </div>

                <!-- メニュー（最小限の3つ） -->
                <nav>
                    <ul
                        class="flex items-center gap-2 sm:gap-3 text-xs md:text-sm font-medium"
                    >
                        <li v-for="item in header.menu" :key="item.label">
                            <!-- 1. 一般サイト（ホーム）へ戻るボタン -->
                            <router-link
                                v-if="item.url === '/dashboard'"
                                :to="item.url"
                                class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 hover:border-slate-500 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm active:scale-95"
                            >
                                <span>🌐 {{ item.label }}</span>
                            </router-link>

                            <!-- 2. 管理画面トップ（主導線ボタン） -->
                            <router-link
                                v-else-if="item.url === '/admin/management'"
                                :to="item.url"
                                active-class="!bg-indigo-600 !border-indigo-400 !text-white shadow-indigo-500/20"
                                class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 hover:border-slate-500 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm active:scale-95"
                            >
                                <span>📊 {{ item.label }}</span>
                            </router-link>

                            <!-- 3. その他通常URL（汎用） -->
                            <router-link
                                v-else-if="item.url"
                                :to="item.url"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs md:text-sm font-medium text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition-all duration-200"
                            >
                                {{ item.label }}
                            </router-link>

                            <!-- 4. ログアウトボタン -->
                            <button
                                v-else-if="item.action === 'logout'"
                                @click="logout"
                                class="inline-flex items-center gap-1 bg-rose-500/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 hover:border-rose-500 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 active:scale-95 ml-1"
                            >
                                <span>🚪 {{ item.label }}</span>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../api.js";

const router = useRouter();
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
