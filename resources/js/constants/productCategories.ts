export const productCategoryOptions = [
    { value: 'malay', label: 'Malay' },
    { value: 'chinese', label: 'Chinese' },
    { value: 'indian', label: 'Indian' },
    { value: 'other', label: 'Other' },
] as const;

export type ProductCategory = (typeof productCategoryOptions)[number]['value'];

const productCategoryLabels: Record<ProductCategory, string> = {
    malay: 'Malay',
    chinese: 'Chinese',
    indian: 'Indian',
    other: 'Other',
};

export function getProductCategoryLabel(category: string): string {
    return productCategoryLabels[category as ProductCategory] ?? category;
}
