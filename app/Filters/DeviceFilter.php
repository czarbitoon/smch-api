<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class DeviceFilter
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function apply(Builder $query): Builder
    {
        foreach ($this->filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            switch ($key) {
                case 'device_category_id':
                    $query->where('device_category_id', $value);
                    break;
                case 'device_type_id':
                    $query->where('device_type_id', $value);
                    break;
                case 'device_subcategory_id':
                    $query->where('device_subcategory_id', $value);
                    break;
                case 'office_id':
                    $query->where('office_id', $value);
                    break;
                case 'status':
                    $query->where('status', $value);
                    break;
                case 'manufacturer':
                    $query->where('manufacturer', 'like', "%$value%");
                    break;
                case 'serial_number':
                    $query->where('serial_number', 'like', "%$value%");
                    break;
                case 'model_number':
                    $query->where('model_number', 'like', "%$value%");
                    break;
                case 'name':
                    $query->where('name', 'like', "%$value%");
                    break;
                // Add more filters as needed
            }
        }
        return $query;
    }
}
