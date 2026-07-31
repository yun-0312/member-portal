<template>
    <CategoryMasterList
        title="スケジュールカテゴリー管理"
        api-endpoint="/admin/schedule-categories"
        :columns="['slug']"
    />
</template>

<script setup>
import CategoryMasterList from '@/components/CategoryMasterList.vue'
</script>