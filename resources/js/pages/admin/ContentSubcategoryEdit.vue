<template>
    <!-- 全データの準備が整ってからレンダリング -->
    <CategoryMasterEdit
        v-if="isLoaded"
        title="サブカテゴリー編集"
        :fetch-url="`/admin/content-subcategories/${$route.params.id}`"
        :submit-url="`/admin/content-subcategories/${$route.params.id}`"
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
import { useRoute } from 'vue-router'
import api from '@/api.js'
import CategoryMasterEdit from '@/components/CategoryMasterEdit.vue'

const route = useRoute()
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
        // per_page=100 を指定してページネーションで漏れるのを防ぐ
        const [catRes, subRes] = await Promise.all([
            api.get('/admin/content-categories?per_page=1000'),
            api.get('/admin/content-subcategories?per_page=1000')
        ])

        // JSON構造に合わせた抽出 (res.data.data)
        categoryList.value = catRes.data.data || []

        // 自分自身を親サブカテゴリー（parent_id）の選択肢から除外する処理（循環参照防止）
        const currentId = Number(route.params.id)
        const allSubcategories = subRes.data.data || []
        subCategoryList.value = allSubcategories.filter(item => Number(item.id) !== currentId)

    } catch (err) {
        console.error('カテゴリー/サブカテゴリー一覧の取得失敗:', err)
    } finally {
        isLoaded.value = true
    }
})
</script>