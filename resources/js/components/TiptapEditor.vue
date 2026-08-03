<template>
    <div class="border border-slate-200 rounded-2xl overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all bg-white relative shadow-xs">

        <!--  テキスト選択時の BubbleMenu -->
        <bubble-menu
            v-if="editor"
            :editor="editor"
            :tippy-options="{ duration: 100 }"
            :should-show="shouldShowTextMenu"
            class="flex items-center gap-1 p-1 bg-slate-900 text-white rounded-xl shadow-xl text-xs backdrop-blur-md"
        >
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'bg-slate-700 text-blue-400': editor.isActive('bold') }"
                class="px-2 py-1 rounded-lg hover:bg-slate-800 transition-colors font-bold"
            >
                B
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'bg-slate-700 text-blue-400': editor.isActive('italic') }"
                class="px-2 py-1 rounded-lg hover:bg-slate-800 transition-colors italic"
            >
                I
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'bg-slate-700 text-blue-400': editor.isActive('heading', { level: 2 }) }"
                class="px-2 py-1 rounded-lg hover:bg-slate-800 transition-colors font-bold"
            >
                H2
            </button>
            <button
                type="button"
                @click="openLinkInput"
                :class="{ 'bg-slate-700 text-blue-400': editor.isActive('link') }"
                class="px-2 py-1 rounded-lg hover:bg-slate-800 transition-colors"
                title="リンク設定"
            >
                🔗
            </button>
        </bubble-menu>

        <!--  画像選択時の専用 BubbleMenu -->
        <bubble-menu
            v-if="editor"
            :editor="editor"
            :tippy-options="{ duration: 100 }"
            :should-show="shouldShowImageMenu"
            class="flex items-center gap-1 p-1.5 bg-slate-900 text-white rounded-xl shadow-xl text-xs backdrop-blur-md"
        >
            <button
                type="button"
                @click="setImageAlignment('left')"
                :class="{ 'bg-slate-700 text-blue-400': editor.isActive({ textAlign: 'left' }) }"
                class="px-2.5 py-1 rounded-lg hover:bg-slate-800 transition-colors flex items-center gap-1 cursor-pointer"
                title="左寄せ"
            >
                ⬅️ 左寄せ
            </button>
            <button
                type="button"
                @click="setImageAlignment('center')"
                :class="{ 'bg-slate-700 text-blue-400': editor.isActive({ textAlign: 'center' }) }"
                class="px-2.5 py-1 rounded-lg hover:bg-slate-800 transition-colors flex items-center gap-1 cursor-pointer"
                title="中央寄せ"
            >
                ↔️ 中央
            </button>
            <button
                type="button"
                @click="setImageAlignment('right')"
                :class="{ 'bg-slate-700 text-blue-400': editor.isActive({ textAlign: 'right' }) }"
                class="px-2.5 py-1 rounded-lg hover:bg-slate-800 transition-colors flex items-center gap-1 cursor-pointer"
                title="右寄せ"
            >
                ➡️ 右寄せ
            </button>

            <div class="h-4 w-px bg-slate-700 mx-1"></div>

            <button
                type="button"
                @click="deleteSelectedImage"
                class="px-2.5 py-1 rounded-lg bg-red-600/80 hover:bg-red-600 text-white transition-colors flex items-center gap-1 cursor-pointer"
                title="画像を削除"
            >
                🗑️ 削除
            </button>
        </bubble-menu>

        <!--  固定ツールバー -->
        <div v-if="editor" class="flex flex-wrap items-center gap-1 p-2 bg-slate-50/80 border-b border-slate-200 text-xs text-slate-700 select-none">

            <!-- 見出し切り替えドロップダウン -->
            <select
                :value="currentHeadingLevel"
                @change="handleHeadingChange"
                class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer"
            >
                <option value="paragraph">標準テキスト</option>
                <option value="1">見出し 1 (H1)</option>
                <option value="2">見出し 2 (H2)</option>
                <option value="3">見出し 3 (H3)</option>
                <option value="4">見出し 4 (H4)</option>
            </select>

            <div class="h-4 w-px bg-slate-300 mx-0.5"></div>

            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'bg-slate-200 text-blue-600 font-bold': editor.isActive('bold') }"
                class="px-2 py-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                title="太字"
            >
                <b>B</b>
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'bg-slate-200 text-blue-600': editor.isActive('italic') }"
                class="px-2 py-1.5 rounded-lg hover:bg-slate-200 transition-colors italic"
                title="斜体"
            >
                I
            </button>

            <!-- 〰 カスタム下線ツール -->
            <div class="relative inline-block text-left">
                <!-- 1. メインボタン (U) -->
                <button
                    type="button"
                    @click="toggleUnderlineMenu"
                    :class="[
                        'px-2 py-1.5 rounded-lg hover:bg-slate-200 transition-colors flex items-center gap-1 cursor-pointer font-bold text-xs',
                        showUnderlineMenu ? 'bg-slate-200 text-blue-600' : 'text-slate-700'
                    ]"
                    title="カスタム下線設定"
                >
                    <span class="underline underline-offset-2">U</span>
                    <span class="text-[9px] text-slate-400">▼</span>
                </button>

                <!-- 背景クリックで閉じる透明オーバーレイ -->
                <div
                    v-if="showUnderlineMenu"
                    @click="showUnderlineMenu = false"
                    class="fixed inset-0 z-10 cursor-default"
                ></div>

                <!-- 2. ポップアップ設定メニュー -->
                <div
                    v-if="showUnderlineMenu"
                    class="absolute left-0 mt-1 z-20 min-w-[220px] p-2.5 bg-white rounded-xl shadow-xl border border-slate-200 text-xs animate-in fade-in zoom-in-95 duration-100 flex flex-col gap-2.5"
                >
                    <!-- A. 線の種類 (スタイル) -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] text-slate-500 font-medium">種類</span>
                        <select
                            v-model="underlineStyle"
                            @change="applyUnderline()"
                            class="px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-700 cursor-pointer focus:outline-none"
                        >
                            <option value="solid">直線 ――</option>
                            <option value="wavy">波線 〰〰</option>
                            <option value="dotted">点線 ┈┈</option>
                            <option value="dashed">破線 - - -</option>
                            <option value="double">二重線 ══</option>
                        </select>
                    </div>

                    <!-- B. 線の太さ -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] text-slate-500 font-medium">太さ</span>
                        <select
                            v-model="underlineThickness"
                            @change="applyUnderline()"
                            class="px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-700 cursor-pointer focus:outline-none"
                        >
                            <option value="1px">細 (1px)</option>
                            <option value="2px">中 (2px)</option>
                            <option value="3px">太 (3px)</option>
                            <option value="5px">極太 (5px)</option>
                        </select>
                    </div>

                    <!-- C. カラーパレット -->
                    <div>
                        <span class="text-[11px] text-slate-500 font-medium block mb-1.5">線の色</span>
                        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-lg justify-between">
                            <button
                                v-for="color in underlinePresetColors"
                                :key="color"
                                type="button"
                                @click="applyUnderline(color)"
                                class="w-5 h-5 rounded-full border border-black/10 hover:scale-110 transition-transform cursor-pointer shadow-xs flex items-center justify-center"
                                :style="{ backgroundColor: color }"
                                :title="`線の色: ${color}`"
                            >
                                <span v-if="underlineColor === color" class="w-1.5 h-1.5 rounded-full bg-white/90"></span>
                            </button>

                            <!-- 自由色ピッカー -->
                            <label class="relative w-5 h-5 rounded-full border border-slate-300 overflow-hidden cursor-pointer hover:scale-110 transition-transform flex items-center justify-center bg-white" title="自由な色を選択">
                                <span class="text-[9px]">🎨</span>
                                <input
                                    type="color"
                                    v-model="underlineColor"
                                    @change="applyUnderline()"
                                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                />
                            </label>
                        </div>
                    </div>

                    <hr class="border-slate-100 my-0.5" />

                    <!-- D. アクションボタン (適用・解除) -->
                    <div class="flex items-center justify-between gap-2 pt-0.5">
                        <button
                            type="button"
                            @click="removeUnderline"
                            class="px-2 py-1 text-[11px] text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded transition-colors"
                        >
                            下線を解除
                        </button>
                        <button
                            type="button"
                            @click="applyUnderline(); showUnderlineMenu = false"
                            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md shadow-xs transition-colors"
                        >
                            適用
                        </button>
                    </div>
                </div>
            </div>

            <div class="h-4 w-px bg-slate-300 mx-0.5"></div>

            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'bg-slate-200 text-blue-600': editor.isActive('bulletList') }"
                class="px-2.5 py-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                title="箇条書き"
            >
                •☰
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ 'bg-slate-200 text-blue-600': editor.isActive('orderedList') }"
                class="px-2.5 py-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                title="番号付きリスト"
            >
                1.☰
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{ 'bg-slate-200 text-blue-600': editor.isActive('blockquote') }"
                class="px-2.5 py-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                title="引用"
            >
                ❞❞
            </button>

            <!-- 左揃え -->
            <button
                type="button"
                @click="editor.chain().focus().setTextAlign('left').run()"
                :class="{ 'bg-slate-200 text-blue-600': editor.isActive({ textAlign: 'left' }) }"
                class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                title="左揃え"
            >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M3 4h18v2H3V4zm0 5h12v2H3V9zm0 5h18v2H3v-2zm0 5h12v2H3v-2z"/>
                </svg>
            </button>

            <!-- 中央揃え -->
            <button
                type="button"
                @click="editor.chain().focus().setTextAlign('center').run()"
                :class="{ 'bg-slate-200 text-blue-600': editor.isActive({ textAlign: 'center' }) }"
                class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                title="中央揃え"
            >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M3 4h18v2H3V4zm3 5h12v2H6V9zm-3 5h18v2H3v-2zm3 5h12v2H6v-2z"/>
                </svg>
            </button>

            <!-- 右揃え -->
            <button
                type="button"
                @click="editor.chain().focus().setTextAlign('right').run()"
                :class="{ 'bg-slate-200 text-blue-600': editor.isActive({ textAlign: 'right' }) }"
                class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                title="右揃え"
            >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M3 4h18v2H3V4zm6 5h12v2H9V9zm-6 5h18v2H3v-2zm6 5h12v2H9v-2z"/>
                </svg>
            </button>

            <div class="h-4 w-px bg-slate-300 mx-0.5"></div>

            <!-- 🔗 リンクボタン -->
            <button
                type="button"
                @click="openLinkInput"
                :class="{ 'bg-slate-200 text-blue-600': editor.isActive('link') }"
                class="px-2.5 py-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                title="リンク"
            >
                🔗 リンク
            </button>

            <div class="h-4 w-px bg-slate-300 mx-0.5"></div>

            <!--  文字色選択パレット -->
            <div class="flex items-center gap-1 px-1 py-0.5 bg-slate-200/60 rounded-xl">
                <!-- パレット（プリセットカラー） -->
                <button
                    v-for="color in presetColors"
                    :key="color"
                    type="button"
                    @click="editor.chain().focus().setColor(color).run()"
                    class="w-5 h-5 rounded-full border border-black/10 hover:scale-110 transition-transform cursor-pointer shadow-xs"
                    :style="{ backgroundColor: color }"
                    :title="`文字色: ${color}`"
                ></button>

                <!-- カスタムカラーピッカー -->
                <label class="relative w-5 h-5 rounded-full border border-slate-300 overflow-hidden cursor-pointer hover:scale-110 transition-transform flex items-center justify-center bg-white" title="自由な色を選択">
                    <span class="text-[9px] font-bold text-slate-500">🎨</span>
                    <input
                        type="color"
                        @input="editor.chain().focus().setColor($event.target.value).run()"
                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                    />
                </label>

                <!-- 色解除（デフォルトに戻す） -->
                <button
                    type="button"
                    @click="editor.chain().focus().unsetColor().run()"
                    class="px-1.5 py-0.5 text-[10px] text-slate-500 hover:text-slate-800 transition-colors font-bold"
                    title="色を解除"
                >
                    ✕
                </button>
            </div>

            <button
                type="button"
                @click="$emit('open-media')"
                class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold transition-colors flex items-center gap-1 cursor-pointer ml-auto"
            >
                🖼️ 画像を挿入
            </button>
            <!-- モード切り替えタブ -->
            <div class="flex items-center gap-1 bg-slate-100 p-0.5 rounded-lg text-xs font-medium border border-slate-200 ml-auto">
                <button
                    type="button"
                    @click="isHtmlMode && toggleMode()"
                    :class="[
                        'px-2 py-1 rounded-md transition-colors cursor-pointer',
                        !isHtmlMode ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900'
                    ]"
                >
                    ビジュアル
                </button>
                <button
                    type="button"
                    @click="!isHtmlMode && toggleMode()"
                    :class="[
                        'px-2 py-1 rounded-md transition-colors cursor-pointer',
                        isHtmlMode ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900'
                    ]"
                >
                    テキスト (HTML)
                </button>
            </div>
        </div>

        <!-- 🔗 インライン リンク入力ポップアップ -->
        <div
            v-if="showLinkInput"
            class="p-2 bg-slate-800 text-white border-b border-slate-700 flex items-center gap-2 text-xs animate-in fade-in duration-150"
        >
            <span class="text-slate-400 pl-2">🔗 URL:</span>
            <input
                ref="linkInputRef"
                v-model="linkUrl"
                type="text"
                placeholder="https://example.com"
                @keydown.enter.prevent="applyLink"
                @keydown.esc="closeLinkInput"
                class="flex-1 bg-slate-900 text-white px-3 py-1.5 rounded-lg border border-slate-700 focus:outline-none focus:border-blue-500"
            />
            <button
                type="button"
                @click="applyLink"
                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 font-bold rounded-lg transition-colors cursor-pointer"
            >
                適用
            </button>
            <button
                type="button"
                @click="removeLink"
                v-if="editor?.isActive('link')"
                class="px-3 py-1.5 bg-red-600/80 hover:bg-red-600 font-bold rounded-lg transition-colors cursor-pointer"
            >
                解除
            </button>
            <button
                type="button"
                @click="closeLinkInput"
                class="px-2 py-1.5 text-slate-400 hover:text-white transition-colors cursor-pointer"
            >
                ✕
            </button>
        </div>

        <!-- エディタ本体 -->
        <div class="relative">
            <!-- ビジュアルモード -->
            <editor-content
                v-if="!isHtmlMode"
                :editor="editor"
                class="prose prose-sm max-w-none p-4 min-h-[250px] focus:outline-none"
            />

            <!-- テキスト (HTML) モード -->
            <textarea
                v-else
                v-model="rawHtml"
                @input="emit('update:modelValue', $event.target.value)"
                class="w-full p-4 min-h-[300px] font-mono text-xs bg-slate-50 text-slate-800 leading-relaxed border-0 focus:ring-0 focus:outline-none resize-y"
                placeholder="HTMLタグを直接入力・編集できます..."
                spellcheck="false"
            ></textarea>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { BubbleMenu } from '@tiptap/vue-3/menus'
