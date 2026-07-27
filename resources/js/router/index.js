import { createRouter, createWebHistory } from "vue-router";

import Login from "../pages/Auth/Login.vue";
import ForgotPassword from "../pages/Auth/ResetPassword.vue";
import ResetPasswordConfirm from "../pages/Auth/ResetPasswordConfirm.vue";
import RegisterMedicalStaff from "../pages/Auth/RegisterMedicalStaff.vue";
import ChangePassword from "../pages/Auth/ChangePassword.vue";
import Dashboard from "../pages/public/Dashboard.vue";
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
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
