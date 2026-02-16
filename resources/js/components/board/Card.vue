<template>
    <div class="card">
        <div class="card-title">{{ task.title }}</div>
        <div class="card-meta">
            <span v-if="task.sprint_id" class="badge">Sprint {{ task.sprint_id }}</span>
        </div>
    </div>
</template>

<script setup>
defineProps(['task']);
</script>

<style scoped>
.card {
    background: white;
    padding: 0.75rem;
    border-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    cursor: grab;
}
.card:active {
    cursor: grabbing;
}
.card-title {
    font-weight: 500;
    margin-bottom: 0.5rem;
}
.badge {
    font-size: 0.75rem;
    background: #e0e7ff;
    color: #3730a3;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
}
</style>
