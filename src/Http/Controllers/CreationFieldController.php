<?php

namespace Zareismail\NovaWizard\Http\Controllers;

use Illuminate\Routing\Controller; 
use Zareismail\NovaWizard\Http\Requests\CreateResourceRequest;

class CreationFieldController extends Controller
{
    /**
     * List the creation fields for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(CreateResourceRequest $request)
    {
        $resourceClass = $request->resource(); 

        $resourceClass::authorizeToCreate($request); 

        $model = $request->findModelOrNew();

        return response()->json([
            'fields' => $request->newResourceWith($model)->creationFieldsWithinPanels($request),
            'panels' => $request->newResourceWith($model)->availablePanelsForCreate($request),
        ]);
    }
}
