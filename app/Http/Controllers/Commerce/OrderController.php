<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Actions\Commerce\GetUserOrdersAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Order as OrderModel;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request, GetUserOrdersAction $action): Response
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $status = $request->query('status');
        $normalizedStatus = is_string($status) && in_array($status, $this->allowedStatuses(), true)
            ? $status
            : null;

        $orders = $action($user, $normalizedStatus);

        return Inertia::render('account/Orders', [
            'orders' => $this->paginateOrders($orders, $request),
            'filters' => [
                'status' => $normalizedStatus,
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()?->id, 403);

        return Inertia::render('OrderConfirmation', [
            'order' => OrderResource::make($order->load(['items.product']))->resolve($request),
        ]);
    }

    public function accountShow(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()?->id, 403);

        return Inertia::render('account/OrderDetail', [
            'order' => OrderResource::make($order->load(['items.product']))->resolve($request),
        ]);
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
        $data = OrderResource::collection($orders->getCollection())->resolve($request);
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
     * @return list<string>
     */
    private function allowedStatuses(): array
    {
        return ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'paid', 'label' => 'Paid'],
            ['value' => 'shipped', 'label' => 'Shipped'],
            ['value' => 'delivered', 'label' => 'Delivered'],
        ];
    }
}
