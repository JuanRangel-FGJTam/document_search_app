<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from 'vue-toastification';
const props = defineProps({
    token: String,
    user: Object,
});

// Importar el plugin de Toast
const toast = useToast();

// Copiar token al portapapeles
function copyToken() {
    navigator.clipboard.writeText(props.token).then(() => {
        // Si usas Laravel Breeze + SweetAlert2 o similar
        toast.success('Token copiado al portapapeles', {
            position: 'top-right',
            timeout: 2000,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: true,
            progress: undefined,
        });
    });
}
</script>

<template>
    <AppLayout title="Token de Usuario">
        <div class="py-6">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                            Token de acceso JWT
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            Usa este token para autenticar peticiones desde sistemas externos.
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6">
                        <div>
                            <h3 class="font-medium text-gray-700 dark:text-gray-200">Usuario autenticado:</h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ user.name }} — {{ user.email }}
                            </p>
                        </div>

                        <div>
                            <h3 class="font-medium text-gray-700 dark:text-gray-200">Token:</h3>
                            <textarea
                                readonly
                                class="w-full h-48 p-4 text-sm bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-gray-700 dark:text-gray-100 font-mono resize-none cursor-pointer hover:ring-2 hover:ring-blue-400 transition"
                                @click="copyToken"
                            >{{ token }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">Haz clic en el token para copiarlo al portapapeles.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
