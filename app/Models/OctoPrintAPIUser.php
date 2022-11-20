<?php

namespace App\Models;

use App\ModelTraits\UuidKey;
use Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 * @property string $creator_id
 * @property string $api_token
 * @property-read Bot|Cluster $worker
 * @property-read User $creator
 */
class OctoPrintAPIUser extends Model implements Authenticatable
{
    use UuidKey;

    protected $table = "octoprint_api_user";

    protected static function booted()
    {
        self::creating(function($user) {
            /** @var OctoPrintAPIUser $user */
            $user->api_token = bin2hex(Uuid::uuid4()->getBytes());
            $user->creator_id = Auth::user()->id;
        });
    }

    public function url(): string
    {
        return url("octoprint/{$this->id}");
    }

    public function worker()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPassword()
    {
        return null;
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {}

    public function getRememberTokenName()
    {
        return null;
    }
}
