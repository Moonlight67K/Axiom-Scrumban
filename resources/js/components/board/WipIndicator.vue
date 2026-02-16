<template>
    <div class="wip-indicator" :class="{ 'over-limit': isOverLimit }">
        {{ current }} / {{ limit }}
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    current: Number,
    limit: Number
});

const isOverLimit = computed(() => props.limit > 0 && props.current > props.limit);
</script>

<style scoped>
.wip-indicator {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    background: #e5e7eb;
    color: #4b5563;
}
.over-limit {
    background: #fee2e2;
    color: #991b1b;
    font-weight: bold;
}
</style>
