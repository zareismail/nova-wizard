<?php

namespace Zareismail\NovaWizard\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Zareismail\NovaWizard\Http\Requests\CreateResourceRequest;
use Laravel\Nova\Nova;

class ResourceStoreController extends Controller
{
    /**
     * Create a new resource.
     *
     * @param  \Laravel\Nova\Http\Requests\CreateResourceRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(CreateResourceRequest $request)
    {
        $resource = $request->resource();

        $resource::authorizeToCreate($request);

        $resource::validateForCreation($request);

        $model = DB::transaction(function () use ($request, $resource) {
            [$model, $callbacks] = $resource::fill(
                $request, $request->findModelOrNew()
            );

            if(is_null($model->getKey()) && $request->viaSession == 'true') {
                return $model;
            }

            if ($request->viaRelationship()) {
                $request->findParentModelOrFail()
                        ->{$request->viaRelationship}()
                        ->save($model);
            } else {
                $model->save();
            }

            Nova::actionEvent()->forResourceCreate($request->user(), $model)->save();

            collect($callbacks)->each->__invoke();

            return $model;
        });

        $this->refreshTheResourceCheckpoint($request, $resource, $model);

        return response()->json([
            'id' => $model->getKey(),
            'resource' => $model->attributesToArray(),
            'redirect' => $resource::redirectAfterCreate($request, $request->newResourceWith($model)),
        ], 200);
    }

    /**
     * Determine if this request is an checkpoint request.
     *
     * @return bool
     */
    public function refreshTheResourceCheckpoint(CreateResourceRequest $request, $resource, $model)
    {
        if($request->isSubmitRequest()) {
            $request->session()->remove($resource::uriKey());
        } else if(! is_null($model->getKey())) {
            $request->session()->put("{$resource::uriKey()}.step", $request->step);
            $request->session()->put("{$resource::uriKey()}.instance", null);
        } else {
            $request->session()->put("{$resource::uriKey()}.step", $request->step);
            $request->session()->put("{$resource::uriKey()}.instance", $model);
        }

        return $this;
    }

    public function clearSession(CreateResourceRequest $request) {
        $request->session()->remove($request->resource()::uriKey());

        return response()->json([ 'status' => 'OK' ]);
    }
}
