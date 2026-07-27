<template>
    <header class="bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

        <!-- ロゴ・サイト名エリア -->
        <router-link
            to="/"
            class="flex items-center gap-3 group transition-all duration-200 hover:opacity-90 active:scale-98"
        >
            <div class="w-9 h-9 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20 shadow-inner">
            <span class="text-xl">🏥</span>
            </div>
            <div>
            <span class="text-lg font-bold tracking-tight block leading-tight">会員専用サイト</span>
            <span class="text-[10px] text-blue-200 font-medium tracking-widest uppercase block">Member Portal</span>
            </div>
        </router-link>

        <!-- 右側アクションエリア -->
        <nav class="flex items-center gap-4">
            <a
            href="/register-medical-staff"
            class="group relative inline-flex items-center justify-center text-sm font-bold text-blue-700 bg-white hover:bg-blue-50 px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-95"
            >
            <span>医療機関スタッフ会員登録</span>
            <svg
                class="w-4 h-4 ml-1.5 transition-transform duration-200 group-hover:translate-x-1"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.5"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
            </a>
        </nav>

        </div>
    </header>
</template>

<script setup>
import { useRouter } from "vue-router";

const router = useRouter();
</script>