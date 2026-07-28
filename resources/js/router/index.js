import { createRouter, createWebHistory } from "vue-router";

import Login from "../pages/Auth/Login.vue";
import ForgotPassword from "../pages/Auth/ResetPassword.vue";
import ResetPasswordConfirm from "../pages/Auth/ResetPasswordConfirm.vue";
import RegisterMedicalStaff from "../pages/Auth/RegisterMedicalStaff.vue";
import ChangePassword from "../pages/Auth/ChangePassword.vue";
import DashboardView from "../components/DashboardView.vue";
import NoticesIndex from "../pages/public/NoticesIndex.vue";
import ContentsIndex from "../pages/public/ContentsIndex.vue";
import ScheduleList from "../pages/public/ScheduleList.vue";
import WorkshopList from "../pages/public/WorkshopList.vue";
import FaqList from "../pages/public/FaqList.vue";
import VideoList from "../pages/public/VideoList.vue";
import UserProfile from "../pages/public/UserProfile.vue";
import UserEdit from "../pages/public/UserEdit.vue";
import MedicalInstitutionUsers from "../pages/public/MedicalInstitutionUsers.vue";
import MedicalInstitutionDetail from "../pages/public/MedicalInstitutionDetail.vue";
import MedicalInstitutionEdit from "../pages/public/MedicalInstitutionEdit.vue";

import AdminNoticesIndex from "../pages/admin/NoticesIndex.vue";
import AdminNoticeShow from "../pages/admin/NoticeShow.vue";
import AdminWorkshopList from "../pages/admin/WorkshopList.vue";
import AdminWorkshopShow from "../pages/admin/WorkshopShow.vue";
import AdminVideoList from "../pages/admin/VideoList.vue";
import AdminVideoShow from "../pages/admin/VideoShow.vue";
import AdminContentsIndex from "../pages/admin/ContentsIndex.vue";
import AdminContentShow from "../pages/admin/ContentShow.vue";
import AdminFaqList from "../pages/admin/FaqList.vue";
import AdminScheduleList from "../pages/admin/ScheduleList.vue";
import AdminScheduleShow from "../pages/admin/ScheduleShow.vue";

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
    {
        path: "/users/password",
        name: "ChangePassword",
        component: ChangePassword,
        meta: { requiresAuth: true },
    },
    {
        path: "/register-medical-staff",
        name: "register-medical-staff",
        component: RegisterMedicalStaff,
    },
    {
        path: "/dashboard",
        name: "PublicDashboard",
        component: DashboardView,
        props: {
            apiEndpoint: "/home",
            title: "ダッシュボード",
        },
        meta: { requiresAuth: true },
    },
    {
        path: "/admin/dashboard",
        name: "AdminDashboard",
        component: DashboardView,
        props: {
            apiEndpoint: "/admin/home",
            title: "管理者ダッシュボード",
        },
        meta: { requiresAuth: true, roles: ["admin", "staff"] },
    },
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
        path: "/videos",
        name: "VideoList",
        component: VideoList,
    },
    {
        path: "/admin/users/:id",
        name: "UserEdit",
        component: UserEdit,
    },
    {
        path: "/users/:id",
        name: "UserProfile",
        component: UserProfile,
    },
    {
        path: "/admin/medical-institutions/:id",
        name: "MedicalInstitutionEdit",
        component: MedicalInstitutionEdit,
    },
    {
        path: "/medical-institutions/:id/users",
        name: "MedicalInstitutionUsers",
        component: MedicalInstitutionUsers,
    },
    {
        path: "/medical-institutions/:id",
        name: "MedicalInstitutionDetail",
        component: MedicalInstitutionDetail,
    },
    // 管理者グループ（Prefix: /admin）
    {
        path: "/admin",
        children: [
            {
                path: "/admin/notices",
                name: "admin.noticesIndex",
                component: AdminNoticesIndex,
            },
            {
                path: "/admin/notices/:id",
                name: "NoticesShow",
                component: AdminNoticeShow,
            },
            {
                path: "/admin/workshops",
                name: "admin.workshopList",
                component: AdminWorkshopList,
            },
            {
                path: "/admin/workshops/:id",
                name: "admin.workshopShow",
                component: AdminWorkshopShow,
            },
            {
                path: "/admin/videos",
                name: "admin.videoList",
                component: AdminVideoList,
            },
            {
                path: "/admin/videos/:id",
                name: "admin.videoShow",
                component: AdminVideoShow,
            },
            {
                path: "/admin/contents",
                name: "admin.contentsIndex",
                component: AdminContentsIndex,
            },
            {
                path: "/admin/contents/:id",
                name: "admin.contentShow",
                component: AdminContentShow,
            },
            {
                path: "/admin/faqs",
                name: "admin.faqList",
                component: AdminFaqList,
            },
            {
                path: "/admin/schedules",
                name: "admin.ScheduleList",
                component: AdminScheduleList,
            },
            {
                path: "/admin/schedules/:id",
                name: "admin.ScheduleShow",
                component: AdminScheduleShow,
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem("token");

    // 未ログインで認証必須ページへ行こうとした時だけログイン画面へ返す
    if (to.meta.requiresAuth && !token) {
        return next({ name: "login" });
    }

    next();
});

export default router;
