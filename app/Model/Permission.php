<?php 
namespace App\Model;

use App\Model\CoreModel;
use App\Model\Traits\ModelNormalDeleteTrait;
use VString;

class Permission extends CoreModel
{
    use ModelNormalDeleteTrait;

	/**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
	protected $hidden = ['id', 'deleted_at'];

    /**
     * Event when model is creating
     * @param  [type] $model [description]
     * @return [type]        [description]
     */
    protected static function onCreating($model)
    {
        //strip all space
        $model->key = VString::stripAllSpace($model->key);
    }

    /**
     * Event when model is updating
     * @param  [type] $model [description]
     * @return [type]        [description]
     */
    protected static function onUpdating($model)
    {
        //strip all space
        $model->key = VString::stripAllSpace($model->key);
    }
}