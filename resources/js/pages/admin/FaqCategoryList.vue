<template>
    <CategoryMasterList
        title="FAQカテゴリー管理"
        api-endpoint="/admin/faq-categories"
        :columns="['sort_order']"
    />
</template>

<script setup>
import CategoryMasterList from '@/components/CategoryMasterList.vue'
</script>