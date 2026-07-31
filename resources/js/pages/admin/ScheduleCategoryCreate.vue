<template>
    <CategoryMasterCreate
        title="スケジュールカテゴリー新規登録"
        submit-url="/admin/schedule-categories"
        index-url="/admin/schedule-categories"
        :fields="['slug']"
    />
</template>

<script setup>
import CategoryMasterCreate from '@/components/CategoryMasterCreate.vue'
</script>