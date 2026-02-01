<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Email extends Model
{
    protected $fillable = [
        'message_id',
        'subject',
        'from_email',
        'from_name',
        'to',
        'cc',
        'bcc',
        'text_body',
        'html_body',
        'email_date',
        'source',
        'has_attachments',
        'headers',
    ];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'headers' => 'array',
        'email_date' => 'datetime',
        'has_attachments' => 'boolean',
    ];

    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }

    /**
     * Scope für Suche nach Absender
     */
    public function scopeFromEmail($query, string $email)
    {
        return $query->where('from_email', 'like', "%{$email}%");
    }

    /**
     * Scope für Datumsbereich
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('email_date', [$from, $to]);
    }
}
