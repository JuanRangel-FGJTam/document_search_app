<script setup>
    const props = defineProps( {
        plateNumber: String,
        stateName: {
            type: String,
            default: "Tamaulipas"
        },
        serialNumber: {
            type: String,
            default: undefined
        }
    });
</script>
<template>
    <svg width="200" height="90" viewBox="0 0 200 90" xmlns="http://www.w3.org/2000/svg">
        <rect x="5" y="5" width="190" height="80" rx="5" ry="5" fill="#dbddd1" stroke="#666" stroke-width="2"/>
        <rect x="50" y="10" width="5" height="2.5" rx="2" ry="2" fill="#333" stroke="white" stroke-width="0"/>
        <rect x="145" y="10" width="5" height="2.5" rx="2" ry="2" fill="#333" stroke="white" stroke-width="0"/>
        <text x="100" y="38" font-family="Courier New" font-size="20" font-weight="bold" fill="#222" text-anchor="middle">{{ plateNumber.toUpperCase() }}</text>
        <text v-if="serialNumber" x="100" y="60" font-family="Courier New" font-size="12" font-weight="bold" fill="#222" text-anchor="middle">SN: {{ serialNumber.toUpperCase() }}</text>
        <rect x="5" y="65" width="190" height="19" rx="2" ry="5" fill="#761f26"/>
        <text x="100" y="80" font-family="Arial" font-size="10" font-weight="bold" fill="#fff" text-anchor="middle">{{ stateName }}</text>
    </svg>
</template>