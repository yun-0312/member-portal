<template>
    <CategoryMasterShow
        title="スケジュールカテゴリー詳細"
        :api-endpoint="`/admin/rooms/${route.params.id}`"
        :columns="['sort_order']"
    />
</template>

<script setup>
import { useRoute } from 'vue-router'
import CategoryMasterShow from '@/components/CategoryMasterShow.vue'

const route = useRoute()
</script>