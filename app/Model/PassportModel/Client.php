<?php

namespace App\Model\PassportModel;

use Webpatser\Uuid\Uuid;
use App\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Traits\ModelSoftDeleteTrait;

class PassportClient extends CoreModel
{
    use SoftDeletes, ModelSoftDeleteTrait;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_clients';

    protected static $disableScrub = true;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * Attach to the 'creating' Model Event to provide a UUID
         * for the `id` field (provided by $model->getKeyName())
         */
        static::creating(function ($model) {
            $model->incrementing = false;
            $model->id = (string)Uuid::generate();
        });
        static::retrieved(function ($model) {
            $model->incrementing = false;
        });
    }
}
