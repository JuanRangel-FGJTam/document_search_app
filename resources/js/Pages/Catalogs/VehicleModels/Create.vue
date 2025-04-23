<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { defineProps, ref } from 'vue';
import BackButton from '@/Components/BackButton.vue';
import InputError from '@/Components/InputError.vue';
import { useToast } from 'vue-toastification';
import { useForm } from '@inertiajs/vue3';
const props = defineProps({
    errors: Object,
});
const toast = useToast();
const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('vehicleModel.store'), {
        onSuccess: () => {
            toast.success('Modelo de vehículo agregado correctamente');
        },
        onError: (errors) => {
            toast.error('Error al agregar el modelo de vehículo');
        },
    });
};
</script>

<template>
    <AppLayout title="Agregar Marca de Vehículo">
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto bg-white shadow-lg rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <BackButton class="mr-2" :href="route('catalogs.index')" />
                    <h2 class="text-xl font-semibold text-gray-800">
                        Agregar Modelo de Vehículo
                    </h2>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre del modelo</label>
                        <input id="name" v-model="form.name" type="text"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.name }"
                            placeholder="Ingrese el nombre del modelo" />
                        <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded"
                            :disabled="form.processing">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
