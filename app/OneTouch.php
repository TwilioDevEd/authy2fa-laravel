<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OneTouch extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'one_touch';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uuid'];
}
