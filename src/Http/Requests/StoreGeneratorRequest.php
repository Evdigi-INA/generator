<?php

namespace EvdigiIna\Generator\Http\Requests;

use EvdigiIna\Generator\Enums\GeneratorType;
use EvdigiIna\Generator\Generators\GeneratorUtils;
use EvdigiIna\Generator\Generators\Services\GeneratorService;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class StoreGeneratorRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $menuValidations = [
            'menu' => ['required_unless:header,new'],
            'header' => ['required'],
        ];

        $validations = [
            // regex only for string, underscores("_") and slash("/")
            'model' => ['required', 'regex:/^[A-Za-z_\/]+$/'],
            'generate_type' => ['required', new Enum(GeneratorType::class)],
            'input_types.*' => ['required'],
            'foreign_ids.*' => ['nullable'],
            'min_lengths.*' => ['nullable'],
            'max_lengths.*' => ['nullable'],
            'steps.*' => ['nullable'],
            'default_values.*' => ['nullable'],
            'fields.*' => ['required', 'regex:/^[A-Za-z_]+$/'],
            'requireds.*' => ['required', 'in:yes,no'],
            'mimes.*' => ['nullable', 'required_if:file_types.*,mimes'],
            'files_sizes.*' => ['nullable', 'required_if:input_types.*,file'],
            'select_options.*' => ['nullable', 'required_if:column_types.*,enum'],
            'constrains.*' => ['nullable', 'required_if:column_types.*,foreignId'],
            'file_types.*' => ['nullable', 'required_if:input_types.*,file', 'in:image,mimes'],
            'column_types.*' => ['required', 'in:' . implode(',', (new GeneratorService)->columnTypes())],
            'on_update_foreign.*' => ['nullable'],
            'on_delete_foreign.*' => ['nullable'],
            'new_header' => ['nullable'],
            'new_icon' => ['nullable'],
            'new_menu' => ['nullable'],
            'generate_seeder' => ['nullable'],
            'generate_factory' => ['nullable'],
            // 'new_route' => ['required_if:header,new'],
            'new_submenu' => ['nullable'],
            'generate_variant' => ['required'],
        ];

        if(GeneratorUtils::isGenerateApi()) return $validations;

        return [...$validations, ...$menuValidations];
    }
}
