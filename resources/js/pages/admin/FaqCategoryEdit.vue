<template>
    <CategoryMasterEdit
        title="FAQカテゴリー編集"
        :fetch-url="`/admin/faq-categories/${route.params.id}`"
        :submit-url="`/admin/faq-categories/${route.params.id}`"
        index-url="/admin/faq-categories"
        :fields="[]"
    />
</template>

<script setup>
import { useRoute } from 'vue-router'
import CategoryMasterEdit from '@/components/CategoryMasterEdit.vue'

const route = useRoute()
</script>