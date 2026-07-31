<template>
    <CategoryMasterList
        title="お知らせカテゴリー管理"
        api-endpoint="/admin/notice-categories"
        :columns="['slug', 'sort_order']"
    />
</template>

<script setup>
import CategoryMasterList from '@/components/CategoryMasterList.vue'
</script>