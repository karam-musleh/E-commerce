<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasSlug;
    use HasFactory;
    // use HasSlug;
    // status / active, inactive, archived , deleted
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ARCHIVED = 'archived';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'icon',
        'banner',
        'image',
        'is_featured',
        'status',
    ];
    // scopes
    public function scopeActive($query)
    {
        return $query->where('status', Category::STATUS_ACTIVE);
    }
    // in Active scope
    public function scopeInactive($query)
    {
        return $query->where('status', Category::STATUS_INACTIVE);
    }
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }


    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->active()
            ->with('children');
    }
    public function childrenAdmin()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->with('childrenAdmin');
    }
    public function setStatus(string $status): bool
    {
        $allowed = [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_ARCHIVED,
        ];

        if (! in_array($status, $allowed)) {
            return false;
        }

        return $this->update(['status' => $status]);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
