<template>
    <div class="flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div>
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-6 py-4 bg-white overflow-hidden sm:rounded-lg">
            <slot />
        </div>
    </div>
</template>
