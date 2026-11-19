<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /** Dane operatora portalu – wypełnia administrator, trafiają do regulaminu i polityki prywatności. */
    public const OPERATOR_FIELDS = [
        'operator_name' => 'Nazwa firmy / imię i nazwisko',
        'operator_address' => 'Adres',
        'operator_nip' => 'NIP',
        'operator_regon' => 'REGON (opcjonalnie)',
        'operator_email' => 'E-mail kontaktowy',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = Cache::rememberForever('settings', fn () => static::query()->pluck('value', 'key')->all());

        return ($all[$key] ?? null) ?: $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $k => $v) {
            static::query()->updateOrCreate(['key' => $k], ['value' => $v]);
        }
        Cache::forget('settings');
    }

    public static function operatorComplete(): bool
    {
        return static::get('operator_name') && static::get('operator_address') && static::get('operator_email');
    }
}
