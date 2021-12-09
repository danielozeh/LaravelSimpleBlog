<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class BlogComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blog_id',
        'status',
        'comment'
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
