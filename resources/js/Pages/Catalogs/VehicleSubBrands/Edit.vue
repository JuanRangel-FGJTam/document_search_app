<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { defineProps, ref } from 'vue';
import BackButton from '@/Components/BackButton.vue';
import InputError from '@/Components/InputError.vue';
import { useToast } from 'vue-toastification';
import { useForm } from '@inertiajs/vue3';
const props = defineProps({
    errors: Object,
    brands: {
        type: Object,
    },
    subBrand: {
        type: Object,
    },
    view: {
        type: String,
    },
});
const toast = useToast();
const form = useForm({
    name: props.subBrand.name,
    brand: props.subBrand.vehicle_brand_id,
});

const submit = () => {
    form.post(route('vehicleSubBrand.update',{'vehicleBrand_id':props.subBrand.id, 'view':props.view}), {
        onSuccess: () => {
            toast.success('Submarca de vehículo actualizada correctamente');
        },
        onError: (errors) => {
            toast.error('Error al actualizar la Submarca de vehículo');
        },
    });
};
</script>

<template>
    <AppLayout title="Actualizar SubMarca de Vehículo">
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto bg-white shadow-lg rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <BackButton class="mr-2" :href="route('vehicleSubBrand.index')" />
                    <h2 class="text-xl font-semibold text-gray-800">
                        Actualizar SubMarca de Vehículo
                    </h2>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700">Marca</label>
                            <select v-model="form.brand" id="brand_id"
                                class="appearance-none w-full bg-white border border-gray-300 text-gray-900 py-2 pr-4 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                                <option value="">Seleccione una marca</option>
                                <option v-for="brand in brands" :key="brand.id" v-bind:value="brand.id">
                                    {{ brand.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.brand" class="text-red-600 text-sm mt-1">{{ form.errors.brand }}</p>
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la submarca</label>
                            <input id="name" v-model="form.name" type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                :class="{ 'border-red-500': form.errors.name }"
                                placeholder="Ingrese el nombre de la submarca" />
                            <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
                        </div>
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
