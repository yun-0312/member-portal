<template>
    <div class="z-50 min-w-[180px] bg-white rounded-xl border border-slate-200 shadow-xl p-1 overflow-hidden text-xs animate-in fade-in zoom-in-95 duration-100">
        <div v-if="items.length" class="flex flex-col gap-0.5">
        <button
            v-for="(item, index) in items"
            :key="index"
            type="button"
            @click="selectItem(index)"
            :class="[
            'flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left w-full transition-colors cursor-pointer',
            index === selectedIndex ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-slate-100'
            ]"
        >
            <span class="text-sm">{{ item.icon }}</span>
            <div>
            <div class="leading-tight">{{ item.title }}</div>
            <div v-if="item.description" class="text-[10px] text-slate-400 font-normal leading-tight">{{ item.description }}</div>
            </div>
        </button>
        </div>
        <div v-else class="px-3 py-2 text-slate-400 italic">
        該当するコマンドがありません
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
    command: {
        type: Function,
        required: true,
    },
})

const selectedIndex = ref(0)

watch(() => props.items, () => {
    selectedIndex.value = 0
})

const selectItem = (index) => {
    const item = props.items[index]
    if (item) {
        props.command(item)
    }
}

const onKeyDown = ({ event }) => {
    if (event.key === 'ArrowUp') {
        selectedIndex.value = ((selectedIndex.value - 1) + props.items.length) % props.items.length
        return true
    }
    if (event.key === 'ArrowDown') {
        selectedIndex.value = (selectedIndex.value + 1) % props.items.length
        return true
    }
    if (event.key === 'Enter') {
        selectItem(selectedIndex.value)
        return true
    }
    return false
}

defineExpose({
    onKeyDown,
})
</script>