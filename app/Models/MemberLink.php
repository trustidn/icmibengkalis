<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MemberLink extends Model
{
    /** Jenis tautan yang didukung beserta label bawaannya. */
    public const TYPES = [
        'website' => 'Website',
        'whatsapp' => 'WhatsApp',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'linkedin' => 'LinkedIn',
        'twitter' => 'X (Twitter)',
    ];

    protected $fillable = [
        'member_id',
        'type',
        'label',
        'value',
        'sort_order',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /** Label tampil: label custom bila diisi, selain itu label bawaan jenis. */
    public function displayLabel(): string
    {
        return filled($this->label) ? $this->label : (self::TYPES[$this->type] ?? ucfirst($this->type));
    }

    /** URL siap klik — nomor WhatsApp dinormalkan ke wa.me, sisanya dipastikan ber-skema. */
    public function url(): string
    {
        $value = trim($this->value);

        if ($this->type === 'whatsapp') {
            $digits = preg_replace('/\D+/', '', $value) ?? '';
            $digits = Str::startsWith($digits, '0') ? '62'.substr($digits, 1) : $digits;

            return 'https://wa.me/'.$digits;
        }

        return Str::startsWith($value, ['http://', 'https://']) ? $value : 'https://'.$value;
    }

    /** WhatsApp dianggap kontak pribadi — tunduk pada toggle show_contact_public. */
    public function isPrivateContact(): bool
    {
        return $this->type === 'whatsapp';
    }
}
