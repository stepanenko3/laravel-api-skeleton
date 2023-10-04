<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Requests;

use Stepanenko3\LaravelApiSkeleton\Http\Resource;

class RestoreRequest extends Request
{
    /**
     * Define the validation rules for the restore request.
     *
     * @return array
     *
     * This method defines the validation rules for restoring resources.
     * It requires an array of resources to be restored.
     */
    public function rules()
    {
        return $this->restoreRules($this->route()->controller::newResource());
    }

    /**
     * Define the validation rules for restoring resources.
     *
     * @return array
     *
     * This method specifies the validation rules for the restoration process.
     * It expects an instance of the resource being restored and requires an array
     * containing the resources to be restored.
     */
    public function restoreRules(Resource $resource)
    {
        return [
            'resources' => [
                'required', 'array',
            ],
        ];
    }
}
