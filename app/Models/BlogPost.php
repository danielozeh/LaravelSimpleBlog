<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'status',
        'featured_image',
        'user_id'
    ];

    public function getCreatedAtAttribute($date)
    {
        return Carbon::createFromTimestamp(strtotime($date))->format('d F Y h:i:s A');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::createFromTimestamp(strtotime($date))->format('d F Y h:i:s A');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(BlogCategory::class);
    }

    public function comments() {
        return $this->hasMany(BlogComment::class, 'blog_id');
    }

    public function likes() {
        return $this->hasMany(BlogLike::class, 'blog_id');
    }

    public function user_like() {
        return $this->belongsTo(User::class, 'blog_id');
    }
}
