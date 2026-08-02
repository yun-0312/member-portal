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
import VideoShow from "../pages/public/VideoShow.vue";
import UserProfile from "../pages/public/UserProfile.vue";
import UserEdit from "../pages/public/UserEdit.vue";
import MedicalInstitutionUsers from "../pages/public/MedicalInstitutionUsers.vue";
import MedicalInstitutionDetail from "../pages/public/MedicalInstitutionDetail.vue";
import MedicalInstitutionEdit from "../pages/public/MedicalInstitutionEdit.vue";

import AdminNoticesIndex from "../pages/admin/NoticesIndex.vue";
import AdminNoticeShow from "../pages/admin/NoticeShow.vue";
import AdminNoticeCreate from "../pages/admin/NoticeCreate.vue";
import AdminNoticeEdit from "../pages/admin/NoticeEdit.vue";
import AdminWorkshopList from "../pages/admin/WorkshopList.vue";
import AdminWorkshopShow from "../pages/admin/WorkshopShow.vue";
import AdminWorkshopCreate from "../pages/admin/WorkshopCreate.vue";
import AdminWorkshopEdit from "../pages/admin/WorkshopEdit.vue";
import AdminVideoList from "../pages/admin/VideoList.vue";
import AdminVideoShow from "../pages/admin/VideoShow.vue";
import AdminVideoCreate from "../pages/admin/VideoCreate.vue";
import AdminVideoEdit from "../pages/admin/VideoEdit.vue";
import AdminContentsIndex from "../pages/admin/ContentsIndex.vue";
import AdminContentShow from "../pages/admin/ContentShow.vue";
import AdminContentEdit from "../pages/admin/ContentEdit.vue";
import AdminContentCreate from "../pages/admin/ContentCreate.vue";
import AdminFaqList from "../pages/admin/FaqList.vue";
import AdminFaqCreate from "../pages/admin/FaqCreate.vue";
import AdminFaqEdit from "../pages/admin/FaqEdit.vue";
import AdminScheduleList from "../pages/admin/ScheduleList.vue";
import AdminScheduleShow from "../pages/admin/ScheduleShow.vue";
import AdminScheduleCreate from "../pages/admin/ScheduleCreate.vue";
import AdminScheduleEdit from "../pages/admin/ScheduleEdit.vue";
import AdminManagement from "../pages/admin/Management.vue";
import AdminMedicalInstitutionList from "../pages/admin/MedicalInstitutionList.vue";
import AdminMedicalInstitutionShow from "../pages/admin/MedicalInstitutionShow.vue";
import AdminMedicalInstitutionUsers from "../pages/admin/MedicalInstitutionUsers.vue";
import AdminMedicalInstitutionCreate from "../pages/admin/MedicalInstitutionCreate.vue";
import AdminMedicalInstitutionEdit from "../pages/admin/MedicalInstitutionEdit.vue";
import AdminUserList from "../pages/admin/UserList.vue";
import AdminPendingUserList from "../pages/admin/PendingUserList.vue";
import AdminUserShow from "../pages/admin/UserShow.vue";
import AdminUserCreate from "../pages/admin/UserCreate.vue";
import AdminUserEdit from "../pages/admin/UserEdit.vue";
import AdminFaqCategoryList from "../pages/admin/FaqCategoryList.vue";
import AdminFaqCategoryShow from "../pages/admin/FaqCategoryShow.vue";
import AdminFaqCategoryCreate from "../pages/admin/FaqCategoryCreate.vue";
import AdminFaqCategoryEdit from "../pages/admin/FaqCategoryEdit.vue";
import AdminScheduleCategoryList from "../pages/admin/ScheduleCategoryList.vue";
import AdminScheduleCategoryShow from "../pages/admin/ScheduleCategoryShow.vue";
import AdminScheduleCategoryCreate from "../pages/admin/ScheduleCategoryCreate.vue";
import AdminScheduleCategoryEdit from "../pages/admin/ScheduleCategoryEdit.vue";
import AdminNoticeCategoryList from "../pages/admin/NoticeCategoryList.vue";
import AdminNoticeCategoryShow from "../pages/admin/NoticeCategoryShow.vue";
import AdminNoticeCategoryCreate from "../pages/admin/NoticeCategoryCreate.vue";
import AdminNoticeCategoryEdit from "../pages/admin/NoticeCategoryEdit.vue";
import AdminPermissionList from "../pages/admin/PermissionList.vue";
import AdminPermissionShow from "../pages/admin/PermissionShow.vue";
import AdminPermissionCreate from "../pages/admin/PermissionCreate.vue";
import AdminPermissionEdit from "../pages/admin/PermissionEdit.vue";
import AdminRoomList from "../pages/admin/RoomList.vue";
import AdminRoomShow from "../pages/admin/RoomShow.vue";
import AdminRoomCreate from "../pages/admin/RoomCreate.vue";
import AdminRoomEdit from "../pages/admin/RoomEdit.vue";
import AdminRoleList from "../pages/admin/RoleList.vue";
import AdminRoleShow from "../pages/admin/RoleShow.vue";
import AdminRoleCreate from "../pages/admin/RoleCreate.vue";
import AdminRoleEdit from "../pages/admin/RoleEdit.vue";
import AdminContentCategoryList from "../pages/admin/ContentCategoryList.vue";
import AdminContentCategoryShow from "../pages/admin/ContentCategoryShow.vue";
import AdminContentCategoryCreate from "../pages/admin/ContentCategoryCreate.vue";
import AdminContentCategoryEdit from "../pages/admin/ContentCategoryEdit.vue";
import AdminContentSubcategoryShow from "../pages/admin/ContentSubcategoryShow.vue";
import AdminContentSubcategoryCreate from "../pages/admin/ContentSubcategoryCreate.vue";
import AdminContentSubcategoryEdit from "../pages/admin/ContentSubcategoryEdit.vue";

