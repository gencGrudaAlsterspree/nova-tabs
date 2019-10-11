<?php
namespace Eminiarts\Tabs;

use Laravel\Nova\Panel;
use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;

trait TabsOnEdit
{

    /**
     * This will call ResolvesField::creationFields instead of the modified TabsOnEdit::creationFields
     *
     * @param NovaRequest $request
     */
    public function parentCreationFields(NovaRequest $request)
    {
        return parent::creationFields($request);
    }

    /**
     * This will call ResolvesField::updateFields instead of the modified TabsOnEdit::updateFields
     *
     * @param NovaRequest $request
     */
    public function parentUpdateFields(NovaRequest $request)
    {
        return parent::updateFields($request);
    }

    /**
     * @param  NovaRequest $request
     * @return mixed
     */
    public static function rulesForCreation(NovaRequest $request)
    {
        return static::formatRules($request, (new static(static::newModel()))
            ->parentCreationFields($request)
            ->reject(function ($field) use ($request) {
                return $field->isReadonly($request);
            })
            ->mapWithKeys(function ($field) use ($request) {
                return $field->getCreationRules($request);
            })->all());
    }

    /**
     * Get the validation rules for a resource update request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public static function rulesForUpdate(NovaRequest $request)
    {
        return static::formatRules($request, (new static(static::newModel()))
            ->parentUpdateFields($request)
            ->reject(function ($field) use ($request) {
                return $field->isReadonly($request);
            })
            ->mapWithKeys(function ($field) use ($request) {
                return $field->getUpdateRules($request);
            })->all());
    }

    /**
     * Return the creation fields excluding any readonly ones.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Support\Collection
     */
    public function creationFieldsWithoutReadonly(NovaRequest $request)
    {
        return $this->parentCreationFields($request)
            ->reject(function ($field) use ($request) {
                return $field->isReadonly($request);
            });
    }

    /**
     * Return the update fields excluding any readonly ones.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Support\Collection
     */
    public function updateFieldsWithoutReadonly(NovaRequest $request)
    {
        return $this->parentUpdateFields($request)
            ->reject(function ($field) use ($request) {
                return $field->isReadonly($request);
            });
    }

    /**
     * Resolve the creation fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return \Illuminate\Support\Collection
     */
    public function creationFields(NovaRequest $request)
    {
        return collect(
            [
                'Tabs' => [
                    'component' => 'tabs',
                    'fields'    => $this->removeNonCreationFields($request, $this->resolveFields($request)),
                    'panel'     => Panel::defaultNameForCreate($request->newResource()),
                ],
            ]
        );
    }

    /**
     * Resolve the update fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return \Illuminate\Support\Collection
     */
    public function updateFields(NovaRequest $request)
    {
        return collect(
            [
                'Tabs' => [
                    'component' => 'tabs',
                    'fields'    => $this->removeNonUpdateFields($request, $this->resolveFields($request)),
                    'panel'     => Panel::defaultNameForUpdate($request->newResource()),
                ],
            ]
        );
    }

    /**
     * Assign the fields with the given panels to their parent panel.
     *
     * @param  string                           $label
     * @param  \Illuminate\Support\Collection   $panels
     * @return \Illuminate\Support\Collection
     */
    protected function assignToPanels($label, Collection $panels)
    {
        return $panels->map(function ($field) use ($label) {
            if ( !is_array($field) && !$field->panel ) {
                $field->panel = $label;
            }

            return $field;
        });
    }
}