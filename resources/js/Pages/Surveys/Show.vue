<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { defineProps, ref } from 'vue';
import BackButton from '@/Components/BackButton.vue';
const props = defineProps({
    survey: Object
});

const ratings = ref({
    misplacement_id: props.survey.misplacement_id,
    rating_1: props.survey.rating_1,
    rating_2: props.survey.rating_2,
    rating_3: props.survey.rating_3,
    question_1: props.survey.question_1,
    question_2: props.survey.question_2,
    question_3: props.survey.question_3,
    question_4: props.survey.question_4,
    is_comments: props.survey.is_comments,
    comments: props.survey.comments ?? 'Sin comentarios'
});

const ratingLabels = ['Difícil', 'Medianamente difícil', 'Fácil', 'Medianamente fácil', 'Muy fácil'];
</script>

<template>
    <AppLayout title="Encuesta de Constancias">
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto bg-white shadow-lg rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <BackButton class="mr-2" :href="route('surveys.index')" />
                    <h2 class="text-xl font-semibold text-gray-800">
                        Encuesta de Constancias - Folio {{ survey.misplacement.document_number }}
                    </h2>
                </div>

                <section>
                    <div class="space-y-6">
                        <div v-for="(question, key) in [
                            { label: '¿Qué tan difícil fue ingresar al rubro de Extravío de Documentos?', model: 'rating_1' },
                            { label: '¿Qué tan difícil le fue generar su Constancia?', model: 'rating_2' },
                            { label: '¿Qué tan satisfecho se encuentra con el servicio?', model: 'rating_3' }
                        ]" :key="key">
                            <p class="font-medium text-gray-700">{{ question.label }}</p>
                            <div class="flex space-x-2 mt-2 items-center">
                                <div v-for="(label, i) in ratingLabels" :key="i" class="p-2 flex flex-col items-center"
                                    :class="{ 'text-yellow-400': ratings[question.model] >= i + 1, 'text-gray-300': ratings[question.model] < i + 1 }">
                                    <svg :class="{ 'text-yellow-400': ratings[question.model] >= i + 1, 'text-gray-300': ratings[question.model] < i + 1 }"
                                        xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                        width="24px" fill="currentColor">
                                        <path
                                            d="m233-120 65-281L80-590l288-25 112-265 112 265 288 25-218 189 65 281-247-149-247 149Z" />
                                    </svg>
                                    <span class="text-xs text-gray-600">{{ label }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="font-medium text-gray-700">¿Fue útil la información brindada para el llenado?</p>
                            <div class="flex space-x-4 mt-2">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" v-model="ratings.question_1" :value="1" class="form-radio"
                                        disabled />
                                    <span>Sí</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" v-model="ratings.question_1" :value="0" class="form-radio"
                                        disabled />
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <p class="font-medium text-gray-700">¿Solicitó ayuda telefónica?</p>
                            <div class="flex space-x-4 mt-2">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" v-model="ratings.question_2" :value="1" class="form-radio"
                                        disabled />
                                    <span>Sí</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" v-model="ratings.question_2" :value="0" class="form-radio"
                                        disabled />
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-200">¿El servidor público le solicitó algún pago
                                a cambio?</p>
                            <div class="flex space-x-4 mt-2">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" v-model="ratings.question_3" :value="1" class="form-radio"
                                        disabled />
                                    <span>Sí</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" v-model="ratings.question_3" :value="0" class="form-radio"
                                        disabled />
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-200">¿Sintió discriminación en algún momento?</p>
                            <div class="flex space-x-4 mt-2">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" v-model="ratings.question_4" :value="1" class="form-radio"
                                        disabled />
                                    <span>Sí</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" v-model="ratings.question_4" :value="0" class="form-radio"
                                        disabled />
                                    <span>No</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700">¿Tiene algún comentario sobre el servicio?</p>
                            <textarea v-model="ratings.comments" disabled
                                class="w-full p-2 border rounded-md dark:bg-gray-800 dark:border-gray-600" rows="3">
                            </textarea>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
