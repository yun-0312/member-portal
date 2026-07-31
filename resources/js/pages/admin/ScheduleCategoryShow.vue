<template>
    <CategoryMasterShow
        title="スケジュールカテゴリー詳細"
        :api-endpoint="`/admin/schedule-categories/${route.params.id}`"
        :columns="['slug', 'sort_order']"
    />
</template>

<script setup>
import { useRoute } from 'vue-router'
import CategoryMasterShow from '@/components/CategoryMasterShow.vue'

const route = useRoute()
</script>