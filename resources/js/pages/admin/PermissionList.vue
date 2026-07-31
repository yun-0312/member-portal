<template>
    <CategoryMasterList
        title="権限（パーミッション）管理"
        api-endpoint="/admin/permissions"
        :columns="['roles']"
    />
</template>

<script setup>
import CategoryMasterList from '@/components/CategoryMasterList.vue'
</script>