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
                class="flex items-center gap-3 group transition-all duration-200 hover:opacity-90 active:scale-98"
            >
                <div class="w-9 h-9 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20 shadow-inner group-hover:bg-white/20 transition-colors">
                    <span class="text-xl">🏥</span>
                </div>
                <div>
                    <span class="text-lg font-bold tracking-tight block leading-tight group-hover:text-blue-100 transition-colors">会員専用サイト</span>
                    <span class="text-[10px] text-blue-200 font-medium tracking-widest uppercase block">Member Portal</span>
                </div>
            </router-link>

            <!-- 右側：ユーザー情報 & ナビゲーション -->
            <div class="flex items-center gap-5">
                <div
                    class="hidden sm:flex items-center gap-1.5 bg-white/10 border border-white/20 text-xs px-3 py-1.5 rounded-full text-blue-50 shadow-inner"
                >
                    <span class="opacity-75">👤</span>
                    <span class="font-semibold">{{
                        header?.user?.name || "ユーザー"
                    }}</span>
                    様
                </div>

                <!-- メニュー -->
                <nav>
                    <ul class="flex items-center gap-4 text-sm font-medium">
                        <li
                            v-for="item in header?.menu || []"
                            :key="item.label"
                        >
                            <!-- 通常リンク -->
                            <router-link
                                v-if="item.url"
                                :to="item.url"
                                class="hover:text-blue-200 transition-colors py-1 border-b-2 border-transparent hover:border-blue-200"
                            >
                                {{ item.label }}
                            </router-link>

                            <!-- ログアウトボタン -->
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
        </div>
    </header>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../api.js";

const router = useRouter();
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
