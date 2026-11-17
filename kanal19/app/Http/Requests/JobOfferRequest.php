<?php

namespace App\Http\Requests;

use App\Models\JobOffer;
use App\Support\Languages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // uprawnienia sprawdza JobOfferPolicy w kontrolerze
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'category' => ['required', Rule::in(array_keys(JobOffer::CATEGORIES))],
            'city' => ['required', 'string', 'max:80'],
            'country' => ['required', 'string', 'size:2'],
            'salary_from' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'salary_to' => ['nullable', 'integer', 'min:0', 'max:1000000', 'gte:salary_from'],
            'currency' => ['required', Rule::in(JobOffer::CURRENCIES)],
            'salary_type' => ['required', Rule::in(['brutto', 'netto'])],
            'contract' => ['required', Rule::in(array_keys(JobOffer::CONTRACTS))],
            'work_modes' => ['nullable', 'array'],
            'work_modes.*' => [Rule::in(array_keys(JobOffer::WORK_MODES))],
            'description' => ['required', 'string', 'min:30', 'max:5000'],
            'requirements' => ['nullable', 'string', 'max:3000'],
            'no_cv' => ['boolean'],
            'language' => ['required', Rule::in(array_keys(Languages::ALL))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['no_cv' => $this->boolean('no_cv'), 'country' => strtoupper((string) $this->input('country'))]);
    }
}
