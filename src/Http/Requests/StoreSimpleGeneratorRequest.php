<?php

namespace EvdigiIna\Generator\Http\Requests;

use EvdigiIna\Generator\Enums\GeneratorType;
use EvdigiIna\Generator\Generators\Services\GeneratorService;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class StoreSimpleGeneratorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * set the validation messages
     */
    public function messages(): array
    {
        return [
            'model.regex' => 'The model name may only contain letters, underscores, and slashes.',
            'generate_type.required' => 'The generate type field is required.',
            'input_types.*.required' => 'The input types field is required.',
            'fields.*.regex' => 'The field name may only contain letters and underscores.',
            'requireds.*.required' => 'The requireds field is required.',
            'column_types.*.required' => 'The column types field is required.',
            'generate_variant.required' => 'The generate variant field is required.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
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
            'generate_seeder' => ['nullable'],
            'generate_factory' => ['nullable'],
            'generate_variant' => ['required'],
        ];
    }
}
