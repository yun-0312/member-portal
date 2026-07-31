<template>
    <CategoryMasterCreate
        title="お知らせカテゴリー新規登録"
        submit-url="/admin/notice-categories"
        index-url="/admin/notice-categories"
        :fields="['slug', 'sort_order']"
    />
</template>

<script setup>
import CategoryMasterCreate from '@/components/CategoryMasterCreate.vue'
</script>