<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\GetOrdersAction;
use App\Actions\Admin\UpdateOrderStatusAction;
use App\DTOs\Admin\UpdateOrderStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Resources\AdminOrderResource;
use App\Models\Order;
use App\Models\Order as OrderModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request, GetOrdersAction $action): Response
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $normalizedStatus = is_string($status) && in_array($status, UpdateOrderStatusData::allowedStatuses(), true)
            ? $status
            : null;
        $normalizedSearch = is_string($search) && trim($search) !== '' ? trim($search) : null;

        $orders = $action(
            $normalizedStatus,
            $normalizedSearch,
        );

        return Inertia::render('admin/Orders', [
            'orders' => $this->paginateOrders($orders, $request),
            'filters' => [
                'status' => $normalizedStatus,
                'search' => $normalizedSearch,
            ],
            'statusOptions' => $this->statusOptions(),
            'capabilities' => [
                'canUpdateStatus' => $request->user()?->can('orders.update_status') ?? false,
            ],
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        return Inertia::render('admin/OrderDetail', [
            'order' => AdminOrderResource::make($order->load(['user', 'items.product']))->resolve($request),
            'statusOptions' => $this->statusOptions(),
            'capabilities' => [
                'canUpdateStatus' => $request->user()?->can('orders.update_status') ?? false,
            ],
        ]);
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        UpdateOrderStatusAction $action,
    ): RedirectResponse {
        $action($order, UpdateOrderStatusData::fromRequest($request));

        return back()->with('status', 'Order status updated successfully.');
    }

    /**
     * @param  LengthAwarePaginator<int, OrderModel>  $orders
     * @return array{
     *     data: array<int, array<string, mixed>>,
     *     meta: array<string, int|null>,
     *     links: array<int, array{url: string|null, label: string, active: bool}>
     * }
     */
    private function paginateOrders(LengthAwarePaginator $orders, Request $request): array
    {
        /** @var array<int, array<string, mixed>> $data */
        $data = AdminOrderResource::collection($orders->getCollection())->resolve($request);
        $links = $orders->linkCollection()
            ->map(
                /**
                 * @return array{url: string|null, label: string, active: bool}
                 */
                static function (mixed $link): array {
                    $linkData = is_array($link) ? $link : [];
                    $url = $linkData['url'] ?? null;
                    $label = $linkData['label'] ?? '';
                    $active = $linkData['active'] ?? false;

                    return [
                        'url' => is_string($url) ? $url : null,
                        'label' => is_string($label) ? $label : '',
                        'active' => is_bool($active) ? $active : false,
                    ];
                },
            )
            ->values()
            ->all();

        return [
            'data' => $data,
            'meta' => [
                'currentPage' => $orders->currentPage(),
                'lastPage' => $orders->lastPage(),
                'perPage' => $orders->perPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
            'links' => $links,
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            static fn (string $status): array => [
                'value' => $status,
                'label' => ucfirst($status),
            ],
            UpdateOrderStatusData::allowedStatuses(),
        );
    }
}
