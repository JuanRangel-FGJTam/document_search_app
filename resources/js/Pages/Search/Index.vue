<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchInput from '@/Components/SearchInputSm.vue';

const props = defineProps({
    search: {
        type: String,
        default: ""
    }
});

</script>

<template>
    <AppLayout title="Dashboard">
        <div class="p-6 flex flex-col max-w-screen-xl mx-auto border-t">

            <div class="row">
                <SearchInput :initial-search="props.search" />
            </div>

            <h1 class="flex items-center gap-2 my-2 text-xl uppercase font-semibold text-gray-700 dark:text-gray-200">
                <span>Resultados</span>
                <svg class="mr-3 size-5 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M304 48c0-26.5-21.5-48-48-48s-48 21.5-48 48s21.5 48 48 48s48-21.5 48-48zm0 416c0-26.5-21.5-48-48-48s-48 21.5-48 48s21.5 48 48 48s48-21.5 48-48zM48 304c26.5 0 48-21.5 48-48s-21.5-48-48-48s-48 21.5-48 48s21.5 48 48 48zm464-48c0-26.5-21.5-48-48-48s-48 21.5-48 48s21.5 48 48 48s48-21.5 48-48zM142.9 437c18.7-18.7 18.7-49.1 0-67.9s-49.1-18.7-67.9 0s-18.7 49.1 0 67.9s49.1 18.7 67.9 0zm0-294.2c18.7-18.7 18.7-49.1 0-67.9S93.7 56.2 75 75s-18.7 49.1 0 67.9s49.1 18.7 67.9 0zM369.1 437c18.7 18.7 49.1 18.7 67.9 0s18.7-49.1 0-67.9s-49.1-18.7-67.9 0s-18.7 49.1 0 67.9z"/>
                </svg>
            </h1>
            
            <div class="my-2 grid grid-cols-4 gap-4">

                <div class="flex animate-pulse space-x-4 bg-white border shadow p-4">
                    <div class="w-8 rounded-full bg-gray-200"></div>
                    <div class="flex-1 space-y-2 py-1">
                        <div class="h-4 rounded bg-gray-200"></div>
                        <div class="space-y-2">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2 h-4 rounded bg-gray-200"></div>
                                <div class="col-span-1 h-4 rounded bg-gray-200"></div>
                            </div>
                            <div class="h-4 rounded bg-gray-200"></div>
                        </div>
                    </div>
                </div>

                <div class="flex animate-pulse space-x-4 bg-white border shadow p-4">
                    <div class="w-8 rounded-full bg-gray-200"></div>
                    <div class="flex-1 space-y-2 py-1">
                        <div class="h-4 rounded bg-gray-200"></div>
                        <div class="space-y-2">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2 h-4 rounded bg-gray-200"></div>
                                <div class="col-span-1 h-4 rounded bg-gray-200"></div>
                            </div>
                            <div class="h-4 rounded bg-gray-200"></div>
                        </div>
                    </div>
                </div>

                <div class="flex animate-pulse space-x-4 bg-white border shadow p-4">
                    <div class="w-8 rounded-full bg-gray-200"></div>
                    <div class="flex-1 space-y-2 py-1">
                        <div class="h-4 rounded bg-gray-200"></div>
                        <div class="space-y-2">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2 h-4 rounded bg-gray-200"></div>
                                <div class="col-span-1 h-4 rounded bg-gray-200"></div>
                            </div>
                            <div class="h-4 rounded bg-gray-200"></div>
                        </div>
                    </div>
                </div>

                <div class="flex animate-pulse space-x-4 bg-white border shadow p-4">
                    <div class="w-8 rounded-full bg-gray-200"></div>
                    <div class="flex-1 space-y-2 py-1">
                        <div class="h-4 rounded bg-gray-200"></div>
                        <div class="space-y-2">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2 h-4 rounded bg-gray-200"></div>
                                <div class="col-span-1 h-4 rounded bg-gray-200"></div>
                            </div>
                            <div class="h-4 rounded bg-gray-200"></div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

    </AppLayout>
</template>
