<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\Admin\UpdateOrderStatusData;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class UpdateOrderStatusAction
{
    public function __construct(
        private readonly RestoreStockAction $restoreStockAction,
    ) {}

    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['pending', 'paid', 'cancelled'],
        'paid' => ['paid', 'shipped', 'cancelled'],
        'shipped' => ['shipped', 'delivered'],
        'delivered' => ['delivered'],
        'cancelled' => ['cancelled'],
    ];

    public function __invoke(Order $order, UpdateOrderStatusData $data): Order
    {
        $this->ensureTransitionIsAllowed($order->status, $data->status);

        $changes = [
            'status' => $data->status,
        ];

        if ($data->notes !== null) {
            $changes['notes'] = $data->notes;
        }

        $timestamp = Carbon::now();

        match ($data->status) {
            'paid' => $changes['paid_at'] = $order->paid_at ?? $timestamp,
            'shipped' => $changes['shipped_at'] = $order->shipped_at ?? $timestamp,
            'delivered' => $changes['delivered_at'] = $order->delivered_at ?? $timestamp,
            default => null,
        };

        $order->fill($changes);
        $order->save();

        if ($data->status === 'cancelled') {
            ($this->restoreStockAction)($order);
        }

        return $order->refresh()->load(['user', 'items.product']);
    }

    private function ensureTransitionIsAllowed(string $currentStatus, string $nextStatus): void
    {
        $allowedTransitions = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        if (in_array($nextStatus, $allowedTransitions, true)) {
            return;
        }

        throw ValidationException::withMessages([
            'status' => sprintf(
                'Orders with status "%s" cannot transition to "%s".',
                $currentStatus,
                $nextStatus,
            ),
        ]);
    }
}
