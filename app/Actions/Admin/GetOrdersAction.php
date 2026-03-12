<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\Admin\UpdateOrderStatusData;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class GetOrdersAction
{
    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function __invoke(?string $status = null, ?string $search = null): LengthAwarePaginator
    {
        $normalizedStatus = $this->normalizeStatus($status);
        $normalizedSearch = $this->normalizeSearch($search);
        $matchingOrderId = $normalizedSearch !== null
            ? $this->extractOrderIdFromSearch($normalizedSearch)
            : null;

        return Order::query()
            ->with(['user:id,name,email', 'items.product'])
            ->when(
                $normalizedStatus !== null,
                static fn (Builder $query): Builder => $query->where('status', $normalizedStatus),
            )
            ->when(
                $normalizedSearch !== null,
                static function (Builder $query) use (
                    $matchingOrderId,
                    $normalizedSearch,
                ): Builder {
                    return $query->where(
                        static function (Builder $nestedQuery) use (
                            $matchingOrderId,
                            $normalizedSearch,
                        ): void {
                            if ($matchingOrderId !== null) {
                                $nestedQuery->whereKey($matchingOrderId)
                                    ->orWhereHas(
                                        'user',
                                        static fn (Builder $userQuery): Builder => $userQuery
                                            ->where('name', 'like', '%'.$normalizedSearch.'%'),
                                    );

                                return;
                            }

                            $nestedQuery->whereHas(
                                'user',
                                static fn (Builder $userQuery): Builder => $userQuery
                                    ->where('name', 'like', '%'.$normalizedSearch.'%'),
                            );
                        },
                    );
                },
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    private function normalizeStatus(?string $status): ?string
    {
        if ($status === null || $status === '') {
            return null;
        }

        return in_array($status, UpdateOrderStatusData::allowedStatuses(), true) ? $status : null;
    }

    private function normalizeSearch(?string $search): ?string
    {
        if ($search === null) {
            return null;
        }

        $normalizedSearch = trim($search);

        return $normalizedSearch !== '' ? $normalizedSearch : null;
    }

    private function extractOrderIdFromSearch(string $search): ?int
    {
        if (preg_match('/^TM-(\d+)$/i', $search, $matches) === 1) {
            return max(1, (int) ltrim($matches[1], '0'));
        }

        if (preg_match('/^\d+$/', $search) === 1) {
            return max(1, (int) ltrim($search, '0'));
        }

        return null;
    }
}
