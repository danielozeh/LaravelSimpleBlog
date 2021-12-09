<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


/**
 * @author Daniel Ozeh hello@danielozeh.com.ng
 */


///////////////////////////////////////
////////////////USER AUTH ////////////
/////////////////////////////////////
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']); 
});

Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'auth'

], function ($router) {
    Route::post('/refresh', [AuthController::class, 'refresh']); 
});

Route::post('/auth/verify-user/{email}', [AuthController::class, 'verifyUser']);


Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'user'

], function ($router) {
    Route::get('/profile', [AuthController::class, 'userProfile']); 
    Route::get('/user-profile/{id}', [UserController::class, 'viewUserProfile']);  
    Route::post('/update-user-profile/{id}', [UserController::class, 'updateUserProfile']);
    Route::post('/update-profile/', [UserController::class, 'updateProfile']);
    Route::post('/update-profile-picture', [UserController::class, 'updateProfilePicture']);
    Route::get('/get-all', [UserController::class, 'getAllUsers']); 
    
    Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('/change-password', [UserController::class, 'changePassword']);

    Route::post('/block-user/{id}', [UserController::class, 'blockUser']);
    Route::post('/unblock-user/{id}', [UserController::class, 'unblockUser']);
});


/////////////////////////////////
///////////DASHBOARD ROUTE//////////
////////////////////////////////
Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'dashboard'

], function ($router) {
    Route::get('/', [DashboardController::class, 'getDashboardInfo']);
});




/////////////////////////////////////
////////BLOG ROUTES/////////////
//////////////////////////////////
Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'blog'
], function ($router) {
    Route::post('/category/add',[BlogController::class, 'addBlogCategory']);
    Route::post('/category/edit/{id}',[BlogController::class, 'editBlogCategory']);
    Route::get('/category/details/{id}',[BlogController::class, 'getBlogCategoryDetails']);
    Route::delete('/category/delete/{id}',[BlogController::class, 'deleteBlogCategory']);

    Route::post('/post/add',[BlogController::class, 'addBlogPost']);
    Route::post('/post/edit/{id}',[BlogController::class, 'editBlogPost']);
    Route::delete('/post/delete/{id}',[BlogController::class, 'deleteBlogPost']);

    Route::post('/post/comment/add',[BlogController::class, 'addBlogPostComment']);
    Route::put('/post/comment/edit/{comment_id}',[BlogController::class, 'editBlogPostComment']);
    Route::delete('/post/comment/delete/{comment_id}',[BlogController::class, 'deleteBlogPostComment']);

    Route::post('/post/like/post/{blog_id}',[BlogController::class, 'postLike']);
    
    Route::post('/make-featured/{id}', [BlogController::class, 'makePostFeatured']);
    Route::put('/unfeature-post/{id}', [BlogController::class, 'unfeaturePost']);

    Route::post('/pin-post/{id}', [BlogController::class, 'pinPost']);
    Route::put('/unpin-post/{id}', [BlogController::class, 'unpinPost']);

    Route::put('/post/moderate/{id}', [BlogController::class, 'moderateBlogPost']);

    Route::put('/comment/moderate/{id}', [BlogController::class, 'moderateBlogComment']);
});
Route::get('/blog/category/get-all',[BlogController::class, 'getAllBlogCategories']);

Route::get('/blog/post/get-all/{status}',[BlogController::class, 'getAllBlogPost']);
Route::get('/blog/post/details/{id}',[BlogController::class, 'getBlogPostDetails']);
Route::get('/blog/post/details-by-slug/{slug}',[BlogController::class, 'getBlogPostDetailsBySlug']);
Route::get('/blog/post/category/{category_id}/{status}',[BlogController::class, 'getBlogPostByCategoryID']);
Route::post('/blog/post/category-slug/{slug}/{status}',[BlogController::class, 'getBlogPostByCategorySlug']);

Route::get('/blog/post/get-featured',[BlogController::class, 'getAllFeaturedPosts']);
Route::get('/blog/post/get-pinned',[BlogController::class, 'getAllPinnedPosts']);