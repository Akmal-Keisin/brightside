<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $order_number
 * @property int|null $user_id
 * @property int|null $course_id
 * @property string $customer_name
 * @property string $customer_email
 * @property string $course_title
 * @property string $currency
 * @property int $subtotal_minor
 * @property int $total_minor
 * @property OrderStatus $status
 * @property string|null $payment_url
 * @property Carbon|null $paid_at
 * @property Carbon|null $expires_at
 */
#[Fillable([
    'order_number', 'user_id', 'course_id', 'customer_name', 'customer_email',
    'course_title', 'currency', 'subtotal_minor', 'discount_minor', 'tax_minor',
    'total_minor', 'status', 'payment_provider', 'payment_reference', 'payment_url',
    'paid_at', 'expires_at', 'notes',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'subtotal_minor' => 'integer',
            'discount_minor' => 'integer',
            'tax_minor' => 'integer',
            'total_minor' => 'integer',
            'status' => OrderStatus::class,
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    public function isRefunded(): bool
    {
        return $this->status === OrderStatus::Refunded;
    }

    public function isPayable(): bool
    {
        return $this->isPending() && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /** @param Builder<Order> $query */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Paid);
    }

    /** @param Builder<Order> $query */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasOne<Enrollment, $this> */
    public function enrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class);
    }
}
