<?php

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Enums\VideoProvider;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Content;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\User;
use App\Services\EnrollmentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@brightside.test'],
            ['name' => 'Ari Nugroho', 'password' => Hash::make('password')],
        );

        $admin->forceFill([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
            'terms_version' => (string) config('brightside.terms_version', '1.0'),
        ])->save();

        $member = User::query()->firstOrCreate(
            ['email' => 'member@brightside.test'],
            ['name' => 'Rani Putri', 'password' => Hash::make('password')],
        );

        $member->forceFill([
            'role' => UserRole::Member,
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
            'terms_version' => (string) config('brightside.terms_version', '1.0'),
        ])->save();

        $categories = collect([
            ['name' => 'Design', 'slug' => 'design', 'color' => '#2563EB', 'description' => 'Interfaces, systems, and the craft of making things feel right.'],
            ['name' => 'Development', 'slug' => 'development', 'color' => '#059669', 'description' => 'Build reliable products with modern tools and calm code.'],
            ['name' => 'Product', 'slug' => 'product', 'color' => '#EA580C', 'description' => 'Research, strategy, and shipping work that matters.'],
            ['name' => 'Business', 'slug' => 'business', 'color' => '#7C3AED', 'description' => 'Grow a small studio without losing the plot.'],
        ])->mapWithKeys(function (array $category, int $index): array {
            return [$category['slug'] => Category::query()->firstOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'position' => $index],
            )];
        });

        $designSystems = $this->seedCourse([
            'category_id' => $categories['design']->id,
            'created_by' => $admin->id,
            'title' => 'Design Systems That Ship',
            'subtitle' => 'Turn a pile of components into a shared language your team actually uses.',
            'description' => "A design system is not a Figma library. It is a set of decisions, encoded in components, that help a team move quickly without making a mess.\n\nIn this course you will build a small system from the ground up: tokens, type, spacing, components, documentation, and the habits that keep it alive.",
            'price_minor' => 249000,
            'level' => CourseLevel::Intermediate,
            'is_featured' => true,
            'learning_outcomes' => [
                'Choose tokens that survive real product work.',
                'Build accessible components with clear states.',
                'Document decisions so teammates can move independently.',
                'Keep a system useful after the launch excitement fades.',
            ],
            'requirements' => [
                'Comfort with Figma or a similar design tool.',
                'Some experience shipping interfaces.',
            ],
        ], [
            [
                'title' => 'Start with decisions, not components',
                'description' => 'The foundation of a system that lasts.',
                'contents' => [
                    [
                        'type' => ContentType::Article,
                        'title' => 'What a design system is really for',
                        'minutes' => 9,
                        'free' => true,
                        'body' => "## A shared language\n\nA design system is a contract between the people who design and the people who build. It says: these are our colors, this is how spacing works, and this is what a button means.\n\n> If nobody can find the decision, it is not a system. It is a folder.\n\nStart with the problems the team repeats. A good system removes repeated decisions, not personality.",
                    ],
                    [
                        'type' => ContentType::Article,
                        'title' => 'Tokens: naming the small stuff',
                        'minutes' => 12,
                        'body' => "## From raw values to intent\n\nA token is a name for a decision. `cobalt-600` is a value. `color-action` is a decision.\n\n- Name tokens for intent, not appearance.\n- Keep one source of truth.\n- Let semantic tokens point at primitive tokens.\n\nThat extra layer is what lets dark mode and rebrands happen without a rescue mission.",
                    ],
                    [
                        'type' => ContentType::Video,
                        'title' => 'Building a token set',
                        'minutes' => 14,
                        'provider' => VideoProvider::YouTube,
                        'reference' => 'dQw4w9WgXcQ',
                        'duration' => 840,
                    ],
                ],
            ],
            [
                'title' => 'Components in practice',
                'description' => 'From a button to a pattern library.',
                'contents' => [
                    [
                        'type' => ContentType::Article,
                        'title' => 'The anatomy of a resilient button',
                        'minutes' => 10,
                        'body' => "## One component, many jobs\n\nA button looks simple until you meet loading, disabled, destructive, and icon-only states.\n\n1. Define the semantic variants.\n2. Make focus visible.\n3. Keep the hit area comfortable.\n4. Document when not to use it.",
                    ],
                    [
                        'type' => ContentType::Quiz,
                        'title' => 'Check your system instincts',
                        'minutes' => 5,
                        'quiz' => [
                            'passing_score' => 70,
                            'max_attempts' => 3,
                            'questions' => [
                                [
                                    'question' => 'Which token name is most useful in a product team?',
                                    'explanation' => 'Semantic names describe intent and survive visual changes.',
                                    'options' => [
                                        ['label' => 'blue-600', 'correct' => false],
                                        ['label' => 'color-action', 'correct' => true],
                                        ['label' => '#2563EB', 'correct' => false],
                                    ],
                                ],
                                [
                                    'question' => 'What should a design system remove?',
                                    'explanation' => 'The point is to reduce repeated decisions, not remove craft.',
                                    'options' => [
                                        ['label' => 'Repeated decisions', 'correct' => true],
                                        ['label' => 'All visual personality', 'correct' => false],
                                        ['label' => 'Every component', 'correct' => false],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $laravelCourse = $this->seedCourse([
            'category_id' => $categories['development']->id,
            'created_by' => $admin->id,
            'title' => 'Laravel for Product Teams',
            'subtitle' => 'A practical tour of the framework, from routes to queues, without the cargo cult.',
            'description' => "Laravel is at its best when it gets out of the way. This course is a tour of the pieces that matter for a real product: routing, validation, authorization, queues, and tests.\n\nWe will build the habits that keep a codebase pleasant six months later.",
            'price_minor' => 349000,
            'level' => CourseLevel::Beginner,
            'is_featured' => true,
            'learning_outcomes' => [
                'Trace a request from route to response.',
                'Validate and authorize at the edges.',
                'Move slow work to a queue.',
                'Write tests that protect behavior.',
            ],
            'requirements' => [
                'Basic PHP familiarity.',
                'A terminal you are not afraid of.',
            ],
        ], [
            [
                'title' => 'The request lifecycle',
                'description' => 'Follow one request all the way through.',
                'contents' => [
                    [
                        'type' => ContentType::Article,
                        'title' => 'Routes, middleware, controllers',
                        'minutes' => 11,
                        'free' => true,
                        'body' => "## The shape of a request\n\nA request enters through the front controller, finds a route, passes through middleware, and lands in a controller.\n\n```php\nRoute::get('/courses', [CourseController::class, 'index']);\n```\n\nKeep controllers thin. Put reusable decisions in services or actions when they earn it.",
                    ],
                    [
                        'type' => ContentType::Video,
                        'title' => 'Reading a stack trace without panic',
                        'minutes' => 8,
                        'provider' => VideoProvider::YouTube,
                        'reference' => 'dQw4w9WgXcQ',
                        'duration' => 480,
                    ],
                    [
                        'type' => ContentType::Article,
                        'title' => 'Validation at the edge',
                        'minutes' => 9,
                        'body' => "## Trust nothing from the browser\n\nForm requests keep validation close to the boundary. They also make controllers readable.\n\n- Validate shape and permission separately.\n- Return useful errors.\n- Never let mass assignment decide a role.",
                    ],
                ],
            ],
        ]);

        $freeCourse = $this->seedCourse([
            'category_id' => $categories['product']->id,
            'created_by' => $admin->id,
            'title' => 'Your First Week with Brightside',
            'subtitle' => 'A tiny free course to help you find your way around.',
            'description' => "This short free course shows you how Brightside works: lessons, progress, and quizzes.\n\nFinish it in under twenty minutes, then pick a course that stretches you.",
            'price_minor' => 0,
            'level' => CourseLevel::Beginner,
            'is_featured' => false,
            'learning_outcomes' => [
                'Find your way around the learning player.',
                'Understand how progress is tracked.',
            ],
            'requirements' => [],
        ], [
            [
                'title' => 'Getting oriented',
                'description' => 'The very basics.',
                'contents' => [
                    [
                        'type' => ContentType::Article,
                        'title' => 'How learning works here',
                        'minutes' => 5,
                        'free' => true,
                        'body' => "## Small steps, visible progress\n\nEvery lesson you complete moves the bar. Chapters show progress too, so you always know where you are.\n\n- Articles are read in the player.\n- Videos can be watched at your pace.\n- Quizzes need a passing score to complete.\n\nThat is it. Go learn something good.",
                    ],
                    [
                        'type' => ContentType::Quiz,
                        'title' => 'A two-question warm-up',
                        'minutes' => 3,
                        'quiz' => [
                            'passing_score' => 50,
                            'max_attempts' => null,
                            'questions' => [
                                [
                                    'question' => 'What completes a quiz lesson?',
                                    'explanation' => 'A passing score completes the quiz lesson.',
                                    'options' => [
                                        ['label' => 'Opening it', 'correct' => false],
                                        ['label' => 'A passing score', 'correct' => true],
                                        ['label' => 'Refreshing the page', 'correct' => false],
                                    ],
                                ],
                                [
                                    'question' => 'Where can you see your progress?',
                                    'explanation' => 'The dashboard and course cards both show progress.',
                                    'options' => [
                                        ['label' => 'Only in emails', 'correct' => false],
                                        ['label' => 'On your dashboard and course cards', 'correct' => true],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $enrollmentService = app(EnrollmentService::class);

        foreach ([$designSystems, $laravelCourse, $freeCourse] as $course) {
            $enrollmentService->refreshCourseCounters($course);
        }

        $enrollmentService->enrollForFree($member, $freeCourse);

        $this->command?->info('Seeded Brightside demo content.');
        $this->command?->line('Admin: admin@brightside.test / password (enable 2FA under Settings → Security before opening the studio)');
        $this->command?->line('Member: member@brightside.test / password');
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array<string, mixed>>  $chapters
     */
    private function seedCourse(array $attributes, array $chapters): Course
    {
        $slug = Str::slug($attributes['title']);

        $course = Course::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'slug' => $slug,
                'subtitle' => null,
                'thumbnail_path' => null,
                'thumbnail_alt' => null,
                'trailer_url' => null,
                'currency' => 'IDR',
                'language' => 'en',
                'status' => CourseStatus::Published,
                'published_at' => now(),
                'duration_seconds' => 0,
                'contents_count' => 0,
                'enrollments_count' => 0,
                'learning_outcomes' => [],
                'requirements' => [],
                'is_featured' => false,
                ...$attributes,
            ],
        );

        if ($course->chapters()->exists()) {
            return $course;
        }

        foreach ($chapters as $chapterIndex => $chapterData) {
            $chapter = Chapter::query()->create([
                'course_id' => $course->id,
                'title' => $chapterData['title'],
                'description' => $chapterData['description'] ?? null,
                'position' => $chapterIndex + 1,
            ]);

            foreach ($chapterData['contents'] as $contentIndex => $contentData) {
                $quizData = $contentData['quiz'] ?? null;

                $content = Content::query()->create([
                    'chapter_id' => $chapter->id,
                    'type' => $contentData['type'],
                    'title' => $contentData['title'],
                    'position' => $contentIndex + 1,
                    'is_free_preview' => (bool) ($contentData['free'] ?? false),
                    'estimated_minutes' => $contentData['minutes'] ?? 5,
                    'body' => $contentData['type'] === ContentType::Article ? ($contentData['body'] ?? null) : null,
                    'video_provider' => $contentData['provider'] ?? null,
                    'video_reference' => $contentData['reference'] ?? null,
                    'video_duration_seconds' => $contentData['duration'] ?? null,
                    'transcript' => null,
                ]);

                if ($contentData['type'] === ContentType::Quiz && is_array($quizData)) {
                    $quiz = Quiz::query()->create([
                        'content_id' => $content->id,
                        'passing_score' => $quizData['passing_score'] ?? 70,
                        'max_attempts' => $quizData['max_attempts'] ?? null,
                        'time_limit_seconds' => $quizData['time_limit_seconds'] ?? null,
                        'shuffle_questions' => $quizData['shuffle_questions'] ?? false,
                        'show_answers_after_submit' => $quizData['show_answers_after_submit'] ?? true,
                    ]);

                    foreach ($quizData['questions'] as $questionIndex => $questionData) {
                        $question = $quiz->questions()->create([
                            'question' => $questionData['question'],
                            'explanation' => $questionData['explanation'] ?? null,
                            'position' => $questionIndex + 1,
                            'points' => 1,
                        ]);

                        foreach ($questionData['options'] as $optionIndex => $optionData) {
                            $question->options()->create([
                                'label' => $optionData['label'],
                                'is_correct' => (bool) $optionData['correct'],
                                'position' => $optionIndex + 1,
                            ]);
                        }
                    }
                }
            }
        }

        return $course;
    }
}
