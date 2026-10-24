<?php

namespace App\Models\Concerns;

use App\Support\Slugs;
use Illuminate\Support\Facades\DB;

// Gives a model a URL slug built from its name. When the admin renames it, the slug follows the new
// name and the old one is kept in old_slugs, so links already shared or indexed keep working (301).
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            if (!$model->slug || $model->isDirty('name')) {
                $model->slug = Slugs::unique($model->getTable(), $model->name, $model->id, $model->slugFallback());
            }
        });

        static::saved(function ($model) {
            if (!$model->wasChanged('slug')) {
                return;
            }
            $old = $model->getOriginal('slug');
            DB::transaction(function () use ($model, $old) {
                // The new slug now belongs to this row, not to whatever used to answer to it
                DB::table('old_slugs')->where('type', $model->getTable())->where('slug', $model->slug)->delete();
                if ($old) {
                    DB::table('old_slugs')->updateOrInsert(
                        ['type' => $model->getTable(), 'slug' => $old],
                        ['target_id' => $model->id, 'created_at' => now()]
                    );
                }
            });
        });
    }

    // Which row an old slug of this model's table now points to
    public static function idForOldSlug(string $slug): ?int
    {
        $id = DB::table('old_slugs')->where('type', (new static)->getTable())->where('slug', $slug)->value('target_id');

        return $id ? (int) $id : null;
    }

    protected function slugFallback(): string
    {
        return strtolower(class_basename($this));
    }
}
