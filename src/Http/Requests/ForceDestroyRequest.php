<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Requests;

use Stepanenko3\LaravelApiSkeleton\Http\Resource;

class ForceDestroyRequest extends Request
{
    /**
     * Define the validation rules for the force destroy request.
     *
     * @return array
     *
     * This method defines the validation rules for force destroying resources.
     * It requires an array of resources to be force destroyed.
     */
    public function rules()
    {
        return $this->forceDestroyRules($this->route()->controller::newResource());
    }

    /**
     * Define the validation rules for force destroying resources.
     *
     * @return array
     *
     * This method specifies the validation rules for force destroying resources.
     * It expects an instance of the resource being force destroyed and requires an array
     * containing the resources to be force destroyed.
     */
    public function forceDestroyRules(Resource $resource)
    {
        return [
            'resources' => [
                'required', 'array',
            ],
        ];
    }
}
