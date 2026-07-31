<template>
    <CategoryMasterCreate
        title="パーミッション新規登録"
        submit-url="/admin/permissions"
        index-url="/admin/permissions"
        :fields="[]"
    />
</template>

<script setup>
import CategoryMasterCreate from '@/components/CategoryMasterCreate.vue'
</script>