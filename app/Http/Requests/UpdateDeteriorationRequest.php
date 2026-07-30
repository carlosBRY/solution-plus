<?php

namespace App\Http\Requests;

use App\Enums\DeteriorationCause;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateDeteriorationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date'],
            'observation' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produit_id' => ['required', 'exists:produits,id'],
            'items.*.conditionnement_id' => ['required', 'exists:conditionnements,id'],
            'items.*.quantite_conditionnement' => ['required', 'integer', 'min:1'],
            'items.*.cause' => ['required', new Enum(DeteriorationCause::class)],
            'items.*.cout_unitaire' => ['nullable', 'numeric', 'min:0'],
            'items.*.observation' => ['nullable', 'string', 'max:500'],
        ];
    }
}
