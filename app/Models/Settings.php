<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int    $id
 * @property string $key
 * @property array  $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Settings extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $fillable = [
        'key',
        'title',
        'value',
    ];
    protected $casts = [
        'value' => 'array',
    ];
}
