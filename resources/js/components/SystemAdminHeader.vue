<template>
    <header class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-xl sticky top-0 z-50 border-b border-indigo-500/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

            <!-- ロゴ・サイト名エリア -->
            <router-link
                to="/system"
                class="flex items-center gap-3 group transition-all duration-200 hover:opacity-90 active:scale-98"
            >
                <div class="w-9 h-9 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20 shadow-inner">
                <span class="text-xl">⚙️</span>
                </div>
                <div>
                <span class="text-lg font-bold tracking-tight block leading-tight">システム管理者専用</span>
                <span class="text-[10px] text-blue-200 font-medium tracking-widest uppercase block">Member Portal</span>
                </div>
            </router-link>

            <!-- 右側：ログアウトボタン -->
            <div class="flex items-center gap-4">
                <button
                    @click="handleLogout"
                    class="text-xs font-semibold bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg border border-white/20 transition-colors"
                >
                    ログアウト
                </button>
            </div>
        </div>
    </header>
</template>

<script setup>
import { useRouter } from "vue-router";
import api from "../api.js";

const router = useRouter();

const handleLogout = async () => {
    try {
        await api.post("/logout");
    } catch (error) {
        console.error("ログアウト失敗", error);
    } finally {
        localStorage.removeItem("user");
        localStorage.removeItem("token");

        window.dispatchEvent(new Event("user-updated"));

        router.push("/");
    }
};
</script>