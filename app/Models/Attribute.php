<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasSlug;
    //
    protected $fillable = [
        'name',
    ];
    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }
    

}
