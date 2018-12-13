<?php 
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\CoreModel;
use VDateTime;

class UserActiveCode extends CoreModel
{
	/**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_active_codes';

	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
	protected $hidden = ['id'];

    /**
     * Check if the code is expired or not
     * @return boolean [True if the code is expired]
     */
    public function isExpired()
    {
        //if not set we will consider it is as expired
        if(!$this->expire_at) {
            return true;
        }

        return VDateTime::isBeforeNow($this->expire_at);
    }

    /**
     * Check if the code is used or not
     * @return boolean [description]
     */
    public function isUsed()
    {
        return $this->status == 'actived';
    }

    /**
     * Set the code as actived
     * @return [boolean] [save result]
     */
    public function activeCode()
    {
        $this->status = 'actived';
        return $this->save();
    }
}