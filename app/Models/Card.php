<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'name',
        'rank',
        'suit',
        'value',
        'code',
        'description',
        'photo_path',
        'is_active',
    ];

    protected $appends = [
        'photo_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'value' => 'integer',
        ];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        $path = str_replace('\\', '/', ltrim((string) $this->photo_path, '/'));

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return '/admin/cards/'.$this->id.'/photo'.($this->updated_at ? '?v='.$this->updated_at->getTimestamp() : '');
    }
}
