<?php

namespace App\Models;

use App\Http\Traits\Attachmentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory, SoftDeletes, Attachmentable;

    protected $fillable = [
        'url',
        'uploaded_user_id',
        'attachmentable_id',
        'attachmentable_type',
        'state',
        'label',
    ];

    protected $dates = ['deleted_at'];

    protected  $appends=['file', 'content_type', 'user'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'attachmentable_type'
    ];

    /**
     * Get the parent attachmentable.
     */
    public function attachmentable()
    {
        return $this->morphTo();
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
