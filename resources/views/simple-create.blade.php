<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simple Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
        integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="bg-dark" data-bs-theme="dark">
    <div class="container my-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="{{ route(name: 'simple-generators.store') }}" method="POST" id="form-generator">
                            @csrf
                            @method('POST')

                            <div class="row mb-3">
                                {{-- model name --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="model">{{ __(key: 'Model') }}</label>
                                        <input type="text" name="model" id="model"
                                            class="form-control @error('model') is-invalid @enderror"
                                            placeholder="{{ __(key: 'Product') }}" value="{{ old(key: 'model') }}" autofocus
                                            required>
                                        <small
                                            class="text-secondary">{{ __(key: "Use '/' for generate a sub folder. e.g.: Main/Product.") }}</small>
                                        @error('model')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                {{-- end of model name --}}

                                {{-- generate type --}}
                                <div class="col-md-3">
                                    <p class="mb-2">{{ __(key: 'Generator Type') }}</p>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="generate_type"
                                            id="generate-type-1"
                                            value="{{ \EvdigiIna\Generator\Enums\GeneratorType::ALL->value }}" checked>
                                        <label class="form-check-label" for="generate-type-1">
                                            {{ __(key: 'All (Migration, Model, View, Controller, Route, & Request)') }}
                                        </label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="generate_type"
                                            id="generate-variant-1"
                                            value="{{ \EvdigiIna\Generator\Enums\GeneratorType::ONLY_MODEL_AND_MIGRATION->value }}">
                                        <label class="form-check-label" for="generate-variant-1">
                                            {{ __(key: 'Only Model & Migration') }}
                                        </label>
                                    </div>
                                </div>
                                {{-- end of generate type --}}

                                {{-- generate variant --}}
                                <div class="col-md-3">
                                    <p class="mb-2">{{ __(key: 'Variant') }}</p>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="generate_variant"
                                            id="generate-variant-2" value="default" checked>
                                        <label class="form-check-label" for="generate-variant-2">
                                            {{ __(key: 'Default (blade)') }}
                                        </label>
                                    </div>

                                    <div class="form-check form-check-inline"
                                        @if (!class_exists(class: \Laravel\Sanctum\Sanctum::class)) data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Execute 'php artisan install:api' to enable this feature" @endif>
                                        <div class="checkbox">
                                            <input class="form-check-input" type="radio" name="generate_variant"
                                                id="generate-type-1" value="api"
                                                @if (!class_exists(class: \Laravel\Sanctum\Sanctum::class)) disabled @endif>
                                            <label for="generate-type-1">
                                                {{ __(key: 'API') }}
                                                <small style="font-size: 8px;">Beta</small>

                                                @if (!class_exists(class: \Laravel\Sanctum\Sanctum::class))
                                                    <i class="fas fa-question fa-xs"></i>
                                                @endif
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="generate_variant"
                                            id="generate-varaint-3"
                                            value="{{ \EvdigiIna\Generator\Enums\GeneratorVariant::SINGLE_FORM->value }}">
                                        <label class="form-check-label" for="generate-varaint-3">
                                            {{ __(key: 'Single Form (only create or update)') }}
                                        </label>
                                    </div>
                                </div>
                                {{-- end of generate variant --}}

                                <div class="col-md-3">
                                    <p class="mb-2">{{ __(key: 'Add-ons') }}
                                        <small style="font-size: 8px;">Beta</small>
                                    </p>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" class="form-check-input" id="generate-seeder"
                                                name="generate_seeder">
                                            <label for="generate-seeder">{{ __(key: 'Generate Seeder') }}</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" class="form-check-input" id="generate-factory"
                                                name="generate_factory">
                                            <label for="generate-factory">{{ __(key: 'Generate Factory') }}</label>
                                        </div>
                                    </div>
                                    <div class="form-check"
                                        @if (!class_exists(class: \Maatwebsite\Excel\ExcelServiceProvider::class)) data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Install 'composer require maatwebsite/excel' to enable this feature" @endif>
                                        <div class="checkbox">
                                            <input type="checkbox" class="form-check-input" id="generate-export"
                                                name="generate_export"
                                                @if (!class_exists(class: \Maatwebsite\Excel\ExcelServiceProvider::class)) disabled @endif>
                                            <label for="generate-export">
                                                {{ __(key: 'Generate Export') }}

                                                @if (!class_exists(class: \Maatwebsite\Excel\ExcelServiceProvider::class))
                                                    <i class="fas fa-question fa-xs"></i>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-3">
                                    <h6>{{ __(key: 'Table Fields') }}</h6>
                                </div>

                                <div class="col-md-6 mt-3 d-flex justify-content-end">
                                    <button type="button" id="btn-add" class="btn btn-success">
                                        <i class="fas fa-plus"></i>
                                        {{ __(key: 'Add') }}
                                    </button>
                                </div>

                                {{-- table fields --}}
                                <div class="col-md-12">
                                    <table class="table table-striped table-hover table-sm" id="tbl-field">
                                        <thead>
                                            <tr>
                                                <th width="30">#</th>
                                                <th>{{ __(key: 'Field name') }}</th>
                                                <th>{{ __(key: 'Column Type') }}</th>
                                                <th width="310">{{ __(key: 'Length') }}</th>
                                                <th>
                                                    {{ __(key: 'Input Type') }}
                                                    <br>
                                                    <small>{{ __(key: 'Used for validation request') }}</small>
                                                </th>
                                                <th>{{ __(key: 'Required') }}</th>
                                                <th>{{ __(key: 'Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr draggable="true" ondragstart="dragStart()" ondragover="dragOver()"
                                                style="cursor: move;">
                                                <td>1</td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" name="fields[]" class="form-control"
                                                            placeholder="{{ __(key: 'Field Name') }}" required>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="column_types[]"
                                                            class="form-select form-column-types" required>
                                                            <option value="" disabled selected>
                                                                --{{ __(key: 'Select column type') }}--</option>
                                                            @foreach (['string', 'integer', 'text', 'bigInteger', 'boolean', 'char', 'date', 'time', 'year', 'dateTime', 'decimal', 'double', 'enum', 'float', 'foreignId', 'tinyInteger', 'mediumInteger', 'tinyText', 'mediumText', 'longText'] as $type)
                                                                <option value="{{ $type }}">
                                                                    {{ ucwords($type) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="select_options[]"
                                                            class="form-option">
                                                        <input type="hidden" name="constrains[]"
                                                            class="form-constrain">
                                                        <input type="hidden" name="foreign_ids[]"
                                                            class="form-foreign-id">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="number" name="min_lengths[]"
                                                                    class="form-control form-min-lengths"
                                                                    min="1" placeholder="Min Length">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="number" name="max_lengths[]"
                                                                    class="form-control form-max-lengths"
                                                                    min="1" placeholder="Max Length">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="input_types[]"
                                                            class="form-select form-input-types" required>
                                                            <option value="" disabled selected>--
                                                                {{ __(key: 'Select input type') }} --</option>
                                                            <option value="" disabled>
                                                                {{ __(key: 'Select the column type first') }}</option>
                                                        </select>
                                                    </div>
                                                    <input type="hidden" name="mimes[]" class="form-mimes">
                                                    <input type="hidden" name="file_types[]"
                                                        class="form-file-types">
                                                    <input type="hidden" name="files_sizes[]"
                                                        class="form-file-sizes">
                                                    <input type="hidden" name="steps[]" class="form-step"
                                                        placeholder="step">
                                                </td>
                                                <td class="mt-0 pt-0">
                                                    <div class="form-check form-switch form-control-lg">
                                                        <input class="form-check-input switch-requireds"
                                                            type="checkbox" id="switch-1" name="requireds[]"
                                                            checked>
                                                    </div>
                                                    <input type="hidden" name="default_values[]"
                                                        class="form-default-value"
                                                        placeholder="{{ __(key: 'Default Value (optional)') }}">
                                                </td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-sm btn-delete" disabled>
                                                        <i class="fa fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                {{-- end of table fields --}}

                                <div id="col-new-menu" style="display: none;"></div>
                            </div>

                            <a href="/" id="btn-back" class="btn btn-secondary">{{ __(key: 'Back') }}</a>

                            <button type="submit" id="btn-save"
                                class="btn btn-primary">{{ __(key: 'Generate') }}</button>
                        </form>
                    </div>
                </div>

                <div id="validation-errors" style="display: none;">
                    <div class="alert alert-danger fade show mt-3" role="alert">
                        <h4 class="alert-heading">Error</h4>
                        <ul class="mb-0"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('generator::include.js.create-js')

    @include('generator::include.js.function-js')
</body>

</html>
