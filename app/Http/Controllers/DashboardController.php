<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BlogPost;
use App\Models\BlogComment;

class DashboardController extends Controller
{
    public function getDashboardInfo() {
        $totaBlogPost = 0;
        $totalBlogComments = 0;
        $activeBlogPost = 0;
        $inactiveBlogPost = 0;
        
        if(auth()->user()->role_id == 2 || auth()->user()->role_id == 1) {
            $totaBlogPost = BlogPost::count();
            $totalBlogComments = BlogComment::count();
            $activeBlogPost = BlogPost::where('status', 1)->count();
            $inactiveBlogPost = BlogPost::where('status', 0)->count();
        }

        return response()->json(['totalBlogPost' => $totaBlogPost, 'totalBlogComments' => $totalBlogComments, 'activeBlogPost' => $activeBlogPost, 'inactiveBlogPost' => $inactiveBlogPost],200);
    }
}
