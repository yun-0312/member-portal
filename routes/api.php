<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController as PublicHomeController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Layout\HeaderController as PublicHeaderController;
use App\Http\Controllers\Admin\Layout\HeaderController as AdminHeaderController;
use App\Http\Controllers\NoticeController as PublicNoticeController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\ContentController as PublicContentController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\WorkshopController as PublicWorkshopController;
use App\Http\Controllers\Admin\WorkshopController as AdminWorkshopController;
use App\Http\Controllers\VideoController as PublicVideoController;
use App\Http\Controllers\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\FaqController as PublicFaqController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\ScheduleController as PublicScheduleController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\UserController as PublicUserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\RoleTargetableController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\MedicalInstitutionController as AdminMedicalInstitutionController;
use App\Http\Controllers\MedicalInstitutionController as PublicMedicalInstitutionController;
use App\Http\Controllers\Admin\ContentCategoryController;
use App\Http\Controllers\Admin\ContentSubcategoryController;
use App\Http\Controllers\Admin\FaqCategoryController;
use App\Http\Controllers\Admin\NoticeCategoryController;
use App\Http\Controllers\Admin\ScheduleCategoryController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\ManagementController;
use App\Http\Controllers\Admin\NoticeRolesController;
use App\Http\Controllers\Admin\ContentRolesController;
use App\Http\Controllers\Admin\SystemAdminHomeController;
use App\Http\Controllers\Admin\VideoRolesController;
use Illuminate\Http\Request;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register-medical-staff', [AuthController::class, 'registerMedicalStaff'])
    ->middleware('throttle:5,1')
    ->name('auth.register-medical-staff');
Route::get('/medical-institutions', [AuthController::class, 'medicalInstitutions'])
    ->name('auth.medical-institutions');
Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')
    ->name('auth.logout');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum')
    ->name('auth.me');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [ResetPasswordController::class, 'reset']);
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $frontendUrl = config('app.frontend_url', 'http://localhost');

    // 1. URLの署名（有効期限・改ざん）チェック
    if (! $request->hasValidSignature()) {
        return redirect($frontendUrl . '/?verified=error&message=' . urlencode('URLの有効期限が切れているか、無効なリンクです。'));
    }

    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return redirect($frontendUrl . '/?verified=error&message=' . urlencode('不正なアクセスです。'));
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return redirect($frontendUrl . '/?verified=success_register');
})->name('verification.verify');

Route::get('/email/verify-new/{id}/{hash}', function (Request $request, $id, $hash) {
    $frontendUrl = config('app.frontend_url', 'http://localhost');
    if (! $request->hasValidSignature()) {
        return response()->json(['message' => '不正なアクセスです（URLの期限切れまたは改ざん）'], 403);
    }

    $user = User::findOrFail($id);
    $newEmail = urldecode($request->query('email'));

    if (! hash_equals((string) $hash, sha1($newEmail))) {
        return response()->json(['message' => '不正なアクセスです'], 403);
    }

    $user->email = $newEmail;
    $user->email_verified_at = now();
    $user->save();

    return redirect($frontendUrl . '/?verified=success');
})->name('verification.verify.new');


