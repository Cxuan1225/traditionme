export interface CartLine {
    id: number;
    slug: string;
    name: string;
    category: string;
    unitPriceInSen: number;
    quantity: number;
    imageUrl: string | null;
    gradient: string | null;
}

export type CheckoutCartLine = CartLine;

export interface OrderItem {
    id: number;
    productId: number;
    productSlug: string | null;
    productName: string;
    unitPriceInSen: number;
    quantity: number;
    subtotalInSen: number;
    imageUrl: string | null;
    gradient: string | null;
    category: string | null;
}

export interface OrderSummary {
    subtotalInSen: number;
    discountInSen: number;
    shippingInSen: number;
    totalInSen: number;
    itemCount: number;
    subtotal: string;
    discount: string;
    shipping: string;
    total: string;
}

export interface OrderShipping {
    name: string;
    address: string;
    city: string;
    state: string;
    postcode: string;
    phone: string;
}

export interface Order {
    id: number;
    number: string;
    status: string;
    statusLabel: string;
    couponCode: string | null;
    notes: string | null;
    placedAt: string | null;
    paidAt: string | null;
    shippedAt: string | null;
    deliveredAt: string | null;
    summary: OrderSummary;
    shipping: OrderShipping;
    items: OrderItem[];
}
