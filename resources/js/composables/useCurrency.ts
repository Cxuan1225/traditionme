export function toRinggit(valueInSen: number): string {
    return `RM ${(valueInSen / 100).toFixed(2)}`;
}

export function formatDiscount(valueInSen: number): string {
    return `- ${toRinggit(valueInSen)}`;
}
