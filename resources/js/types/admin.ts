export type QuizOptionDraft = {
    id: number;
    label: string;
    is_correct: boolean;
};

export type QuizQuestionDraft = {
    id: number;
    question: string;
    explanation: string | null;
    points: number;
    options: QuizOptionDraft[];
};

export type EditableQuiz = {
    id: number;
    passing_score: number;
    max_attempts: number | null;
    time_limit_seconds: number | null;
    shuffle_questions: boolean;
    show_answers_after_submit: boolean;
    questions: QuizQuestionDraft[];
};

export type EditableContent = {
    id: number;
    title: string;
    type: string;
    position: number;
    is_free_preview: boolean;
    estimated_minutes: number;
    body: string | null;
    video_provider: string | null;
    video_reference: string | null;
    video_duration_seconds: number | null;
    quiz: EditableQuiz | null;
};

export type EditableChapter = {
    id: number;
    title: string;
    description: string | null;
    position: number;
    contents: EditableContent[];
};

export type EditableCourse = {
    id?: number;
    title: string;
    subtitle: string | null;
    description: string;
    category_id: number | null;
    price_minor: number;
    level: string;
    trailer_url: string | null;
    thumbnail_url: string | null;
    thumbnail_alt: string | null;
    learning_outcomes: string;
    requirements: string;
    is_featured: boolean;
};
