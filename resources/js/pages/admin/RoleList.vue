<template>
    <CategoryMasterList
        title="ロール管理"
        api-endpoint="/admin/roles"
        :columns="['']"
    />
</template>

<script setup>
import CategoryMasterList from '@/components/CategoryMasterList.vue'
</script>