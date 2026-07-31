<template>
    <CategoryMasterList
        title="会議室管理"
        api-endpoint="/admin/rooms"
        :columns="['sort_order']"
    />
</template>

<script setup>
import CategoryMasterList from '@/components/CategoryMasterList.vue'
</script>