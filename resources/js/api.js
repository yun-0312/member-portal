import axios from "axios";
import router from "./router";

const api = axios.create({
    baseURL: "/api",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    withCredentials: true,
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem("token");

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

// レスポンス時の処理（401エラーの自動検知とリダイレクト）
api.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // 401 Unauthenticated（認証切れ）が発生した場合
        if (error.response && error.response.status === 401) {
            const currentPath = router.currentRoute.value.path;

            if (currentPath !== "/" && currentPath !== "/login") {
                // 1. ローカルストレージの古い情報をクリア
                localStorage.removeItem("token");
                localStorage.removeItem("user");

                // 2. ヘッダー更新用イベントを発火して Vue 側の状態を同期させる
                window.dispatchEvent(new Event("user-updated"));

                // 3. ログイン画面へ遷移
                router.push("/");
            }
        }

        return Promise.reject(error);
    },
);

export default api;
