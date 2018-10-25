<?php

namespace R64\Tags;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Illuminate\Support\Str;
use Laravel\Nova\TrashedStatus;
use Laravel\Nova\Fields\FormatsRelatableDisplayValues;

class Tags extends Field
{
    use FormatsRelatableDisplayValues;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-fields-tags';

    /**
     * The class name of the related resource.
     *
     * @var string
     */
    public $resourceClass;

    /**
     * The column that should be displayed for the field.
     *
     * @var \Closure
     */
    public $display;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  mixed|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        $this->resourceClass = ResourceRelationshipGuesser::guessResource($name);
        $this->name = $name;
        $this->resolveCallback = $resolveCallback;
        $this->attribute = $attribute ?? str_replace(' ', '_', Str::lower($name));
    }

    /**
     * Specify a callback that should be used to store the field instead of hidrating the model.
     *
     * @param  callable  $fillCallback
     * @return $this
     */
    public function storeUsing($callback)
    {
        $this->fillUsing(function ($request, $model, $attribute) use ($callback) {
            unset($model->{$attribute});
            $model::saved(function ($savedModel) use ($callback, $request) {
                call_user_func($callback, $request, $savedModel);
            });
        });

        return $this;
    }

    /**
     * Hide selected options from the list
     *
     * @param $value
     * @return $this
     */
    public function hideOnSelect($value)
    {
        $this->withMeta(['hideOnSelect' => $value]);

        return $this;
    }

    /**
     * The key that should be used for display items.
     *
     * @param $key
     * @return $this
     */
    public function labelKey($key)
    {
        $this->withMeta(['labelKey' => $key]);

        return $this;
    }

    /**
     * The key that should be used for unique items.
     *
     * @param $key
     * @return $this
     */
    public function valueKey($key)
    {
        $this->withMeta(['valueKey' => $key]);

        return $this;
    }

    /**
     * The placeholder that should be displayed in the input.
     *
     * @param $placeholder
     * @return $this
     */
    public function placeholder($placeholder)
    {
        $this->withMeta(['placeholder' => $placeholder]);

        return $this;
    }

    /**
     * The placeholder that should be displayed in the add new tag input.
     *
     * @param $placeholder
     * @return $this
     */
    public function tagPlaceholder($placeholder)
    {
        $this->withMeta(['tagPlaceholder' => $placeholder]);

        return $this;
    }

    /**
     * Build an associatable query for the field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  bool  $withTrashed
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildAssociatableQuery(NovaRequest $request, $withTrashed = false)
    {
        $model = forward_static_call(
            [$resourceClass = $this->resourceClass, 'newModel']
        );

        $query = $request->first === 'true'
                        ? $model->newQueryWithoutScopes()->whereKey($request->current)
                        : $resourceClass::buildIndexQuery(
                                $request,
                            $model->newQuery(),
                            $request->search,
                                [],
                            [],
                            TrashedStatus::fromBoolean($withTrashed)
                          );

        return $query->tap(function ($query) use ($request, $model) {
            forward_static_call($this->associatableQueryCallable($request, $model), $request, $query);
        });
    }

    /**
     * Get the associatable query method name.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    protected function associatableQueryCallable(NovaRequest $request, $model)
    {
        return ($method = $this->associatableQueryMethod($request, $model))
                    ? [$request->resource(), $method]
                    : [$this->resourceClass, 'relatableQuery'];
    }

    /**
     * Get the associatable query method name.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    protected function associatableQueryMethod(NovaRequest $request, $model)
    {
        $method = 'relatable'.Str::plural(class_basename($model));

        if (method_exists($request->resource(), $method)) {
            return $method;
        }
    }

    /**
     * Format the given associatable resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  mixed  $resource
     * @return array
     */
    public function formatAssociatableResource(NovaRequest $request, $resource)
    {
        return array_filter([
            'avatar' => $resource->resolveAvatarUrl($request),
            'display' => $this->formatDisplayValue($resource),
            'value' => $resource->getKey(),
        ]);
    }
}
