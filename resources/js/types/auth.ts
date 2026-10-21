export type User = {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    email_verified_at: string | null;
    role: 'Admin' | 'Member';
    is_admin: boolean;
    headline: string | null;
    bio: string | null;
    two_factor_enabled: boolean;
    two_factor_pending: boolean;
    created_at: string;
    updated_at: string;
};

export type Auth = {
    user: User;
};
