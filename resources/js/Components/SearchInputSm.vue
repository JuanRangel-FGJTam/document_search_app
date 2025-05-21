<script setup>
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import InputError from '@/Components/InputError.vue';

const toast = useToast();

const props = defineProps({
    initialSearch: {
        type: String,
        default: ""
    }
});

const searchForm = useForm({
    "search": props.initialSearch ?? "",
    "type": 1
});

function submit(e)
{
    searchForm.get(route("search"));
}

</script>

<template>
    <form @submit.prevent="submit" class="max-w-xl flex items-center p-1 border rounded-xl border-gray-400 m-4 bg-slate-100 hover:bg-white transition-colors shadow">
        <input v-model="searchForm.search" type="search" class="uppercase bg-transparent border-none w-full outline-none focus:outline-none focus:ring-0 focus:border-none"/>
        <select v-model="searchForm.type" class="bg-transparent border-none text-end w-[10rem] focus:outline-none focus:ring-0 focus:border-none">
            <option value="1">Placa</option>
            <option value="2">Otros</option>
        </select>
        <button type="submit" class="border-none bg-[#3b4280] p-2 rounded-full text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10a7 7 0 1 0 14 0a7 7 0 1 0-14 0m18 11l-6-6"/>
            </svg>
        </button>
    </form>
    <InputError :message="searchForm.errors.search" />
</template>
