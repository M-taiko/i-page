<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $table = 'user_preferences';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'color_scheme',
        'font_size',
        'language',
        'compact_mode',
        'email_notifications',
        'push_notifications',
        'sms_notifications',
        'notify_new_guest',
        'notify_channel_updates',
        'notify_system_alerts',
        'notify_weekly_report',
    ];

    protected $casts = [
        'compact_mode' => 'boolean',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'notify_new_guest' => 'boolean',
        'notify_channel_updates' => 'boolean',
        'notify_system_alerts' => 'boolean',
        'notify_weekly_report' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
