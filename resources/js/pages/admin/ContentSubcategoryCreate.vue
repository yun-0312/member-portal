<template>
    <CategoryMasterCreate
        v-if="isLoaded"
        title="サブカテゴリー新規作成"
        submit-url="/admin/content-subcategories"
        index-url="/admin/content-categories"
        :fields="['slug', 'category_id', 'parent_id', 'display_type', 'sort_order']"
        :display-type-options="subcategoryDisplayOptions"
        :category-options="categoryList"
        :sub-category-options="subCategoryList"
    />
    <div v-else class="flex justify-center items-center py-20 text-slate-400">
        <div class="w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin mr-2"></div>
        選択肢データを読み込み中…
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api.js'
import CategoryMasterCreate from '@/components/CategoryMasterCreate.vue'

const isLoaded = ref(false)

const subcategoryDisplayOptions = [
    { value: 'list', label: 'list' },
    { value: 'children', label: 'children' },
    { value: 'year_archive', label: 'year_archive' }
]

const categoryList = ref([])
const subCategoryList = ref([])

onMounted(async () => {
    try {
        const [catRes, subRes] = await Promise.all([
            api.get('/admin/content-categories?per_page=1000'),
            api.get('/admin/content-subcategories?per_page=1000')
        ])

        categoryList.value = catRes.data.data || []
        subCategoryList.value = subRes.data.data || []
    } catch (err) {
        console.error('選択肢の取得失敗:', err)
    } finally {
        isLoaded.value = true
    }
})
</script>