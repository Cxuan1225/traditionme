export interface ShopProduct {
    id: number;
    name: string;
    slug: string;
    category: string;
    priceInSen: number;
    originalPriceInSen: number | null;
    badge: string | null;
    gradient: string | null;
    imageUrl: string | null;
    inStock: boolean;
    stockQuantity: number | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedResponse<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface ShopFilters {
    category: string;
    search: string;
    sort: string;
}