import System from "../pages/admin/System.vue";

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
        path: "/videos/:id",
        name: "VideoShow",
        component: VideoShow,
    },
    {
        path: "/users/:id/edit",
        name: "UserEdit",
        component: UserEdit,
    },
    {
        path: "/users/:id",
        name: "UserProfile",
        component: UserProfile,
    },
    {
        path: "/medical-institutions/:id/edit",
        name: "MedicalInstitutionEdit",
        component: MedicalInstitutionEdit,
        meta: { requiresAuth: true },
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
                path: "/admin/notices/create",
                name: "NoticesCreate",
                component: AdminNoticeCreate,
            },
            {
                path: "/admin/notices/:id/edit",
                name: "NoticesEdit",
                component: AdminNoticeEdit,
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
                path: "/admin/workshops/create",
                name: "admin.workshopCreate",
                component: AdminWorkshopCreate,
            },
            {
                path: "/admin/workshops/:id/edit",
                name: "admin.workshopEdit",
                component: AdminWorkshopEdit,
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
                path: "/admin/videos/create",
                name: "admin.videoCreate",
                component: AdminVideoCreate,
            },
            {
                path: "/admin/videos/:id/edit",
                name: "admin.videoEdit",
                component: AdminVideoEdit,
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
                path: "/admin/contents/create",
                name: "admin.contentCreate",
                component: AdminContentCreate,
            },
            {
                path: "/admin/contents/:id/edit",
                name: "admin.contentEdit",
                component: AdminContentEdit,
            },
            {
                path: "/admin/faqs",
                name: "admin.faqList",
                component: AdminFaqList,
            },
            {
                path: "/admin/faqs/create",
                name: "admin.faqCreate",
                component: AdminFaqCreate,
            },
            {
                path: "/admin/faqs/:id",
                name: "admin.faqEdit",
                component: AdminFaqEdit,
            },
            {
                path: "/admin/schedules",
                name: "admin.ScheduleList",
                component: AdminScheduleList,
            },
            {
                path: "/admin/schedule-occurrences/:id",
                name: "admin.ScheduleShow",
                component: AdminScheduleShow,
            },
            {
                path: "/admin/schedules/create",
                name: "admin.ScheduleCreate",
                component: AdminScheduleCreate,
            },
            {
                path: "/admin/schedule-occurrences/:id/edit",
                name: "admin.ScheduleEdit",
                component: AdminScheduleEdit,
            },
            {
                path: "/admin/management",
                name: "admin.Management",
                component: AdminManagement,
            },
            {
                path: "/admin/medical-institutions",
                name: "admin.MedicalInstitutionList",
                component: AdminMedicalInstitutionList,
            },
            {
                path: "/admin/medical-institutions/:id",
                name: "admin.MedicalInstitutionShow",
                component: AdminMedicalInstitutionShow,
            },
            {
                path: "/admin/medical-institutions/:id/users",
                name: "admin.MedicalInstitutionUsers",
                component: AdminMedicalInstitutionUsers,
            },
            {
                path: "/admin/medical-institutions/create",
                name: "admin.MedicalInstitutionCreate",
                component: AdminMedicalInstitutionCreate,
            },
            {
                path: "/admin/medical-institutions/:id/edit",
                name: "admin.MedicalInstitutionEdit",
                component: AdminMedicalInstitutionEdit,
            },
            {
                path: "/admin/users",
                name: "admin.UserList",
                component: AdminUserList,
            },
            {
                path: "/admin/users/pending",
                name: "admin.PendingUserList",
                component: AdminPendingUserList,
            },
            {
                path: "/admin/users/:id",
                name: "admin.UserShow",
                component: AdminUserShow,
            },
            {
                path: "/admin/users/create",
                name: "admin.UserCreate",
                component: AdminUserCreate,
            },
            {
                path: "/admin/users/:id/edit",
                name: "admin.UserEdit",
                component: AdminUserEdit,
                meta: { requiresAuth: true, roles: ["admin"] },
            },
            {
                path: "/admin/faq-categories",
                name: "admin.FaqCategoryList",
                component: AdminFaqCategoryList,
            },
            {
                path: "/admin/faq-categories/:id",
                name: "admin.FaqCategoryShow",
                component: AdminFaqCategoryShow,
            },
            {
                path: "/admin/faq-categories/create",
                name: "admin.FaqCategoryCreate",
                component: AdminFaqCategoryCreate,
            },
            {
                path: "/admin/faq-categories/:id/edit",
                name: "admin.FaqCategoryEdit",
                component: AdminFaqCategoryEdit,
            },
            {
                path: "/admin/schedule-categories",
                name: "admin.ScheduleCategoryList",
                component: AdminScheduleCategoryList,
            },
            {
                path: "/admin/schedule-categories/:id",
                name: "admin.ScheduleCategoryShow",
                component: AdminScheduleCategoryShow,
            },
            {
                path: "/admin/schedule-categories/create",
                name: "admin.ScheduleCategoryCreate",
                component: AdminScheduleCategoryCreate,
            },
            {
                path: "/admin/schedule-categories/:id/edit",
                name: "admin.ScheduleCategoryEdit",
                component: AdminScheduleCategoryEdit,
            },
            {
                path: "/admin/notice-categories",
                name: "admin.NoticeCategoryList",
                component: AdminNoticeCategoryList,
            },
            {
                path: "/admin/notice-categories/:id",
                name: "admin.NoticeCategoryShow",
                component: AdminNoticeCategoryShow,
            },
            {
                path: "/admin/notice-categories/create",
                name: "admin.NoticeCategoryCreate",
                component: AdminNoticeCategoryCreate,
            },
            {
                path: "/admin/notice-categories/:id/edit",
                name: "admin.NoticeCategoryEdit",
                component: AdminNoticeCategoryEdit,
            },
            {
                path: "/admin/permissions",
                name: "admin.PermissionList",
                component: AdminPermissionList,
            },
            {
                path: "/admin/permissions/:id",
                name: "admin.PermissionShow",
                component: AdminPermissionShow,
            },
            {
                path: "/admin/permissions/create",
                name: "admin.PermissionCreate",
                component: AdminPermissionCreate,
            },
            {
                path: "/admin/permissions/:id/edit",
                name: "admin.PermissionEdit",
                component: AdminPermissionEdit,
            },
            {
                path: "/admin/rooms",
                name: "admin.RoomList",
                component: AdminRoomList,
            },
            {
                path: "/admin/rooms/:id",
                name: "admin.RoomShow",
                component: AdminRoomShow,
            },
            {
                path: "/admin/rooms/create",
                name: "admin.RoomCreate",
                component: AdminRoomCreate,
            },
            {
                path: "/admin/rooms/:id/edit",
                name: "admin.RoomEdit",
                component: AdminRoomEdit,
            },
            {
                path: "/admin/roles",
                name: "admin.RoleList",
                component: AdminRoleList,
            },
            {
                path: "/admin/roles/:id",
                name: "admin.RoleShow",
                component: AdminRoleShow,
            },
            {
                path: "/admin/roles/create",
                name: "admin.RoleCreate",
                component: AdminRoleCreate,
            },
            {
                path: "/admin/roles/:id/edit",
                name: "admin.RoleEdit",
                component: AdminRoleEdit,
            },
            {
                path: "/admin/content-categories",
                name: "admin.ContentCategoryList",
                component: AdminContentCategoryList,
            },
            {
                path: "/admin/content-categories/:id",
                name: "admin.ContentCategoryShow",
                component: AdminContentCategoryShow,
            },
            {
                path: "/admin/content-categories/create",
                name: "admin.ContentCategoryCreate",
                component: AdminContentCategoryCreate,
            },
            {
                path: "/admin/content-categories/:id/edit",
                name: "admin.ContentCategoryEdit",
                component: AdminContentCategoryEdit,
            },
            {
                path: "/admin/content-subcategories/:id",
                name: "admin.ContentSubcategoryShow",
                component: AdminContentSubcategoryShow,
            },
            {
                path: "/admin/content-subcategories/create",
                name: "admin.ContentSubcategoryCreate",
                component: AdminContentSubcategoryCreate,
            },
            {
                path: "/admin/content-subcategories/:id/edit",
                name: "admin.ContentSubcategoryEdit",
                component: AdminContentSubcategoryEdit,
            },
        ],
    },
    {
        path: "/system",
        name: "System",
        component: System,
        meta: {
            requiresAuth: true,
            role: "system_admin",
        },
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
