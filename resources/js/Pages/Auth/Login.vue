<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
        <div class="relative flex flex-col justify-center w-full h-screen dark:bg-gray-900 sm:p-0 lg:flex-row">
            <div class="flex flex-col flex-1 w-full lg:w-1/2">
                <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                    <div>
                        <div class="mb-6">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Iniciar Sesión</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Ingresa tu correo electrónico y contraseña para acceder.
                            </p>
                        </div>
                        <div>
                            <AuthenticationCard>
                                <form @submit.prevent="submit">
                                    <div>
                                        <InputLabel for="email" value="Correo Electrónico" />
                                        <TextInput id="email" v-model="form.email" type="email"
                                            class="mt-1 block w-full" required autofocus autocomplete="username" />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <div class="mt-4">
                                        <InputLabel for="password" value="Contraseña" />
                                        <TextInput id="password" v-model="form.password" type="password"
                                            class="mt-1 block w-full" required autocomplete="current-password" />
                                        <InputError class="mt-2" :message="form.errors.password" />
                                    </div>

                                    <div class="block mt-4">
                                        <label class="flex items-center">
                                            <Checkbox v-model:checked="form.remember" name="remember" />
                                            <span class="ms-2 text-sm text-gray-600">Recordar sesión</span>
                                        </label>
                                    </div>

                                    <div class="flex items-center justify-end mt-4">
                                        <Link v-if="canResetPassword" :href="route('password.request')"
                                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Olvidaste tú contraseña?
                                        </Link>

                                        <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }"
                                            :disabled="form.processing">
                                            Iniciar sesión
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </AuthenticationCard>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden lg:flex items-center justify-center w-1/2 bg-blue-950 dark:bg-gray-700 p-10 text-center">
                <div class="max-w-sm">
                    <a href="https://www.fgjtam.gob.mx" target="_blank" class="block mb-4">
                        <AuthenticationCardLogo class="mx-auto" />
                    </a>
                    <h2 class="text-2xl text-gray-300 dark:text-gray-100 font-semibold">Constancia de Extravío de
                        Documentos</h2>
                    <p class="text-sm text-gray-400 mt-2">Accede a la plataforma para gestionar tus reportes de manera
                        rápida y segura.</p>
                </div>
            </div>
        </div>
    </div>

</template>
