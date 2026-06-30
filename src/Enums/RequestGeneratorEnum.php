<?php

namespace EvdigiIna\Generator\Enums;

use EvdigiIna\Generator\Generators\GeneratorUtils;

enum RequestGeneratorEnum: string
{
    case RULE_URL = "'url', ";

    case RULE_DATE = 'Rule::date(), ';

    case RULE_BOOLEAN = "'boolean', ";

    case RULE_STRING = "'string', ";

    case RULE_NUMERIC = 'Rule::numeric(), ';

    case RULE_CONFIRMED = "'confirmed', ";

    case RULE_EMAIL = 'Rule::email(), ';

    case RULE_PASSWORD_MIN = 'Password::min(size: 8), ';

    case RULE_IMAGE = 'File::image()->max(size: ';

    case RULE_REQUIRED = "'required', ";

    case RULE_NULLABLE = "'nullable', ";
    case RULE_DATE_WEEK = "'regex:/^\d{4}-W(0[1-9]|[1-4][0-9]|5[0-3])$/', ";

    case RULE_DATE_MONTH = "Rule::date()->format(format: 'Y-m'), ";

    case RULE_DATE_TIME_LOCAL = "Rule::date()->format(format: 'Y-m-d\TH:i'), ";

    case RULE_DATE_YEAR = self::RULE_NUMERIC->value."'date_format:Y', ";

    case RULE_DATE_TIME = "'date_format:H:i', ";

    case RULE_EXISTS_OPEN = 'Rule::exists(table: ';

    case RULE_EXISTS_CLOSE = ", column: 'id'), ";

    case RULE_UNIQUE_OPEN = 'Rule::unique(table: ';

    case RULE_UNIQUE_MIDDLE = ', column: ';

    case RULE_IMAGE_AND_UNIQUE_CLOSE = '), ';

    case RULE_IN_OPEN = 'Rule::in(values: [';

    case RULE_IN_CLOSE = ']), ';

    case RULE_BETWEEN = "'between:";

    case RULE_MIN = "'min:";

    case RULE_MAX = "'max:";

    case RULE_LENGTH_CLOSE = "', ";

    /**
     * Returns a rule by name.
     */
    public static function getRule(string $name): string
    {
        return match ($name) {
            'url' => self::RULE_URL->value,
            'date' => self::RULE_DATE->value,
            'boolean' => self::RULE_BOOLEAN->value,
            'string' => self::RULE_STRING->value,
            'numeric' => self::RULE_NUMERIC->value,
            'confirmed' => self::RULE_CONFIRMED->value,
            'email' => self::RULE_EMAIL->value,
            'password' => self::RULE_CONFIRMED->value.self::RULE_PASSWORD_MIN->value,
            'required' => self::RULE_REQUIRED->value,
            'nullable' => self::RULE_NULLABLE->value,
            'null' => self::RULE_NULLABLE->value,
            'date_week' => self::RULE_DATE_WEEK->value,
            'week' => self::RULE_DATE_WEEK->value,
            'date_month' => self::RULE_DATE_MONTH->value,
            'month' => self::RULE_DATE_MONTH->value,
            'date_time_local' => self::RULE_DATE_TIME_LOCAL->value,
            'date-time' => self::RULE_DATE_TIME_LOCAL->value,
            'datetime' => self::RULE_DATE_TIME_LOCAL->value,
            'date-time-local' => self::RULE_DATE_TIME_LOCAL->value,
            'date_year' => self::RULE_DATE_YEAR->value,
            'year' => self::RULE_DATE_YEAR->value,
            'date_time' => self::RULE_DATE_TIME->value,
            'time' => self::RULE_DATE_TIME->value,
            default => '',
        };
    }

    /**
     * Build image size rule
     */
    public static function image(int $sizeInKB): string
    {
        return self::RULE_IMAGE->value.$sizeInKB.self::RULE_IMAGE_AND_UNIQUE_CLOSE->value;
    }

    /**
     * Build exists rule
     */
    public static function exists(string $table): string
    {
        $table = GeneratorUtils::pluralSnakeCase($table);

        return self::RULE_EXISTS_OPEN->value."'$table'".self::RULE_EXISTS_CLOSE->value;
    }

    /**
     * Build unique rule
     */
    public static function unique(string $table, string $column): string
    {
        $table = GeneratorUtils::pluralSnakeCase($table);
        $column = GeneratorUtils::singularSnakeCase($column);

        $rule = self::RULE_EMAIL->value.self::RULE_UNIQUE_OPEN->value."'$table', column: '$column'".self::RULE_IMAGE_AND_UNIQUE_CLOSE->value;

        return $rule;
    }

    /**
     * Build in rule
     */
    public static function in(array $values): string
    {
        $options = '';

        foreach ($values as $v) {
            $options .= "'$v', ";
        }

        $options = substr($options, 0, -2);

        return self::RULE_IN_OPEN->value.$options.self::RULE_IN_CLOSE->value;
    }

    /**
     * Build between rule
     */
    public static function between(int $min, int $max): string
    {
        return "'between:$min,$max', ";
    }

    /**
     * Build min rule
     *
     * @return string "min:100"
     */
    public static function min(int $value): string
    {
        return "'min:$value', ";
    }

    /**
     * Build max rule
     */
    public static function max(int $value): string
    {
        return "'max:$value', ";
    }
}
