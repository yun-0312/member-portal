<template>
    <CategoryMasterEdit
        title="スケジュールカテゴリー編集"
        :fetch-url="`/admin/schedule-categories/${route.params.id}`"
        :submit-url="`/admin/schedule-categories/${route.params.id}`"
        index-url="/admin/schedule-categories"
        :fields="[]"
    />
</template>

<script setup>
import { useRoute } from 'vue-router'
import CategoryMasterEdit from '@/components/CategoryMasterEdit.vue'

const route = useRoute()
</script>