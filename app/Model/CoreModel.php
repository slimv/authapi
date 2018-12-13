<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

abstract class CoreModel extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Set this to true if model dont use scrub
     * @var boolean
     */
    protected static $disableScrub = false;

    /**
     * Constructor 
     * @param [type] $attributes [description]
     */
    public function __construct(array $attributes = array())
    {
        //set the date format
        $this->dateFormat = env('SYSTEM_DATETIME_FORMAT');

        parent::__construct($attributes);
    }

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
            if(!static::$disableScrub) {
                $model->scrub_id = (string)Uuid::generate();
            }

            static::onCreating($model);
        });

        static::updating(function ($model) {
            static::onUpdating($model);
        });
    }

    /**
     * This function will return the model public identity. For example scrub_id or id, depend on the model.
     * This value will be used in public so make sure dont use easy to guess value as number id
     * @return [string|number] [Public identity value]
     */
    public function getPublicIdentity()
    {
        if(!static::$disableScrub) {
            return $this->scrub_id;
        } else {
            return $this->id;
        }
    }

    /**
     * Event when model is creating
     * @param  [type] $model [description]
     * @return [type]        [description]
     */
    protected static function onCreating($model)
    {

    }

    /**
     * Event when model is updating
     * @param  [type] $model [description]
     * @return [type]        [description]
     */
    protected static function onUpdating($model)
    {

    }
}