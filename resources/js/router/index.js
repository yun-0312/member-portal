import { createRouter, createWebHistory } from "vue-router";

import Login from "../pages/Auth/Login.vue";
import ForgotPassword from "../pages/Auth/ResetPassword.vue";
import ResetPasswordConfirm from "../pages/Auth/ResetPasswordConfirm.vue";
import Dashboard from "../pages/public/Dashboard.vue";
import NoticesIndex from "../pages/public/NoticesIndex.vue";
import ContentsIndex from "../pages/public/ContentsIndex.vue";
import ScheduleList from "../pages/public/ScheduleList.vue";
import WorkshopList from "../pages/public/WorkshopList.vue";
import FaqList from "../pages/public/FaqList.vue";
import UserProfile from "../pages/public/UserProfile.vue";

const routes = [
    { path: "/", name: "login", component: Login },
    {
        path: "/forgot-password",
        name: "forgot-password",
        component: ForgotPassword,
    },
    {
        path: "/reset-password",
        name: "reset-password",
        component: ResetPasswordConfirm,
    },
    { path: "/dashboard", name: "dashboard", component: Dashboard },
    {
        path: "/notices",
        name: "NoticesIndex",
        component: NoticesIndex,
    },
    {
        path: "/contents",
        name: "ContentsIndex",
        component: ContentsIndex,
    },
    {
        path: "/schedules",
        name: "ScheduleList",
        component: ScheduleList,
    },
    {
        path: "/workshops",
        name: "WorkshopList",
        component: WorkshopList,
    },
    {
        path: "/faqs",
        name: "FaqList",
        component: FaqList,
    },
    {
        path: "/users/:id",
        name: "UserProfile",
        component: UserProfile,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
