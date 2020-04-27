<?php

namespace Zareismail\NovaWizard\Http\Controllers; 

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class UpdateFieldController extends Controller
{
    /**
     * List the update fields for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(NovaRequest $request)
    {
        return parent::index($request); 
    }
}