import BubbleMenuExtension from '@tiptap/extension-bubble-menu'
import Placeholder from '@tiptap/extension-placeholder'
import ResizeImage from 'tiptap-extension-resize-image'
import Link from '@tiptap/extension-link'
import TextAlign from '@tiptap/extension-text-align'
import Color from '@tiptap/extension-color'
import { TextStyle } from '@tiptap/extension-text-style'
import Suggestion from '@tiptap/suggestion'
import { SlashCommands, getSuggestionItems, renderItems } from './slashCommands'

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue', 'open-media'])

const openMediaModal = () => {
    emit('open-media')
}

const showLinkInput = ref(false)
const linkUrl = ref('')
const linkInputRef = ref(null)
const showColorPicker = ref(false)
const rawHtml = ref('')
const isHtmlMode = ref(false)
const showUnderlineMenu = ref(false)
const underlineStyle = ref('solid')
const underlineColor = ref('#000000')
const underlineThickness = ref('2px')

const editor = useEditor({
    content: props.modelValue,
    parseOptions: {
        preserveWhitespace: false,
    },
    extensions: [
        StarterKit.configure({
            link: false,
        }),
        TextStyle.configure({
            types: ['textStyle'],
            }).extend({
            addAttributes() {
                return {
                style: {
                    default: null,
                    parseHTML: element => element.getAttribute('style'),
                    renderHTML: attributes => {
                    if (!attributes.style) {
                        return {}
                    }
                    return {
                        style: attributes.style,
                    }
                    },
                },
                }
            },
        }),
        Color,
        ResizeImage,
        BubbleMenuExtension,
        Placeholder.configure({
            placeholder: 'ここに本文を入力してください...',
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-blue-600 underline hover:text-blue-800 cursor-pointer',
            },
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph', 'image'],
        }),
        SlashCommands.configure({
            suggestion: {
                items: getSuggestionItems(openMediaModal),
                render: renderItems,
            },
        }),
    ],
    onUpdate: () => {
        let html = editor.value.getHTML()

        // 1. 中身が空（または空白・&nbsp;のみ）の <p></p> を削除
        html = html.replace(/<p>\s*(?:&nbsp;)*\s*<\/p>/gi, '')

        // 2. 中身が空の 見出しタグ <h1>〜<h6> を削除
        html = html.replace(/<h[1-6]>\s*(?:&nbsp;)*\s*<\/h[1-6]>/gi, '')

        // 3. リスト内などのネストされた不要な空タグも掃除
        html = html.replace(/<li>\s*<p>\s*<\/p>\s*<\/li>/gi, '')

        emit('update:modelValue', html)
    },
})

