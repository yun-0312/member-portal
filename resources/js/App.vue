<template>
    <div class="flex flex-col min-h-screen">
        <!-- ログイン前 -->
        <PublicHeader v-if="!user" />

        <!-- system_admin の場合 (専用ヘッダー) -->
        <SystemAdminHeader v-else-if="isSystemAdmin" />

        <!-- admin または staff の場合 -->
        <AdminHeader v-else-if="isAdminOrStaff" />

        <!-- それ以外の一般ユーザー (user, medical_staff など) -->
        <UserHeader v-else />

        <main class="flex-grow">
            <RouterView />
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import PublicHeader from "./components/PublicHeader.vue";
import UserHeader from "./components/UserHeader.vue";
import AdminHeader from "./components/AdminHeader.vue";
import SystemAdminHeader from "./components/SystemAdminHeader.vue";

const router = useRouter();
const user = ref(null);

const normalizeRoleName = (value) => {
    if (!value) return null;

    if (typeof value === "string") {
        return value.toLowerCase().replace(/[-\s]+/g, "_");
    }

    if (typeof value === "object") {
        return normalizeRoleName(value.name);
    }

    return null;
};

const isSystemAdmin = computed(() => {
    if (!user.value) return false;

    const roleName = normalizeRoleName(
        user.value.role ?? user.value.role_name ?? user.value.roleName
    );

    return roleName === "system_admin";
});

const isAdminOrStaff = computed(() => {
    if (!user.value) return false;

    // ロール名の取得（オブジェクト/文字列どちらにも対応）
    const roleName = normalizeRoleName(
        user.value.role ?? user.value.role_name ?? user.value.roleName
    );

    if (roleName) {
        return ["admin", "staff"].includes(roleName);
    }

    return false;
});

const fetchUser = () => {
    const rawData = localStorage.getItem("user");
    if (!rawData) {
        user.value = null;
        return;
    }

    try {
        const parsed = JSON.parse(rawData);
        user.value = parsed.user ? parsed.user : parsed;
    } catch (e) {
        console.error("userデータのパースエラー", e);
        user.value = null;
    }
};

onMounted(() => {
    fetchUser();
});

// ログイン・ログアウト等の更新イベントを受け取る
window.addEventListener("user-updated", () => {
    fetchUser();

    if (user.value) {
        // 取得した user.value から直接判定（Computedの遅延を回避）
        const roleName = normalizeRoleName(
            user.value.role ?? user.value.role_name ?? user.value.roleName
        );

        if (roleName === "system_admin") {
            router.push({ name: "System" });
        } else if (["admin", "staff"].includes(roleName)) {
            router.push({ name: "AdminDashboard" });
        } else {
            router.push({ name: "PublicDashboard" });
        }
    } else {
        router.push("/");
    }
});

</script>
