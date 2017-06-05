<?php

namespace App;

use App\Events\FileCreating;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'path',
        'filesystem',
        'type',
        'size',
        'uploader_id'
    ];

    protected $events = [
        'creating' => FileCreating::class,
    ];

    public function uploader() {
        return $this->belongsTo(User::class);
    }
}
