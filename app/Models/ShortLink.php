<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortLink extends Model
{
    protected $fillable = [
        'code',
        'target_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'clicks',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'clicks' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedUrl(): string
    {
        $parts = parse_url($this->target_url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return $this->target_url;
        }

        $query = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        if ($this->utm_source) {
            $query['utm_source'] = $this->utm_source;
        }
        if ($this->utm_medium) {
            $query['utm_medium'] = $this->utm_medium;
        }
        if ($this->utm_campaign) {
            $query['utm_campaign'] = $this->utm_campaign;
        }

        $url = $parts['scheme'].'://'.$parts['host'];
        if (! empty($parts['port'])) {
            $url .= ':'.$parts['port'];
        }
        $url .= $parts['path'] ?? '';
        if ($query) {
            $url .= '?'.http_build_query($query);
        }
        if (! empty($parts['fragment'])) {
            $url .= '#'.$parts['fragment'];
        }

        return $url;
    }
}
