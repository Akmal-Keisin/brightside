<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Catalog;
use App\Http\Controllers\Checkout;
use App\Http\Controllers\Learning;
use Illuminate\Support\Facades\Route;

Route::get('/', Catalog\HomeController::class)->name('home');

Route::get('courses', [Catalog\CourseController::class, 'index'])->name('courses.index');
Route::get('courses/{course:slug}', [Catalog\CourseController::class, 'show'])->name('courses.show');
Route::get('courses/{course:slug}/preview/{content}', [Catalog\CourseController::class, 'preview'])->name('courses.preview');
Route::get('sitemap.xml', Catalog\SitemapController::class)->name('sitemap');

Route::inertia('terms', 'legal/Terms')->name('legal.terms');
Route::inertia('privacy', 'legal/Privacy')->name('legal.privacy');
Route::inertia('refund-policy', 'legal/RefundPolicy')->name('legal.refund');

Route::post('webhooks/midtrans', Checkout\MidtransWebhookController::class)->name('webhooks.midtrans');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', Learning\DashboardController::class)->name('dashboard');
    Route::get('my-courses', Learning\MyCoursesController::class)->name('learning.courses.index');

    Route::get('learning/{course:slug}', [Learning\PlayerController::class, 'resume'])->name('learning.resume');
    Route::get('learning/{course:slug}/{content}', [Learning\PlayerController::class, 'show'])->name('learning.show');
    Route::post('learning/{course:slug}/{content}/complete', [Learning\ProgressController::class, 'complete'])->name('learning.complete');
    Route::post('learning/{course:slug}/{content}/progress', [Learning\ProgressController::class, 'track'])->name('learning.progress');

    Route::post('learning/{course:slug}/{content}/quiz/attempts', [Learning\QuizAttemptController::class, 'store'])->name('learning.quiz.start');
    Route::get('learning/quiz-attempts/{attempt}', [Learning\QuizAttemptController::class, 'show'])->name('learning.quiz.show');
    Route::post('learning/quiz-attempts/{attempt}/submit', [Learning\QuizAttemptController::class, 'submit'])->name('learning.quiz.submit');

    Route::get('checkout/{course:slug}', [Checkout\CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('checkout/{course:slug}', [Checkout\CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('courses/{course:slug}/enroll', [Checkout\FreeEnrollmentController::class, 'store'])->name('courses.enroll');

    Route::get('checkout/{order:order_number}/status', [Checkout\PaymentStatusController::class, 'show'])->name('checkout.status');
    Route::get('orders', [Checkout\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order:order_number}', [Checkout\OrderController::class, 'show'])->name('orders.show');

    if (app()->environment('local', 'testing')) {
        Route::post('dev/orders/{order:order_number}/pay', Checkout\DevelopmentPaymentController::class)
            ->name('dev.orders.pay');
    }

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['admin', 'admin.2fa'])
        ->group(function (): void {
            Route::get('/', Admin\DashboardController::class)->name('dashboard');

            Route::get('courses', [Admin\CourseController::class, 'index'])->name('courses.index');
            Route::get('courses/create', [Admin\CourseController::class, 'create'])->name('courses.create');
            Route::post('courses', [Admin\CourseController::class, 'store'])->name('courses.store');
            Route::get('courses/{course:slug}/edit', [Admin\CourseController::class, 'edit'])->name('courses.edit');
            Route::put('courses/{course:slug}', [Admin\CourseController::class, 'update'])->name('courses.update');
            Route::delete('courses/{course:slug}', [Admin\CourseController::class, 'destroy'])->name('courses.destroy');
            Route::post('courses/{course:slug}/publish', [Admin\PublishCourseController::class, 'store'])->name('courses.publish');
            Route::post('courses/{course:slug}/unpublish', [Admin\PublishCourseController::class, 'destroy'])->name('courses.unpublish');
            Route::post('courses/{course:slug}/archive', [Admin\ArchiveCourseController::class, 'store'])->name('courses.archive');
            Route::get('courses/{course:slug}/preview', Admin\CoursePreviewController::class)->name('courses.preview');
            Route::put('courses/{course:slug}/curriculum/order', [Admin\CurriculumOrderController::class, 'update'])->name('curriculum.reorder');

            Route::post('courses/{course:slug}/chapters', [Admin\ChapterController::class, 'store'])->name('chapters.store');
            Route::put('chapters/{chapter}', [Admin\ChapterController::class, 'update'])->name('chapters.update');
            Route::delete('chapters/{chapter}', [Admin\ChapterController::class, 'destroy'])->name('chapters.destroy');

            Route::post('chapters/{chapter}/contents', [Admin\ContentController::class, 'store'])->name('contents.store');
            Route::put('contents/{content}', [Admin\ContentController::class, 'update'])->name('contents.update');
            Route::delete('contents/{content}', [Admin\ContentController::class, 'destroy'])->name('contents.destroy');
            Route::put('contents/{content}/quiz', [Admin\QuizController::class, 'update'])->name('quizzes.update');
            Route::get('contents/{content}/quiz/attempts', Admin\QuizReportController::class)->name('quizzes.report');
            Route::post('markdown/preview', Admin\MarkdownPreviewController::class)->name('markdown.preview');

            Route::get('categories', [Admin\CategoryController::class, 'index'])->name('categories.index');
            Route::post('categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
            Route::put('categories/{category:slug}', [Admin\CategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category:slug}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

            Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order:order_number}', [Admin\OrderController::class, 'show'])->name('orders.show');
            Route::post('orders/{order:order_number}/refund', [Admin\OrderController::class, 'refund'])->name('orders.refund');
            Route::post('orders/{order:order_number}/resend-receipt', [Admin\OrderController::class, 'receipt'])->name('orders.receipt');

            Route::get('members', [Admin\MemberController::class, 'index'])->name('members.index');
            Route::get('members/{user}', [Admin\MemberController::class, 'show'])->name('members.show');
            Route::post('members/{user}/enrollments', [Admin\EnrollmentController::class, 'store'])->name('enrollments.store');
            Route::delete('enrollments/{enrollment}', [Admin\EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
        });

    require __DIR__.'/settings.php';
});
