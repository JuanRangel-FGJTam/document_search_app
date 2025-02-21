<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
    colspan: Number,
    ObjectData: Object,
})
</script>

<template>
    <td :colspan="colspan">
        <div class="flex items-center justify-between border-gray-200 bg-white px-4 py-3 sm:px-6">
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando <span class="font-medium">{{ ObjectData.to }}</span>
                        de <span class="font-medium">{{ ObjectData.total }}</span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="inline-flex -space-x-px text-base h-10" aria-label="Pagination">
                        <!-- Prev Page Link -->
                        <Link v-if="ObjectData.prev_page_url" :href="ObjectData.prev_page_url"
                            class="flex items-center justify-center px-4 h-10 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </Link>

                        <!-- Page Links -->
                        <template v-for="vlink in ObjectData.links" :key="vlink.label">
                            <Link v-if="vlink.url" :href="vlink.url" aria-current="page"
                                class="flex items-center justify-center px-4 h-10 leading-tight"
                                :class="[vlink.active ? 'text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white']">
                                {{ vlink.label }}
                            </Link>
                            <!-- If link URL is null, render span instead -->
                            <span v-else
                                class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                                {{ vlink.label }}
                            </span>
                        </template>

                        <!-- Next Page Link -->
                        <Link v-if="ObjectData.next_page_url" :href="ObjectData.next_page_url"
                            class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </Link>
                    </nav>
                </div>
            </div>
        </div>
    </td>
</template>
