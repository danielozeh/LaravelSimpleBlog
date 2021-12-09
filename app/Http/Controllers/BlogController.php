<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogComment;
use App\Models\BlogLike;
use Validator;
use App\Helper;

use Carbon\Carbon;

use Illuminate\Support\Str;

/**
 * @author Daniel Ozeh hello@danielozeh.com.ng
 */

class BlogController extends Controller
{
    public function addBlogCategory(Request $request) {
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|between:2,100',
            //'thumbnail' => 'required|mimes:png,jpg|max:2048'
        ]);

        if($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
        }

        $thumbnail = "blog-category.png";

        if($request->file('thumbnail')) {
            $size = $request->file('thumbnail')->getSize();
            $thumbnail = Helper::generateCode(12);

            $save_image = $request->thumbnail->move(public_path('/blog/thumbnail'), $thumbnail);

            //$save_image = $request->thumbnail->store('public/blog/thumbnail');
            //$size = $request->file('thumbnail')->getSize();

            //$thumbnail = $request->file('thumbnail')->hashName();
        }

        $categories = BlogCategory::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title, '-'),
            'thumbnail' => $thumbnail,
            'user_id' => auth()->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully'
        ], 200);
    }

    public function editBlogCategory(Request $request, $id) {
        if(BlogCategory::where('id', $id)->exists()) {
            $validator = Validator::make($request->all(),[
                'title' => 'required|string|between:2,100',
            ]);

            if($validator->fails()) {
                return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
            }

            $thumbnail = "blog-category.png";

            if($request->file('thumbnail')) {
                $size = $request->file('thumbnail')->getSize();
                $thumbnail = Helper::generateCode(12);

                $save_image = $request->thumbnail->move(public_path('/blog/thumbnail'), $thumbnail);
            }

            $category = BlogCategory::find($id);
            $category->title = $request->title;
            $category->thumbnail = $thumbnail;
            $category->slug = Str::slug($request->title, '-');

            $category->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Category updated successfully'
            ], 200);
        }
        return response()->json([
            'status' => 'failed',
            'message' => 'Category does not exist'
        ], 400);
    }

    public function deleteBlogCategory(Request $request, $id) {
        if(BlogCategory::where('id', $id)->exists()) {
            $category = BlogCategory::find($id);
            $category->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Blog Category Deleted successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Category Does not exist'], 404);
        }
    }

    public function getBlogCategoryDetails(Request $request, $id) {
        if(BlogCategory::where('id', $id)->exists()) {
            $category = BlogCategory::with('user:id,first_name,last_name,email,role_id')->where('id', $id)->first();

            return response()->json([
                'status' => 'success',
                'message' => $category
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'Category does not exist'
        ], 400);
    }

    public function getAllBlogCategories() {
        $categories = BlogCategory::with('user:id,first_name,last_name,email,role_id')->orderBy('id', 'desc')->get();

        $image_path = Helper::imagePath(). '/blog/thumbnail/';

        return response()->json([
            'status' => 'success',
            'message' => $categories,
            'image_path' => $image_path
        ],200);
    }

    public function addBlogPost(Request $request) {
        $validator = Validator::make($request->all(),[
            'category_id' => 'required',
            'title' => 'required|string|between:2,1000',
            'content' => 'required|string|between:2,100000',
            'featured_image' => 'required|mimes:png,jpg|max:2048'
        ]);

        if($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
        }

        $featured_image = "blog-post.png";

        if($request->file('featured_image')) {
            //$save_image = $request->featured_image->store('public/blog');
            //$size = $request->file('featured_image')->getSize();

            //$featured_image = $request->file('featured_image')->hashName();

            $size = $request->file('featured_image')->getSize();
            $featured_image = Helper::generateCode(12);

            $save_image = $request->featured_image->move(public_path('/blog'), $featured_image);
        }

        $status = 1;

        if(auth()->user()->role_id == 3) {
            $status = 0;
        }

        $blogs = BlogPost::create([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title, '-'),
            'content' => $request->content,
            'status' => $status,
            'featured_image' => $featured_image,
            'user_id' => auth()->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Blog Post Added successfully!'
        ], 200);
    }

    public function editBlogPost(Request $request, $id) {
        if(BlogPost::where('id', $id)->exists()) {

            $validator = Validator::make($request->all(),[
                'category_id' => 'required',
                'title' => 'required|string|between:2,100',
                'content' => 'required|string|between:2,1000',
                'featured_image' => 'required|mimes:png,jpg|max:2048',
                'status' => 'required|int'
            ]);

            if($validator->fails()) {
                return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
            }

            //$featured_image = "";

            if($featured_image = $request->file('featured_image')) {
                $size = $request->file('featured_image')->getSize();
                $featured_image = Helper::generateCode(12);

                $save_image = $request->featured_image->move(public_path('/blog'), $featured_image);
            }

            $blogs = BlogPost::find($id);
            $blogs->category_id = $request->category_id;
            $blogs->title = $request->title;
            $blogs->slug = Str::slug($request->title, '-');
            $blogs->content = $request->content;
            $blogs->featured_image = $featured_image;
            $blogs->status = $request->status;

            $blogs->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Blog Post Updated successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Post Does not exist'], 404);
        }
    }

    public function deleteBlogPost(Request $request, $id) {
        if(BlogPost::where('id', $id)->exists()) {
            $blogs = BlogPost::find($id);
            $blogs->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Blog Post Deleted successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Post Does not exist'], 404);
        }
    }

    public function getAllBlogPost($status) {
        if(auth()->user()->role_id == 3) {
            $blogs = BlogPost::with('user:id,first_name,last_name,email')->with('category:id,title')->where('user_id', auth()->user()->id)->latest()->get();
        }
        else {
            if($status == "all") {
                $blogs = BlogPost::with('user:id,first_name,last_name,email')->with('category:id,title')->latest()->get();
            }
            else {
                $blogs = BlogPost::with('user:id,first_name,last_name,email')->with('category:id,title')->where('status', $status)->latest()->get();
            }
        }
        

        $image_path = Helper::imagePath(). '/blog';

        return response()->json([
            'status' => 'success',
            'message' => $blogs,
            'image_path' => $image_path
        ],200);
    }

    public function getBlogPostDetails($id) {
        if(BlogPost::where('id', $id)->exists()) {

            //$details = BlogPost::with('user:id,first_name,last_name,email')->with(['comments' => function($q) {
                //$q->orderBy('id', 'DESC');
            //}])->with('likes')->where('id', $id)->get();

            $details = BlogPost::with('user:id,first_name,last_name,email')->with(['comments' => function($q) {
                $q->leftJoin('users', function($join) {
                    $join
                    ->on('blog_comments.user_id', '=', 'users.id');
                })
                ->orderBy('blog_comments.id', 'DESC')
                ->select('blog_comments.*','users.first_name','users.last_name','users.email')
                ->get();
            }])->with(['likes' => function($q) {
                $q->leftJoin('users', function($join) {
                    $join
                    ->on('blog_likes.user_id', '=', 'users.id');
                })
                ->orderBy('blog_likes.id', 'DESC')
                ->select('blog_likes.*','users.first_name','users.last_name','users.email')
                ->get();
            }])
            ->where('id', $id)->first();
            
            $image_path = Helper::imagePath(). '/blog';

            $total_comments = BlogComment::with('blog')->where('blog_id', $id)->count('comment');
            $total_active_comments = BlogComment::with('blog')->where('blog_id', $id)->where('status', 1)->count('comment');
            $total_likes = BlogLike::with('blog')->where('blog_id', $id)->count();

            return response()->json([
                'status' => 'success',
                'message' => $details,
                'image_path' => $image_path,
                'total_comments' => $total_comments,
                'total_active_comments' => $total_active_comments,
                'total_likes' => $total_likes
            ], 200);
        }
        return response()->json([
            'status' => 'failed',
            'message' => 'Blog Post Not Found'
        ], 404);
    }

    public function getBlogPostDetailsBySlug($slug) {
        $details = BlogPost::with('user:id,first_name,last_name,email')->with(['comments' => function($q) {
            $q->leftJoin('users', function($join) {
                $join
                ->on('blog_comments.user_id', '=', 'users.id');
            })->leftJoin('user_profile', function($join) {
                $join
                ->on('blog_comments.user_id', '=', 'user_profile.user_id');
            })
            ->orderBy('blog_comments.id', 'DESC')
            ->select('blog_comments.*','users.first_name','users.last_name','users.email','user_profile.avatar','user_profile.phone_number','user_profile.gender')
            ->get();
        }])->with(['likes' => function($q) {
            $q->leftJoin('users', function($join) {
                $join
                ->on('blog_likes.user_id', '=', 'users.id');
            })
            ->orderBy('blog_likes.id', 'DESC')
            ->select('blog_likes.*','users.first_name','users.last_name','users.email')
            ->get();
        }])
        ->where('slug', $slug)->first();
        
        $image_path = Helper::imagePath(). '/blog';
        $user_image_path = Helper::imagePath(). '/users';

        $id = $details->id;

        $total_comments = BlogComment::with('blog')->where('blog_id', $id)->count('comment');
        $total_active_comments = BlogComment::with('blog')->where('blog_id', $id)->where('status', 1)->count('comment');
        $total_likes = BlogLike::with('blog')->where('blog_id', $id)->count();

        return response()->json([
            'status' => 'success',
            'message' => $details,
            'image_path' => $image_path,
            'user_image_path' => $user_image_path,
            'total_comments' => $total_comments,
            'total_active_comments' => $total_active_comments,
            'total_likes' => $total_likes
        ], 200);
    }

    public function getBlogPostByCategoryID($category_id, $status) {
        //$blogs = BlogPost::with('user')->where('category_id', $category_id)->orderBy('id', 'desc')->get();
        if($status == "all") {
            $blogs = BlogPost::with('user:id,first_name,last_name,email')->with('category:id,title')->where('category_id', $category_id)->latest()->get();
        }
        else {
            $blogs = BlogPost::with('user:id,first_name,last_name,email')->with('category:id,title')->where('category_id', $category_id)->where('status', $status)->latest()->get();
        }

        $image_path = Helper::imagePath(). '/storage/app/public/blog/';

        return response()->json([
            'status' => 'success',
            'message' => $blogs,
            'image_path' => $image_path
        ],200);
        
    }

    public function getBlogPostByCategorySlug(Request $request, $slug, $status) {
        $category = BlogCategory::where('slug', $slug)->first();
        $category_id = $category->id;

        if($status == "all") {
            $blogs = BlogPost::with('user:id,first_name,last_name,email')->with('category:id,title')->where('category_id', $category_id)->latest()->get();
        }
        else {
            if($request->has('current_page')) {
                $blogs = BlogPost::with('user:id,first_name,last_name,email')->with('category:id,title')->where('category_id', $category_id)->where('status', $status)->offset(($request->current_page - 1) * $request->limit)->limit($request->limit)->latest()->get();
            }
            else {
                $blogs = BlogPost::with('user:id,first_name,last_name,email')->with('category:id,title')->where('category_id', $category_id)->where('status', $status)->latest()->get();
            }
        }

        $image_path = Helper::imagePath(). '/blog';

        return response()->json([
            'status' => 'success',
            'message' => $blogs,
            'image_path' => $image_path,
            'blog_count' => count($blogs),
            'current_offset' => ($request->current_page - 1) * $request->limit
        ],200);
        
    }

    public function addBlogPostComment(Request $request) {
        $validator = Validator::make($request->all(),[
            'blog_id' => 'required|int',
            'comment' => 'required|string|between:2,10000'
        ]);

        if($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
        }

        $status = 1;

        if(auth()->user()->role_id == 3) {
            $status = 0;
        }

        $comment = BlogComment::create([
            'blog_id' => $request->blog_id,
            'comment' => $request->comment,
            'status' => $status,
            'user_id' => auth()->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Comment Added successfully!'
        ], 200);
    }

    public function editBlogPostComment(Request $request, $id) {
        if(BlogComment::where('id', $id)->exists()) {

            $validator = Validator::make($request->all(),[
                'comment' => 'required|string|between:2,10000',
                'status' => 'required|int'
            ]);

            if($validator->fails()) {
                return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
            }

            $comment = BlogComment::find($id);
            $comment->comment = $request->comment;
            $comment->status = $request->status;

            $comment->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Comment Updated successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Post Does not exist'], 404);
        }
    }

    public function deleteBlogPostComment(Request $request, $id) {
        if(BlogComment::where('id', $id)->exists()) {
            $blog_comment = BlogComment::find($id);
            $blog_comment->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Comment Deleted successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Comment Does not exist'], 404);
        }
    }

    public function postLike(Request $request, $blog_id) {
        if(BlogPost::where('id', $blog_id)->exists()) {

            if(BlogLike::where('blog_id', $blog_id)->where('user_id', auth()->user()->id)->exists()) {
                $remove_like = $this->removeBlogPostLikes($blog_id);
                return $remove_like;
            }
            $add_like = $this->addBlogPostLikes($blog_id);
            return $add_like;
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Post does not exist'], 404);
        }
    }

    public function addBlogPostLikes($blog_id) {
        $like = BlogLike::create([
            'blog_id' => $blog_id,
            'user_id' => auth('api')->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Like Added successfully!'
        ], 200);
    }

    public function removeBlogPostLikes($blog_id) {
        $blogs = BlogLike::where('blog_id', $blog_id)->where('user_id', auth('api')->user()->id);
        $blogs->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Like Deleted successfully!'
        ], 200);
    }

    public function makePostFeatured(Request $request, $id) {
        if(BlogPost::where('id', $id)->exists()) {
            $blogs = BlogPost::find($id);
            $blogs->featured = '1';
            $blogs->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Blog Post Featured successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Post Does not exist'], 404);
        }
    }

    public function unfeaturePost(Request $request, $id) {
        if(BlogPost::where('id', $id)->exists()) {
            $blogs = BlogPost::find($id);
            $blogs->featured = '0';
            $blogs->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Blog Post Unfeatured successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Post Does not exist'], 404);
        }
    }

    public function getAllFeaturedPosts() {
        $blogs = BlogPost::with('user:id,first_name,last_name,email')->where('featured', 1)->orderBy('id', 'desc')->get();

        $image_path = Helper::imagePath(). '/blog';

        return response()->json([
            'status' => 'success',
            'message' => $blogs,
            'image_path' => $image_path
        ],200);
    }

    public function pinPost(Request $request, $id) {
        if(BlogPost::where('id', $id)->exists()) {
            $blogs = BlogPost::find($id);
            $blogs->pinned = '1';
            $blogs->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Blog Post Pinned successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Post Does not exist'], 404);
        }
    }

    public function unpinPost(Request $request, $id) {
        if(BlogPost::where('id', $id)->exists()) {
            $blogs = BlogPost::find($id);
            $blogs->pinned = '0';
            $blogs->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Blog Post Unpinned successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Post Does not exist'], 404);
        }
    }

    public function getAllPinnedPosts() {
        $blogs = BlogPost::with('user:id,first_name,last_name,email')->where('pinned', 1)->orderBy('id', 'desc')->get();

        $image_path = Helper::imagePath(). '/blog';

        return response()->json([
            'status' => 'success',
            'message' => $blogs,
            'image_path' => $image_path
        ],200);
    }

    public function moderateBlogPost(Request $request, $id) {
        if(BlogPost::where('id', $id)->exists()) {

            $validator = Validator::make($request->all(),[
                'status' => 'required|int'
            ]);

            if($validator->fails()) {
                return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
            }

            if(auth()->user()->role_id == 3) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized Access'], 401);
            }

            $blogs = BlogPost::find($id);
            $blogs->status = $request->status;

            $blogs->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Blog Post Moderated successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Post Does not exist'], 404);
        }
    }

    public function moderateBlogComment(Request $request, $id) {
        if(BlogComment::where('id', $id)->exists()) {

            $validator = Validator::make($request->all(),[
                'status' => 'required|int'
            ]);

            if($validator->fails()) {
                return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
            }

            if(auth()->user()->role_id == 3) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized Access'], 401);
            }

            $blogs = BlogComment::find($id);
            $blogs->status = $request->status;

            $blogs->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Blog Comment Moderated successfully!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Blog Post Does not exist'], 404);
        }
    }
}