// HTMLタグごとに改行を入れて見やすく整形する関数
const formatHtml = (html) => {
    if (!html) return ''

    // 1. タグの前後に改行を入れる
    let formatted = html
        .replace(/></g, '>\n<')
        .replace(/(<br\s*\/?>)/gi, '$1\n') // <br> の後ろでも改行

    // 2. 連続した無駄な空行を1つにまとめる
    formatted = formatted.replace(/\n\s*\n/g, '\n')

    return formatted.trim()
}

const toggleMode = () => {
    if (!isHtmlMode.value) {
        // ビジュアル ➔ テキストへ切り替える時：改行を入れる
        const currentHtml = editor.value ? editor.value.getHTML() : props.modelValue
        rawHtml.value = formatHtml(currentHtml)
        isHtmlMode.value = true
    } else {
        // テキスト ➔ ビジュアルへ戻る時：Tiptapへ変更内容を反映
        if (editor.value) {
            editor.value.commands.setContent(rawHtml.value, false)
        }
        // 親の v-model にも反映
        emit('update:modelValue', rawHtml.value)
        isHtmlMode.value = false
    }
}

// 下線用の標準カラーパレット
const underlinePresetColors = [
    '#000000', // 黒
    '#dc2626', // 赤
    '#2563eb', // 青
    '#16a34a', // 緑
    '#d97706', // オレンジ/黄
    '#7c3aed', // 紫
]

