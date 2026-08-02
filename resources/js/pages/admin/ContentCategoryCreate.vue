<template>
    <CategoryMasterCreate
        title="カテゴリー新規作成"
        submit-url="/admin/content-categories"
        index-url="/admin/content-categories"
        :fields="['slug', 'section', 'display_type', 'sort_order']"
    />
</template>

<script setup>
import CategoryMasterCreate from '@/components/CategoryMasterCreate.vue'
</script>