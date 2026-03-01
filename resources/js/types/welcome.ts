export interface WelcomeCategory {
    name: string;
    slug: string;
}

export interface WelcomeProduct {
    name: string;
    slug: string;
    category: string;
    price: string;
    originalPrice: string;
    badge: string;
    gradient: string;
}

export interface WelcomeOccasion {
    name: string;
    slug: string;
    description: string;
    badge: string;
}

export interface WelcomeReview {
    name: string;
    location: string;
    comment: string;
}
