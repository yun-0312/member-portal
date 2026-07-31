<template>
    <CategoryMasterCreate
        title="会議室新規登録"
        submit-url="/admin/rooms"
        index-url="/admin/rooms"
        :fields="['slug']"
    />
</template>

<script setup>
import CategoryMasterCreate from '@/components/CategoryMasterCreate.vue'
</script>