Route::middleware(['auth:sanctum'])->group(function () {
    //Home
    Route::get('/home', [PublicHomeController::class, 'index'])
        ->name('home.index');

    //header
    Route::get('/header', [PublicHeaderController::class, 'index'])
        ->name('header');

    // Notice
    Route::get('/notices', [PublicNoticeController::class, 'index'])
        ->name('notices.index');

    // Content
    Route::prefix('contents')->group(function () {
        Route::get('/', [PublicContentController::class, 'index'])
            ->name('contents.index');
        Route::get('/years', [PublicContentController::class, 'years'])
            ->name('contents.years');
        Route::get('/{content}', [PublicContentController::class, 'show'])
            ->name('contents.show');
    });

    // Workshop
    Route::get('/workshops', [PublicWorkshopController::class, 'index'])
            ->name('workshops.index');

    // Video
        Route::get('/videos', [PublicVideoController::class, 'index'])
            ->name('videos.index');

    // FAQ
    Route::prefix('faqs')->group(function () {
        Route::get('/', [PublicFaqController::class, 'index'])
            ->name('faqs.index');
        Route::get('/export', [PublicFaqController::class, 'export'])
            ->name('faqs.export');
    });

    // Schedule
    Route::get('/schedules', [PublicScheduleController::class, 'index'])
            ->name('schedules.index');

    // MedicalInstitution（閲覧）
    Route::prefix('medical-institutions')->group(function () {
        Route::get('/{medicalInstitution}', [PublicMedicalInstitutionController::class, 'show'])
            ->name('medical-institutions.show');
        Route::get('/{medicalInstitution}/users', [PublicMedicalInstitutionController::class, 'users'])
            ->name('medical-institutions.users');
    });

    // User
    Route::prefix('users')->group(function () {
        Route::get('/{user}', [PublicUserController::class, 'show'])
            ->name('users.show');
        Route::post('/{user}/retire', [PublicUserController::class, 'retire'])
            ->middleware('permission_or:user.update,medical_institution.update')
            ->name('users.retire');
        Route::post('/password', [PublicUserController::class, 'changePassword']);
        Route::post('/{user}/approve', [PublicUserController::class, 'approve'])
            ->middleware('permission_or:user.update,medical_institution.update')
            ->name('users.approve');
        Route::post('/{user}/reject', [PublicUserController::class, 'reject'])
            ->middleware('permission_or:user.update,medical_institution.update')
            ->name('users.reject');
    });

});

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    //home
    Route::get('/home', [AdminHomeController::class, 'index'])
        ->middleware('permission:content.update')
        ->name('admin.home.index');
    Route::get('/management', [ManagementController::class, 'index'])
        ->middleware('permission:content.update')
        ->name('admin.management.index');
    //header
    Route::get('/header', [AdminHeaderController::class, 'index'])
        ->middleware('permission:content.update')
        ->name('admin.header');

    // Notice
    Route::prefix('notices')->group(function () {
        Route::get('/', [AdminNoticeController::class, 'index'])
            ->middleware('permission:notice.update')
            ->name('admin.notices.index');
        Route::get('/{notice}', [AdminNoticeController::class, 'show'])
            ->middleware('permission:notice.update')
            ->name('admin.notices.show');
        Route::post('/', [AdminNoticeController::class, 'store'])
            ->middleware('permission:notice.create')
            ->name('admin.notices.store');
        //post(_method:PATCH)
        Route::patch('/{notice}', [AdminNoticeController::class, 'update'])
            ->middleware('permission:notice.update')
            ->name('admin.notices.update');
        Route::delete('/{notice}', [AdminNoticeController::class, 'destroy'])
            ->middleware('permission:notice.delete')
            ->name('admin.notice.destroy');
        Route::post('/{notice}/roles', [NoticeRolesController::class, 'store'])
            ->middleware('permission:notice.update')
            ->name('admin.notices.roles.store');
        Route::delete('/{notice}/roles/{role}', [NoticeRolesController::class, 'destroy'])
            ->middleware('permission:notice.update')
            ->name('admin.notices.roles.destroy');
    });


    // Content
    Route::prefix('contents')->group(function () {
        Route::get('/', [AdminContentController::class, 'index'])
            ->middleware('permission:content.update')
            ->name('admin.contents.index');
        Route::get('/{content}', [AdminContentController::class, 'show'])
            ->middleware('permission:content.update')
            ->name('admin.contents.show');
        Route::post('/', [AdminContentController::class, 'store'])
            ->middleware('permission:content.create')
            ->name('admin.contents.store');
        //post(_method:PATCH)
        Route::patch('/{content}', [AdminContentController::class, 'update'])
            ->middleware('permission:content.update')
            ->name('admin.contents.update');
        Route::delete('/{content}', [AdminContentController::class, 'destroy'])
            ->middleware('permission:content.delete')
            ->name('admin.contents.destroy');
        Route::post('/{content}/roles', [ContentRolesController::class, 'store'])
            ->middleware('permission:content.update')
            ->name('admin.contents.roles.store');
        Route::delete('/{content}/roles/{role}', [ContentRolesController::class, 'destroy'])
            ->middleware('permission:content.update')
            ->name('admin.contents.roles.destroy');
    });

    // Workshop
    Route::prefix('workshops')->group(function () {
        Route::get('/', [AdminWorkshopController::class, 'index'])
            ->middleware('permission:workshop.update')
            ->name('admin.workshops.index');
        Route::get('/{workshop}', [AdminWorkshopController::class, 'show'])
            ->middleware('permission:workshop.update')
            ->name('admin.workshops.show');
        Route::post('/', [AdminWorkshopController::class, 'store'])
            ->middleware('permission:workshop.create')
            ->name('admin.workshops.store');
        Route::put('/{workshop}', [AdminWorkshopController::class, 'update'])
            ->middleware('permission:workshop.update')
            ->name('admin.workshops.update');
        Route::delete('/{workshop}', [AdminWorkshopController::class, 'destroy'])
            ->middleware('permission:workshop.delete')
            ->name('admin.workshops.destroy');
    });

    // Video
    Route::prefix('videos')->group(function () {
        Route::get('/', [AdminVideoController::class, 'index'])
            ->middleware('permission:video.update')
            ->name('admin.videos.index');
        Route::get('/{video}', [AdminVideoController::class, 'show'])
            ->middleware('permission:video.update')
            ->name('admin.videos.show');
        Route::post('/', [AdminVideoController::class, 'store'])
            ->middleware('permission:video.create')
            ->name('admin.videos.store');
        //post(_method:PATCH)
        Route::patch('/{video}', [AdminVideoController::class, 'update'])
            ->middleware('permission:video.update')
            ->name('admin.videos.update');
        Route::delete('/{video}', [AdminVideoController::class, 'destroy'])
            ->middleware('permission:video.delete')
            ->name('admin.videos.destroy');
        Route::post('/{video}/roles', [VideoRolesController::class, 'store'])
            ->middleware('permission:video.update')
            ->name('admin.videos.roles.store');
        Route::delete('/{video}/roles/{role}', [VideoRolesController::class, 'destroy'])
            ->middleware('permission:video.update')
            ->name('admin.videos.roles.destroy');
    });

    // FAQ
    Route::prefix('faqs')->group(function () {
        Route::get('/', [AdminFaqController::class, 'index'])
            ->middleware('permission:faq.update')
            ->name('admin.faqs.index');
        Route::get('/{faq}', [AdminFaqController::class, 'show'])
            ->middleware('permission:faq.update')
            ->name('admin.faqs.show');
        Route::post('/', [AdminFaqController::class, 'store'])
            ->middleware('permission:faq.create')
            ->name('admin.faqs.store');
        Route::put('/{faq}', [AdminFaqController::class, 'update'])
            ->middleware('permission:faq.update')
            ->name('admin.faqs.update');
        Route::delete('/{faq}', [AdminFaqController::class, 'destroy'])
            ->middleware('permission:faq.delete')
            ->name('admin.faqs.destroy');
        Route::post('/import', [AdminFaqController::class, 'import'])
            ->middleware('permission:faq.create')
            ->name('admin.faqs.import');
    });

    //FaqCategory
    Route::prefix('faq-categories')->group(function () {
        Route::get('/', [FaqCategoryController::class, 'index'])
            ->middleware('permission:category.update')
            ->name('admin.faq-categories.index');
        Route::get('/{category}', [FaqCategoryController::class, 'show'])
            ->middleware('permission:category.update')
            ->name('admin.faq-categories.show');
        Route::post('/', [FaqCategoryController::class, 'store'])
            ->middleware('permission:category.create')
            ->name('admin.faq-categories.store');
        Route::put('/{category}', [FaqCategoryController::class, 'update'])
            ->middleware('permission:category.update')
            ->name('admin.faq-categories.update');
        Route::delete('/{category}', [FaqCategoryController::class, 'destroy'])
            ->middleware('permission:category.delete')
            ->name('admin.faq-categories.destroy');
    });

    //Schedule
    Route::prefix('schedules')->group(function () {
        Route::get('/', [AdminScheduleController::class, 'index'])
            ->middleware('permission:schedule.update')
            ->name('admin.schedules.index');
        Route::get('/{schedule}', [AdminScheduleController::class, 'show'])
            ->middleware('permission:schedule.update')
            ->name('admin.schedules.show');
        Route::post('/', [AdminScheduleController::class, 'store'])
            ->middleware('permission:schedule.create')
            ->name('admin.schedules.store');
        Route::put('/{schedule}', [AdminScheduleController::class, 'updateSchedule'])
            ->middleware('permission:schedule.update')
            ->name('admin.schedules.update');
        Route::delete('/{schedule}', [AdminScheduleController::class, 'destroy'])
            ->middleware('permission:schedule.delete')
            ->name('admin.schedules.destroy');
    });
    Route::prefix('schedule-occurrences')->group(function () {
        Route::get('/{occurrence}', [AdminScheduleController::class, 'showOccurrence'])
            ->middleware('permission:schedule.update')
            ->name('admin.schedule-occurrences.show');
        Route::put('/{occurrence}', [AdminScheduleController::class, 'updateOccurrence'])
            ->middleware('permission:schedule.update')
            ->name('admin.schedule-occurrences.update');
        Route::delete('/{occurrence}', [AdminScheduleController::class, 'destroyOccurrence'])
            ->middleware('permission:schedule.delete')
            ->name('admin.schedule-occurrences.destroy');
    });

    //ScheduleCategory
    Route::prefix('schedule-categories')->group(function () {
        Route::get('/', [ScheduleCategoryController::class, 'index'])
            ->middleware('permission:category.update')
            ->name('admin.schedule-categories.index');
        Route::get('/{category}', [scheduleCategoryController::class, 'show'])
            ->middleware('permission:category.update')
            ->name('admin.schedule-categories.show');
        Route::post('/', [scheduleCategoryController::class, 'store'])
            ->middleware('permission:category.create')
            ->name('admin.schedule-categories.store');
        Route::put('/{category}', [scheduleCategoryController::class, 'update'])
            ->middleware('permission:category.update')
            ->name('admin.schedule-categories.update');
        Route::delete('/{category}', [scheduleCategoryController::class, 'destroy'])
            ->middleware('permission:category.delete')
            ->name('admin.schedule-categories.destroy');
    });

    // User
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])
            ->name('admin.users.index');
        Route::get('/pending', [AdminUserController::class, 'pending'])
            ->name('admin.users.pending');
        Route::get('/export', [AdminUserController::class, 'export'])
            ->middleware('permission:user.update')
            ->name('admin.users.export');
        Route::get('/options', [AdminUserController::class, 'options'])
            ->middleware('permission:user.update')
            ->name('admin.users.options');
        Route::get('/representatives', [AdminUserController::class, 'representatives'])
            ->middleware('permission:user.update')
            ->name('admin.users.options');
        Route::get('/{user}', [AdminUserController::class, 'show'])
            ->name('admin.users.show');
        Route::post('/', [AdminUserController::class, 'store'])
            ->middleware('permission:user.create')
            ->name('admin.users.store');
        Route::put('/{user}', [AdminUserController::class, 'update'])
            ->middleware('permission_or:user.update,medical_institution.update')
            ->name('admin.users.update');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])
            ->middleware('permission:user.delete')
            ->name('admin.users.destroy');

    });

    // Role
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])
            ->middleware('permission_or:role.update,permission.update')
            ->name('admin.roles.index');
        Route::get('/{role}', [RoleController::class, 'show'])
            ->middleware('permission:role.update')
            ->name('admin.roles.show');
        Route::post('/', [RoleController::class, 'store'])
            ->middleware('permission:role.create')
            ->name('admin.roles.store');
        Route::put('/{role}', [RoleController::class, 'update'])
            ->middleware('permission:role.update')
            ->name('admin.roles.update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:role.delete')
            ->name('admin.roles.destroy');
    });

    // Room
    Route::prefix('rooms')->group(function () {
        Route::get('/', [RoomController::class, 'index'])
            ->middleware('permission:room.update')
            ->name('admin.rooms.index');
        Route::get('/{room}', [RoomController::class,'show'])
            ->middleware('permission:room.update')
            ->name('admin.rooms.show');
        Route::post('/', [RoomController::class, 'store'])
            ->middleware('permission:room.create')
            ->name('admin.rooms.store');
        Route::put('/{room}', [RoomController::class, 'update'])
            ->middleware('permission:room.update')
            ->name('admin.rooms.update');
        Route::delete('/{room}', [RoomController::class, 'destroy'])
            ->middleware('permission:room.delete')
            ->name('admin.rooms.destroy');
    });

    // MedicalInstitution
    Route::prefix('medical-institutions')->group(function () {
        Route::get('/', [AdminMedicalInstitutionController::class, 'index'])
            ->name('admin.medical-institutions.index');
        Route::get('/export', [AdminMedicalInstitutionController::class, 'export'])
            ->middleware(('permission:medical_institution.update'))
            ->name('admin.medical-institutions.export');
        Route::get('/{medicalInstitution}', [AdminMedicalInstitutionController::class, 'show'])
            ->name('admin.medical-institutions.show');
        Route::post('/', [AdminMedicalInstitutionController::class, 'store'])
            ->middleware('permission:medical_institution.create')
            ->name('admin.medical-institutions.store');
        Route::put('/{medicalInstitution}', [AdminMedicalInstitutionController::class, 'update'])
            ->middleware('permission:medical_institution.update')
            ->name('admin.medical-institutions.update');
        Route::delete('/{medicalInstitution}', [AdminMedicalInstitutionController::class, 'destroy'])
            ->middleware('permission:medical_institution.delete')
            ->name('admin.medical-institutions.destroy');
        Route::get('/{medicalInstitution}/users', [AdminMedicalInstitutionController::class, 'users'])
            ->name('admin.medical-institutions.users');
    });

    //ContentCategory
    Route::prefix('content-categories')->group(function () {
        Route::get('/', [ContentCategoryController::class, 'index'])
            ->middleware('permission:category.update')
            ->name('admin.content-categories.index');
        Route::get('/{category}', [ContentCategoryController::class, 'show'])
            ->middleware('permission:category.update')
            ->name('admin.content-categories.show');
        Route::post('/', [ContentCategoryController::class, 'store'])
            ->middleware('permission:category.create')
            ->name('admin.content-categories.store');
        Route::put('/{category}', [ContentCategoryController::class, 'update'])
            ->middleware('permission:category.update')
            ->name('admin.content-categories.update');
        Route::delete('/{category}', [ContentCategoryController::class, 'destroy'])
            ->middleware('permission:category.delete')
            ->name('admin.content-categories.destroy');
    });

    //ContentSubcategory
    Route::prefix('content-subcategories')->group(function () {
        Route::get('/', [ContentSubcategoryController::class, 'index'])
            ->middleware('permission:category.update')
            ->name('admin.content-subcategories.index');
        Route::get('/{subcategory}', [ContentSubcategoryController::class, 'show'])
            ->middleware('permission:category.update')
            ->name('admin.content-subcategories.show');
        Route::post('/', [ContentSubcategoryController::class, 'store'])
            ->middleware('permission:category.create')
            ->name('admin.content-subcategories.store');
        Route::put('/{subcategory}', [ContentSubcategoryController::class, 'update'])
            ->middleware('permission:category.update')
            ->name('admin.content-subcategories.update');
        Route::delete('/{subcategory}', [ContentSubcategoryController::class, 'destroy'])
            ->middleware('permission:category.delete')
            ->name('admin.content-subcategories.destroy');
    });

    //file
    Route::prefix('files')->group(function () {
        Route::get('/notice/{notice}', [FileController::class, 'listNoticeFiles'])
            ->middleware('permission:notice.update')
            ->name('admin.files.notice.index');

        Route::get('/content/{content}', [FileController::class, 'listContentFiles'])
            ->middleware('permission:content.update')
            ->name('admin.files.content.index');

        Route::get('/video/{video}', [FileController::class, 'listVideoFiles'])
            ->middleware('permission:video.update')
            ->name('admin.files.video.index');

        Route::post('/notice/{notice}', [FileController::class, 'uploadToNotice'])
            ->middleware('permission:notice.update')
            ->name('admin.files.upload.notice');

        Route::post('/content/{content}', [FileController::class, 'uploadToContent'])
            ->middleware('permission:content.update')
            ->name('admin.files.upload.content');

        Route::post('/videos/{video}', [FileController::class, 'uploadToVideo'])
            ->middleware('permission:video.update')
            ->name('admin.files.upload.video');

        Route::get('/{file}/download', [FileController::class, 'download'])
            ->name('admin.files.download');

        Route::delete('/{file}', [FileController::class, 'destroy'])
            ->middleware('permission:content.update')
            ->name('admin.files.destroy');
    });

    //Content、NoticeのCategoryにroleを結びつける
    Route::get('{type}/{id}/roles', [RoleTargetableController::class, 'index']);
    Route::post('{type}/{id}/roles', [RoleTargetableController::class, 'store']);
    Route::delete('{type}/{id}/roles/{role}', [RoleTargetableController::class, 'destroy']);

    //system-admin home
    Route::prefix('system')->group(function () {
        Route::get('/', [SystemAdminHomeController::class, 'index'])
            ->middleware('permission:permission.update')
            ->name('admin.system.index');
    });

    // Permission
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])
            ->middleware('permission:permission.update')
            ->name('admin.permissions.index');
        Route::get('/{permission}', [PermissionController::class, 'show'])
            ->middleware('permission:permission.update')
            ->name('admin.permissions.show');
        Route::post('/', [PermissionController::class, 'store'])
            ->middleware('permission:permission.create')
            ->name('admin.permissions.store');
        Route::put('/{permission}', [PermissionController::class, 'update'])
            ->middleware('permission:permission.update')
            ->name('admin.permissions.update');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])
            ->middleware('permission:permission.delete')
            ->name('admin.permissions.destroy');
    });

    //RolePermission
    Route::prefix('roles')->group(function () {
        Route::get('/{role}/permissions', [RolePermissionController::class, 'index'])
            ->middleware('permission:permission.update')
            ->name('admin.role-permissions.index');
        Route::post('/{role}/permissions', [RolePermissionController::class, 'store'])
            ->middleware('permission:permission.update')
            ->name('admin.role-permissions.store');
        Route::delete('/{role}/permissions/{permission}', [RolePermissionController::class, 'destroy'])
                    ->middleware('permission:permission.update')
                    ->name('admin.role-permissions.destroy');
    });

    // NoticeCategory
    Route::prefix('notice-categories')->group(function () {
        Route::get('/', [NoticeCategoryController::class, 'index'])
            ->middleware('permission:notice_category.update')
            ->name('admin.notice-categories.index');
        Route::get('/{noticeCategory}', [NoticeCategoryController::class, 'show'])
            ->middleware('permission:notice_category.update')
            ->name('admin.notice-categories.show');
        Route::post('/', [NoticeCategoryController::class, 'store'])
            ->middleware('permission:notice_category.create')
            ->name('admin.notice-categories.store');
        Route::put('/{noticeCategory}', [NoticeCategoryController::class, 'update'])
            ->middleware('permission:notice_category.update')
            ->name('admin.notice-categories.update');
        Route::delete('/{noticeCategory}', [NoticeCategoryController::class, 'destroy'])
            ->middleware('permission:notice_category.delete')
            ->name('admin.notice-categories.destroy');
    });

});
