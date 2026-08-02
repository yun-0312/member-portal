<template>
    <CategoryMasterEdit
        title="会議室編集"
        :fetch-url="`/admin/rooms/${route.params.id}`"
        :submit-url="`/admin/rooms/${route.params.id}`"
        index-url="/admin/rooms"
        :fields="[]"
    />
</template>

<script setup>
import { useRoute } from 'vue-router'
import CategoryMasterEdit from '@/components/CategoryMasterEdit.vue'

const route = useRoute()
</script>