<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Api\Media\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $credential_id
 * @property string|null $image_id
 * @property string $image_url
 * @property string|null $max_dimension_image_url
 * @property string|null $original_filename
 * @property string $source_type
 * @property string $source_path
 * @property \Illuminate\Support\Carbon|null $expiration_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class EbayMediaImage extends Model
{
    protected $table = 'ebay_media_images';

    protected $fillable = [
        'credential_id',
        'image_id',
        'image_url',
        'max_dimension_image_url',
        'original_filename',
        'source_type',
        'source_path',
        'expiration_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiration_date' => 'datetime',
        ];
    }

    /**
     * @param Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForCredential(Builder $query, int $credentialId): Builder
    {
        return $query->where('credential_id', $credentialId);
    }
}
