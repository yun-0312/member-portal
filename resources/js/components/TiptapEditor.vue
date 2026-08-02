<template>
    <div class="border border-slate-300 rounded-2xl overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all bg-white relative">

        <!-- ★ BubbleMenu (テキスト選択時の浮遊メニュー) -->
        <bubble-menu
            v-if="editor"
            :editor="editor"
            :tippy-options="{ duration: 100 }"
            class="flex items-center gap-1 p-1 bg-slate-800 text-white rounded-lg shadow-lg text-xs"
        >
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'bg-slate-600': editor.isActive('bold') }"
                class="px-2 py-1 rounded hover:bg-slate-700 transition-colors font-bold"
            >
                B
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'bg-slate-600': editor.isActive('italic') }"
                class="px-2 py-1 rounded hover:bg-slate-700 transition-colors italic"
            >
                I
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'bg-slate-600': editor.isActive('heading', { level: 2 }) }"
                class="px-2 py-1 rounded hover:bg-slate-700 transition-colors font-bold"
            >
                H2
            </button>
        </bubble-menu>

        <!-- 固定ツールバー -->
        <div v-if="editor" class="flex flex-wrap items-center gap-1 p-2 bg-slate-50 border-b border-slate-200 text-xs text-slate-700">
            <!-- 太字 -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'bg-slate-200 text-slate-900 font-bold': editor.isActive('bold') }"
                class="px-2.5 py-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                title="太字"
            >
                <b>B</b>
            </button>

            <!-- 斜体 -->
            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'bg-slate-200 text-slate-900': editor.isActive('italic') }"
                class="px-2.5 py-1.5 rounded-lg hover:bg-slate-200 transition-colors italic"
                title="斜体"
            >
                I
            </button>

            <!-- 見出し (H2, H3) -->
            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'bg-slate-200 text-slate-900 font-bold': editor.isActive('heading', { level: 2 }) }"
                class="px-2.5 py-1.5 rounded-lg hover:bg-slate-200 transition-colors"
            >
                H2
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                :class="{ 'bg-slate-200 text-slate-900 font-bold': editor.isActive('heading', { level: 3 }) }"
                class="px-2.5 py-1.5 rounded-lg hover:bg-slate-200 transition-colors"
            >
                H3
            </button>

            <!-- 箇条書きリスト -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'bg-slate-200 text-slate-900': editor.isActive('bulletList') }"
                class="px-2.5 py-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                title="箇条書き"
            >
                • リスト
            </button>

            <div class="h-4 w-px bg-slate-300 mx-1"></div>

            <!-- メディアライブラリ呼び出しボタン -->
            <button
                type="button"
                @click="$emit('open-media')"
                class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold transition-colors flex items-center gap-1 cursor-pointer"
            >
                🖼️ 画像を挿入
            </button>
        </div>

        <!-- エディタ本体 -->
        <editor-content :editor="editor" class="prose prose-sm max-w-none p-4 min-h-[200px] focus:outline-none" />
    </div>
</template>

<script setup>
import { watch, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { BubbleMenu } from '@tiptap/vue-3/menus'
import BubbleMenuExtension from '@tiptap/extension-bubble-menu'

import Placeholder from '@tiptap/extension-placeholder'
import ResizeImage from 'tiptap-extension-resize-image'

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue', 'open-media'])

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        ResizeImage,
        BubbleMenuExtension,
        Placeholder.configure({
            placeholder: 'ここに本文を入力してください...',
        }),
    ],
    onUpdate: () => {
        emit('update:modelValue', editor.value.getHTML())
    },
})

// 親の v-model の変更を監視して同期
watch(() => props.modelValue, (value) => {
    const isSame = editor.value.getHTML() === value
    if (!isSame) {
        editor.value.commands.setContent(value, false)
    }
})

// 画像挿入用メソッド
const insertImage = (url) => {
    if (editor.value && url) {
        editor.value.chain().focus().setImage({ src: url }).run()
    }
}

defineExpose({
    insertImage
})

onBeforeUnmount(() => {
    editor.value?.destroy()
})
</script>

<style>
/* ★ Placeholder用CSS (Tailwind環境で表示を薄く見せるためのスタイル) */
.tiptap p.is-editor-empty:first-child::before {
    color: #9ca3af;
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}
</style>