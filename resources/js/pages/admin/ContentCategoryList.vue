<template>
    <CategoryMasterList
        title="コンテンツカテゴリー管理"
        api-endpoint="/admin/content-categories"
        :columns="['slug', 'section', 'sort_order', 'roles']"
    />
</template>

<script setup>
import CategoryMasterList from '@/components/CategoryMasterList.vue'
</script>