// 下線（または取り消し線など）を適用する関数
const applyUnderline = (color = null) => {
    if (!editor.value) return

    if (color) {
        underlineColor.value = color
    }

    const styleValue = `underline ${underlineStyle.value} ${underlineColor.value} ${underlineThickness.value}`

    // TextStyleエクステンションを使ってstyle属性をセット
    editor.value.chain().focus().setMark('textStyle', {
        style: `text-decoration: ${styleValue};`
    }).run()
}

// 下線を解除する関数
const removeUnderline = () => {
    if (!editor.value) return
    editor.value.chain().focus().unsetMark('textStyle').run()
}

// メニューの開閉切り替え
const toggleUnderlineMenu = () => {
    showUnderlineMenu.value = !showUnderlineMenu.value
}

const presetColors = [
    // 1列目：黒〜グレー
    '#000000', '#4b5563', '#9ca3af', '#d1d5db', '#ffffff',
    // 2列目：赤・ピンク
    '#dc2626', '#ef4444', '#f87171', '#fca5a5', '#ffe4e6',
    // 3列目：オレンジ・黄
    '#ea580c', '#f97316', '#fb923c', '#eab308', '#fef08a',
    // 4列目：緑・エメラルド
    '#16a34a', '#22c55e', '#4ade80', '#0d9488', '#14b8a6',
    // 5列目：青・紫
    '#2563eb', '#3b82f6', '#60a5fa', '#9333ea', '#a855f7',
]

