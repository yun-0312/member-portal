<template>
    <CategoryMasterEdit
        title="お知らせカテゴリー編集"
        :fetch-url="`/admin/notice-categories/${route.params.id}`"
        :submit-url="`/admin/notice-categories/${route.params.id}`"
        index-url="/admin/notice-categories"
        :fields="['slug', 'sort_order']"
    />
</template>

<script setup>
import { useRoute } from 'vue-router'
import CategoryMasterEdit from '@/components/CategoryMasterEdit.vue'

const route = useRoute()
</script>