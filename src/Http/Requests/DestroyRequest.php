<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Requests;

use Stepanenko3\LaravelApiSkeleton\Http\Resource;

class DestroyRequest extends Request
{
    /**
     * Define the validation rules for the destroy request.
     *
     * This method defines the validation rules for destroying resources.
     * It requires an array of resources to be destroyed.
     */
    public function rules(): array
    {
        return $this->destroyRules($this->route()->controller::newResource());
    }

    /**
     * Define the validation rules for destroying resources.
     *
     * @return array
     *
     * This method specifies the validation rules for destroying resources.
     * It expects an instance of the resource being destroyed and requires an array
     * containing the resources to be destroyed.
     */
    public function destroyRules(Resource $resource)
    {
        return [
            'resources' => [
                'required', 'array',
            ],
        ];
    }
}
