<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Log;
class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function creating(Category $category)
    {
        if (is_null($category->position)) {
            $category->position = Category::max('position') + 1;
            return;
        }

        $lowerPriorityCategories = Category::where('position', '>=', $category->position)
            ->get();

        foreach ($lowerPriorityCategories as $lowerPriorityCategory) {
            $lowerPriorityCategory->position++;
            $lowerPriorityCategory->save();
        }

        Log::info("========= Observer Creating ==========");
    }

    /**
     * Handle the Category "updating" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function updating(Category $category)
    {
        

    
        if (is_null($category->position)) {
            $category->position = Category::max('position');
        }
       
        if ($category->getOriginal('position') > $category->position) {
            $positionRange = [
                $category->position, $category->getOriginal('position')
            ];
        } else {
            $positionRange = [
                $category->getOriginal('position'), $category->position
            ];
        }

        $lowerPriorityCategories = Category::where('id', '!=', $category->id)
            ->whereBetween('position', $positionRange)
            ->get();

        foreach ($lowerPriorityCategories as $lowerPriorityCategory) {
            if ($category->getOriginal('position') < $category->position) {
                $lowerPriorityCategory->position--;
            } else {
                $lowerPriorityCategory->position++;
            }
            $lowerPriorityCategory->save();
        }

        Log::info("========= Observer Updated ==========");

        
    }

    /**
     * Handle the Category "deleted" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function deleted(Category $category)
    {
        //
    }

    /**
     * Handle the Category "restored" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function restored(Category $category)
    {
        //
    }

    /**
     * Handle the Category "force deleted" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function forceDeleted(Category $category)
    {
        //
    }
}
