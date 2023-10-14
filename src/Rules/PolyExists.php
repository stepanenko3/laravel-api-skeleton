<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;

class PolyExists implements ValidationRule
{
    public function __construct(
        protected string $typeField,
        protected array $data = [],
    ) {
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        $type = data_get(
            target: $this->data,
            key: $this->typeField,
            default: false,
        );

        if ($type) {
            if (Relation::getMorphedModel(
                alias: $type,
            )) {
                $type = Relation::getMorphedModel(
                    alias: $type,
                );
            }

            if (class_exists($type)) {
                $model = !empty(resolve(
                    name: $type
                )
                    ->find($value));

                if (!$model) {
                    $fail(':attribute contains invalid value')
                        ->translate();
                }
            } else {
                $fail(':type contains invalid value')
                    ->translate(
                        replace: [
                            'type' => $this->typeField,
                        ],
                    );
            }
        } else {
            $fail('The :type field is required')
                ->translate(
                    replace: [
                        'type' => $this->typeField,
                    ],
                );
        }
    }
}
