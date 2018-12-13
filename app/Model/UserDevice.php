<?php 
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\CoreModel;
use VDateTime;
use App\Model\Traits\ModelNormalDeleteTrait;

class UserDevice extends CoreModel
{
    use ModelNormalDeleteTrait;
    
	/**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_devices';

	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
	protected $hidden = ['id'];

    /**
     * Save the current time as last access time
     * @return [boolean] [save result]
     */
    public function registerLastAccess()
    {
        $this->last_access_at = VDateTime::now();
        return $this->save();
    }
}