// 現在選択されている文字色を取得（アイコンの下線色に反映）
const currentColor = computed(() => {
    return editor.value?.getAttributes('textStyle').color || '#000000'
})

const selectColor = (color) => {
    if (!editor.value) return
    editor.value.chain().focus().setColor(color).run()
    showColorPicker.value = false
}

const clearColor = () => {
    if (!editor.value) return
    editor.value.chain().focus().unsetColor().run()
    showColorPicker.value = false
}

// 現在選択中の見出しレベル
const currentHeadingLevel = computed(() => {
    if (!editor.value) return 'paragraph'
    if (editor.value.isActive('heading', { level: 1 })) return '1'
    if (editor.value.isActive('heading', { level: 2 })) return '2'
    if (editor.value.isActive('heading', { level: 3 })) return '3'
    if (editor.value.isActive('heading', { level: 4 })) return '4'
    return 'paragraph'
})

// ドロップダウン変更時の処理
const handleHeadingChange = (event) => {
    const val = event.target.value
    if (!editor.value) return

    if (val === 'paragraph') {
        editor.value.chain().focus().setParagraph().run()
    } else {
        editor.value.chain().focus().toggleHeading({ level: parseInt(val) }).run()
    }
}

//  画像メニュー表示制御（画像が選択されている時のみ表示）
const shouldShowImageMenu = ({ editor }) => {
    return editor.isActive('image')
}

