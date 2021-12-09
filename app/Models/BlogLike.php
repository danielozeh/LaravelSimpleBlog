<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class BlogLike extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'blog_id'
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

    public function forum() {
        return $this->belongsTo(BlogPost::class);
    }
}
