<template>
    <CategoryMasterEdit
        title="ロール編集"
        :fetch-url="`/admin/roles/${route.params.id}`"
        :submit-url="`/admin/roles/${route.params.id}`"
        index-url="/admin/roles"
        :fields="[]"
    />
</template>

<script setup>
import { useRoute } from 'vue-router'
import CategoryMasterEdit from '@/components/CategoryMasterEdit.vue'

const route = useRoute()
</script>