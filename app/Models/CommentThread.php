<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentThread extends Model
{
    use HasFactory;

    protected $fillable = ['comment_id', 'parent_id'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
}
