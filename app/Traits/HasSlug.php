<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    //
    protected static function bootHasSlug()
    {
        static::creating(function ($model) {
            $slugColumn = property_exists($model, 'slugFrom') ? $model->slugFrom : 'name';

            if (empty($model->slug) && isset($model->$slugColumn)) {
                $model->slug = $model->generateSlug($model->$slugColumn);
            }
        });

        static::updating(function ($model) {
            $slugColumn = property_exists($model, 'slugFrom') ? $model->slugFrom : 'name';

            if ($model->isDirty($slugColumn)) {
                $model->slug = $model->generateSlug($model->$slugColumn);
            }
        });
    }

    public function generateSlug($value)
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $count = 1;

        while (static::where('slug', $slug)
            ->where('id', '!=', $this->id ?? 0)
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
