<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { defineProps, ref } from 'vue';
import BackButton from '@/Components/BackButton.vue';
</script>

<template>
    <AppLayout title="Agregar Marca de Vehículo">
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto bg-white shadow-lg rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <BackButton class="mr-2" :href="route('catalogs.index')" />
                    <h2 class="text-xl font-semibold text-gray-800">

                    </h2>
                </div>
                <section>
                    <div class="space-y-6">

                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
