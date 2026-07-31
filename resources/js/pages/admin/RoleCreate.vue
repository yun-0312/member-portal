<template>
    <CategoryMasterCreate
        title="ロール新規登録"
        submit-url="/admin/roles"
        index-url="/admin/roles"
        :fields="[]"
    />
</template>

<script setup>
import CategoryMasterCreate from '@/components/CategoryMasterCreate.vue'
</script>