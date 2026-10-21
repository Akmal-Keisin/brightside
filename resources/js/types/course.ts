export type CourseSummary = {
    id: number;
    title: string;
    slug: string;
    subtitle?: string | null;
    thumbnail_url?: string | null;
    price?: string;
    price_minor?: number;
    is_free?: boolean;
    level?: string;
    contents_count?: number;
    duration_minutes?: number;
    enrollments_count?: number;
    rating_avg?: number | null;
    rating_count?: number;
    is_featured?: boolean;
    category?: { name: string; slug?: string; color?: string | null } | null;
    author?: { name: string } | null;
};
