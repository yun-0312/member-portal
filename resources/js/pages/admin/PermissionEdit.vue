<template>
    <CategoryMasterEdit
        title="お知らせカテゴリー編集"
        :fetch-url="`/admin/permissions/${route.params.id}`"
        :submit-url="`/admin/permissions/${route.params.id}`"
        index-url="/admin/permissions"
        :fields="[]"
    />
</template>

<script setup>
import { useRoute } from 'vue-router'
import CategoryMasterEdit from '@/components/CategoryMasterEdit.vue'

const route = useRoute()
</script>