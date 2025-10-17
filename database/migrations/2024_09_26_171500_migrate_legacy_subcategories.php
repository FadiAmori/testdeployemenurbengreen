<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $legacy = DB::table('categories')->whereNotNull('parent_id')->get();

        foreach ($legacy as $row) {
            $parentId = $row->parent_id;
            if (! DB::table('categories')->where('id', $parentId)->exists()) {
                continue;
            }

            $slug = $row->slug ?: Str::slug($row->name);
            $slugBase = $slug;
            $index = 1;
            while (DB::table('sub_categories')->where('slug', $slug)->exists()) {
                $slug = $slugBase . '-' . $index++;
            }

            $subCategoryId = DB::table('sub_categories')->insertGetId([
                'category_id' => $parentId,
                'name' => $row->name,
                'slug' => $slug,
                'description' => $row->description,
                'position' => $row->position ?? 0,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            DB::table('products')->where('category_id', $row->id)->update([
                'category_id' => $parentId,
                'sub_category_id' => $subCategoryId,
            ]);

            DB::table('categories')->where('id', $row->id)->delete();
        }
    }

    public function down(): void
    {
        // Intentionally left blank. Converting back would risk data integrity.
    }
};