//  テキストメニュー表示制御（画像選択時は非表示にする）
const shouldShowTextMenu = ({ editor }) => {
    return !editor.isActive('image') && !editor.state.selection.empty
}

//  画像の配置変更処理
const setImageAlignment = (alignment) => {
    if (!editor.value) return
    editor.value.chain().focus().setTextAlign(alignment).run()
}

//  画像の削除処理
const deleteSelectedImage = () => {
    if (!editor.value) return
    editor.value.chain().focus().deleteSelection().run()
}

// リンク入力ポップアップを開く
const openLinkInput = async () => {
    if (!editor.value) return
    const previousUrl = editor.value.getAttributes('link').href || ''
    linkUrl.value = previousUrl
    showLinkInput.value = true

    await nextTick()
    linkInputRef.value?.focus()
}

// 🔗 リンク適用
const applyLink = () => {
    if (!editor.value) return

    let url = linkUrl.value.trim()

    if (url === '') {
        removeLink()
        return
    }

    if (!/^https?:\/\//i.test(url) && !url.startsWith('mailto:') && !url.startsWith('tel:')) {
        url = `https://${url}`
    }

    editor.value
        .chain()
        .focus()
        .extendMarkRange('link')
        .setLink({ href: url })
        .run()

    closeLinkInput()
}

// 🔗 リンク解除
const removeLink = () => {
    if (!editor.value) return
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run()
    closeLinkInput()
}

// 🔗 ポップアップを閉じる
const closeLinkInput = () => {
    showLinkInput.value = false
    linkUrl.value = ''
    editor.value?.chain().focus().run()
}

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
.tiptap p.is-editor-empty:first-child::before {
    color: #9ca3af;
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}

.tiptap blockquote {
    border-left: 4px solid #3b82f6;
    padding-left: 1rem;
    font-style: italic;
    color: #475569;
    margin: 1rem 0;
}

/* ★ 画像配置に関するスタイル設定 */
.tiptap img {
    max-width: 100%;
    height: auto;
    border-radius: 0.75rem;
}

/* 左・中央・右揃えのスタイリング */
.tiptap [style*="text-align: left"] img,
.tiptap img[style*="text-align: left"] {
    margin-right: auto;
    margin-left: 0;
}

.tiptap [style*="text-align: center"] img,
.tiptap img[style*="text-align: center"] {
    margin-left: auto;
    margin-right: auto;
}

.tiptap [style*="text-align: right"] img,
.tiptap img[style*="text-align: right"] {
    margin-left: auto;
    margin-right: 0;
}

</style>