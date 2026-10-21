<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { CirclePlus, ListChecks, Minus, Trash2 } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EditableContent } from '@/types/admin';

const props = defineProps<{
    content: EditableContent;
}>();

type OptionDraft = { label: string; is_correct: boolean };
type QuestionDraft = {
    question: string;
    explanation: string;
    points: number;
    options: OptionDraft[];
};

const quiz = props.content.quiz;

const form = useForm({
    passing_score: quiz?.passing_score ?? 70,
    max_attempts: quiz?.max_attempts ?? null,
    time_limit_seconds: quiz?.time_limit_seconds ?? null,
    shuffle_questions: quiz?.shuffle_questions ?? false,
    show_answers_after_submit: quiz?.show_answers_after_submit ?? true,
    questions: (quiz?.questions ?? []).map<QuestionDraft>((question) => ({
        question: question.question,
        explanation: question.explanation ?? '',
        points: question.points,
        options: question.options.map((option) => ({
            label: option.label,
            is_correct: option.is_correct,
        })),
    })),
});

const numberOrNull = (value: string | number): number | null =>
    value === '' ? null : Number(value);

const addQuestion = () => {
    form.questions.push({
        question: '',
        explanation: '',
        points: 1,
        options: [
            { label: '', is_correct: true },
            { label: '', is_correct: false },
        ],
    });
};

const removeQuestion = (index: number) => {
    form.questions.splice(index, 1);
};

const addOption = (questionIndex: number) => {
    form.questions[questionIndex].options.push({
        label: '',
        is_correct: false,
    });
};

const removeOption = (questionIndex: number, optionIndex: number) => {
    const options = form.questions[questionIndex].options;

    if (options.length <= 2) {
        return;
    }

    options.splice(optionIndex, 1);

    if (!options.some((option) => option.is_correct) && options[0]) {
        options[0].is_correct = true;
    }
};

const selectCorrect = (questionIndex: number, optionIndex: number) => {
    form.questions[questionIndex].options.forEach((option, index) => {
        option.is_correct = index === optionIndex;
    });
};

const submit = () => {
    form.put(`/admin/contents/${props.content.id}/quiz`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="rounded-control border border-dashed bg-muted/20 p-4">
        <div class="flex items-center gap-2">
            <ListChecks class="size-4 text-primary" />
            <h3 class="font-display text-base font-semibold">Quiz settings</h3>
            <Link
                href="/admin/contents/${content.id}/quiz/attempts"
                class="ml-auto inline-flex items-center gap-1 rounded-control border bg-background px-3 py-1.5 text-xs font-semibold hover:bg-muted"
            >
                View attempts
            </Link>
        </div>

        <form class="mt-4 space-y-6" @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="grid gap-2">
                    <Label :for="`passing-${content.id}`"
                        >Passing score (%)</Label
                    >
                    <Input
                        :id="`passing-${content.id}`"
                        v-model.number="form.passing_score"
                        type="number"
                        min="1"
                        max="100"
                    />
                    <InputError :message="form.errors.passing_score" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`attempts-${content.id}`">Max attempts</Label>
                    <Input
                        :id="`attempts-${content.id}`"
                        :model-value="form.max_attempts ?? ''"
                        type="number"
                        min="1"
                        max="50"
                        placeholder="Unlimited"
                        @update:model-value="
                            form.max_attempts = numberOrNull($event)
                        "
                    />
                    <InputError :message="form.errors.max_attempts" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`limit-${content.id}`"
                        >Time limit (seconds)</Label
                    >
                    <Input
                        :id="`limit-${content.id}`"
                        :model-value="form.time_limit_seconds ?? ''"
                        type="number"
                        min="60"
                        placeholder="Untimed"
                        @update:model-value="
                            form.time_limit_seconds = numberOrNull($event)
                        "
                    />
                    <InputError :message="form.errors.time_limit_seconds" />
                </div>
            </div>

            <div class="flex flex-wrap gap-5 text-sm">
                <label class="flex items-center gap-2">
                    <input
                        v-model="form.shuffle_questions"
                        type="checkbox"
                        class="size-4 rounded border"
                    />
                    Shuffle questions
                </label>
                <label class="flex items-center gap-2">
                    <input
                        v-model="form.show_answers_after_submit"
                        type="checkbox"
                        class="size-4 rounded border"
                    />
                    Show answers after submission
                </label>
            </div>

            <div class="space-y-4">
                <div
                    v-for="(question, questionIndex) in form.questions"
                    :key="questionIndex"
                    class="rounded-control border bg-card p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p
                            class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Question {{ questionIndex + 1 }}
                        </p>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            class="text-destructive"
                            @click="removeQuestion(questionIndex)"
                        >
                            <Trash2 class="size-3.5" /> Remove
                        </Button>
                    </div>

                    <div class="mt-3 grid gap-4">
                        <div class="grid gap-2">
                            <Label
                                :for="`question-${content.id}-${questionIndex}`"
                                >Question</Label
                            >
                            <Input
                                :id="`question-${content.id}-${questionIndex}`"
                                v-model="question.question"
                                required
                            />
                            <InputError
                                :message="
                                    form.errors[
                                        `questions.${questionIndex}.question`
                                    ]
                                "
                            />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-[1fr_100px]">
                            <div class="grid gap-2">
                                <Label
                                    :for="`explanation-${content.id}-${questionIndex}`"
                                    >Explanation (optional)</Label
                                >
                                <Input
                                    :id="`explanation-${content.id}-${questionIndex}`"
                                    v-model="question.explanation"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label
                                    :for="`points-${content.id}-${questionIndex}`"
                                    >Points</Label
                                >
                                <Input
                                    :id="`points-${content.id}-${questionIndex}`"
                                    v-model.number="question.points"
                                    type="number"
                                    min="1"
                                    max="100"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Answer options — select the correct one
                            </p>
                            <div
                                v-for="(
                                    option, optionIndex
                                ) in question.options"
                                :key="optionIndex"
                                class="flex items-center gap-2"
                            >
                                <input
                                    type="radio"
                                    :name="`correct-${content.id}-${questionIndex}`"
                                    :checked="option.is_correct"
                                    class="size-4"
                                    @change="
                                        selectCorrect(
                                            questionIndex,
                                            optionIndex,
                                        )
                                    "
                                />
                                <Input
                                    v-model="option.label"
                                    :placeholder="`Option ${optionIndex + 1}`"
                                    required
                                />
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    :disabled="question.options.length <= 2"
                                    @click="
                                        removeOption(questionIndex, optionIndex)
                                    "
                                >
                                    <Minus class="size-3.5" />
                                </Button>
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                @click="addOption(questionIndex)"
                            >
                                <CirclePlus class="size-3.5" /> Add option
                            </Button>
                            <InputError
                                :message="
                                    form.errors[
                                        `questions.${questionIndex}.options`
                                    ]
                                "
                            />
                        </div>
                    </div>
                </div>

                <p
                    v-if="!form.questions.length"
                    class="rounded-control border border-dashed p-5 text-center text-sm text-muted-foreground"
                >
                    Add at least one question before learners can take this
                    quiz.
                </p>

                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addQuestion"
                >
                    <CirclePlus class="size-4" /> Add question
                </Button>
            </div>

            <Button type="submit" :disabled="form.processing">Save quiz</Button>
        </form>
    </div>
</template>
