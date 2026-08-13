<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\SlotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Slot extends Model
{
    /** @use HasFactory<SlotFactory> */
    use HasFactory;

    protected $fillable = ['service_id', 'date', 'time'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return HasOne<Booking, $this> */
    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    /** @return HasOne<Booking, $this> */
    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)->whereIn('status', [
            BookingStatus::Pending,
            BookingStatus::Approved,
        ]);
    }

    public function isAvailable(): bool
    {
        return ! $this->activeBooking()->exists();
    }

    /** @param Builder<Slot> $query */
    public function scopeAvailable(Builder $query): void
    {
        $query->whereDoesntHave('activeBooking');
    }
}
