<template>
    <CategoryMasterCreate
        title="コールセンターカテゴリー新規登録"
        submit-url="/admin/faq-categories"
        index-url="/admin/faq-categories"
        :fields="['sort_order']"
    />
</template>

<script setup>
import CategoryMasterCreate from '@/components/CategoryMasterCreate.vue'
</script>