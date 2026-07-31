<template>
    <CategoryMasterShow
        title="お知らせカテゴリー詳細"
        :api-endpoint="`/admin/notice-categories/${route.params.id}`"
        :columns="['slug', 'sort_order']"
    />
</template>

<script setup>
import { useRoute } from 'vue-router'
import CategoryMasterShow from '@/components/CategoryMasterShow.vue'

const route = useRoute()
</script>