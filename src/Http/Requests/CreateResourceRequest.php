<?php

namespace Zareismail\NovaWizard\Http\Requests;

use Laravel\Nova\Http\Requests\CreateResourceRequest as NovaRequest;

class CreateResourceRequest extends NovaRequest
{ 
    /**
     * Determine if this request is an submit request.
     *
     * @return bool
     */
    public function isSubmitRequest()
    {
        return $this->editing && $this->storeMode === 'submit';
    }

    /**
     * Determine if this request is an checkpoint request.
     *
     * @return bool
     */
    public function isCheckpointRequest()
    {
        return $this->editing && $this->storeMode === 'checkpoint';
    }

    /**
     * Find the model instance or make new.
     *
     * @param  mixed|null  $resourceId
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findModelOrNew($resourceId = null)
    {
        return $this->findModelQuery()->firstOr(function() { 
        	$resourceClass = $this->resource(); 

            return $this->session()->get($resourceClass::uriKey(). '.instance') ?: $resourceClass::newModel();
        });
    } 
}
