<?php

declare(strict_types=1);

namespace App\Actions\Commerce;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class GetUserOrdersAction
{
    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function __invoke(User $user, ?string $status = null): LengthAwarePaginator
    {
        $normalizedStatus = $this->normalizeStatus($status);

        return Order::query()
            ->whereBelongsTo($user)
            ->with(['items.product'])
            ->when(
                $normalizedStatus !== null,
                static fn (Builder $query): Builder => $query->where('status', $normalizedStatus),
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    private function normalizeStatus(?string $status): ?string
    {
        if ($status === null || $status === '') {
            return null;
        }

        return in_array($status, $this->allowedStatuses(), true) ? $status : null;
    }

    /**
     * @return list<string>
     */
    private function allowedStatuses(): array
    {
        return ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    }
}
