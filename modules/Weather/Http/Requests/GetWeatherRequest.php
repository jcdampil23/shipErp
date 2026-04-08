<?php

namespace Modules\Weather\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class GetWeatherRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $city = $this->route("city");

        if (is_string($city)) {
            $city = trim($city);
            $city = strip_tags($city);
            $city = preg_replace('/\s+/', ' ', $city);
            $city = preg_replace('/-+/', ' ', $city);
            $city = mb_convert_case($city, MB_CASE_TITLE, 'UTF-8');
            $this->route()->setParameter('city', $city);
        }

        $this->merge(['city' => $city]);
    }

    public function rules(): array
    {
        return [
            'city' => [
                'required',
                'string',
                'min:2',
                'regex:/^[\pL\s\.]+$/u'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'city.regex' => 'City parameter has invalid characters'
        ];
    }
}