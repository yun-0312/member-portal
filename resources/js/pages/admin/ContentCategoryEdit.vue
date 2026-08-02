<template>
    <CategoryMasterEdit
        title="コンテンツカテゴリー編集"
        :fetch-url="`/admin/content-categories/${route.params.id}`"
        :submit-url="`/admin/content-categories/${route.params.id}`"
        index-url="/admin/content-categories"
        :fields="['slug', 'section', 'sort_order', 'display_type']"
    />
</template>

<script setup>
import { useRoute } from 'vue-router'
import CategoryMasterEdit from '@/components/CategoryMasterEdit.vue'

const route = useRoute()
</script>