<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Http\Requests\DeleteResourceRequest;
use Laravel\Nova\Nova;

class ResourceDestroyController extends Controller
{
    use DeletesFields;

    /**
     * Destroy the given resource(s).
     *
     * @param  \Laravel\Nova\Http\Requests\DeleteResourceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(DeleteResourceRequest $request)
    {
        $request->chunks(150, function ($models) use ($request) {
            $models->each(function ($model) use ($request) {
                $this->deleteFields($request, $model);

                if (in_array(Actionable::class, class_uses_recursive($model))) {
                    $model->actions()->delete();
                }

                $model->delete();

                DB::table('action_events')->insert(
                    Nova::actionEvent()->forResourceDelete($request->user(), collect([$model]))
                                ->map->getAttributes()->all()
                );
            });
        });
    }
}
