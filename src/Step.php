<?php 

namespace Zareismail\NovaWizard;


use Laravel\Nova\Panel;
use Laravel\Nova\Http\Requests\NovaRequest;


class Step extends Panel
{
	protected static $steps = [];  

	protected static $step = 0;  

    /**
     * Create a new panel instance.
     *
     * @param  string  $name
     * @param  \Closure|array  $fields
     * @return void
     */
    public function __construct($name, $fields = [])
    {  
        $request = app(NovaRequest::class);

        $resource = $request->resource;

        if($request->isCreateOrAttachRequest() || (
            $request->isUpdateOrUpdateAttachedRequest() && 
            ! is_subclass_of($resource, Contracts\IngoreUpdateWizard::class)
        )) {
            isset(static::$steps[$name]) || static::$steps[$name] = static::$step++;

            $fields = static::$steps[$name] != request('step') ? [] : $fields; 

            $this->withMeta([
                'step' => static::$steps[$name], 
                'passed' => static::$steps[$name] < request('step') 
            ]);
        } 

        parent::__construct($name, $fields); 
    }

    public function checkpoint()
    {
    	$this->withMeta([
    		'checkpoint' => true
    	]);

        return $this;
    }
}