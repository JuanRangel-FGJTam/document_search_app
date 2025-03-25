<script setup>
import { onMounted, ref, watch } from 'vue'
import { Link, router,useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
const toast = useToast();
const props = defineProps({
    errors:Object,
    user:Object,
})

const form = useForm({
    name:props.user.name,
    email:props.user.email,
    password: null,
});


const submit = () => {
        form.post(route('users.update',props.user.id), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onSuccess() {
                toast.success('Usuario actualizado exitosamente');
            }
        });
};

</script>

<template>
    <AppLayout title="Actualizar usuario">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <section class="bg-white dark:bg-gray-900">
                        <div class="p-6">
                            <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Actualizar usuario</h2>
                            <form @submit.prevent="submit">
                                <div class="grid gap-4 sm:grid-cols-3 sm:gap-6">
                                    <div class="sm:col-span-1">
                                        <label for="name"
                                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                            Nombre
                                        </label>
                                        <input type="text" name="name" id="name"
                                            class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                            placeholder="Ingrese nombre de usuario" v-model="form.name">
                                            <InputError v-if="errors.name" :message="errors.name" />
                                    </div>
                                    <div class="w-full">
                                        <label for="email"
                                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                                        <input type="email" name="email" id="email"
                                            class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                            placeholder="Ingrese email" v-model="form.email">
                                            <InputError v-if="errors.email" :message="errors.email" />
                                    </div>
                                    <div class="w-full">
                                        <label for="password"
                                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                                        <input type="password" name="password" id="password"
                                            class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                            placeholder="Deja en blanco si no quieres cambiar la contraseña" v-model="form.password">
                                            <InputError v-if="errors.password" :message="errors.password" />
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <Link :href="route('users.index')"
                                        class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-red-700 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-primary-900 hover:bg-red-800">
                                        Cancelar
                                    </Link>
                                    <button type="submit"
                                        class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-700 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-primary-900 hover:bg-blue-800">
                                        Actualizar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
