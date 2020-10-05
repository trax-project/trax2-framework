<?php

namespace Trax\Auth\Stores\BasicHttp;

use Illuminate\Database\Eloquent\Model;
use Trax\Auth\Stores\Accesses\Access;

class BasicHttp extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trax_basic_http';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username', 'password'];

    /**
     * Get the access.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function access()
    {
        return $this->morphOne(Access::class, 'credentials');
    }
}
