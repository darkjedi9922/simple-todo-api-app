<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    const STATUS_ENUM = ['View', 'In Progress', 'Done'];
    const STATUS_DEFAULT = self::STATUS_ENUM[0];

    protected $fillable = ['title', 'description', 'status', 'user_id'];

    /**
     * @return array enum statuses as ['status-name', ...]
     */
    public static function getStatusesInKebabCase(): array
    {
        return array_map(function ($status) {
            return str_replace(' ', '-', strtolower($status));
        }, self::STATUS_ENUM);
    }

    /**
     * @param string $status like 'status-name'
     * @return string like 'Status Name'
     */
    public static function getStatusFromKebabCase(string $status): string
    {
        return mb_convert_case(str_replace('-', ' ', $status), MB_CASE_TITLE);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
