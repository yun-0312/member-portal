import { Extension } from "@tiptap/core";
import Suggestion from "@tiptap/suggestion";
import { VueRenderer } from "@tiptap/vue-3";
import tippy from "tippy.js";
import "tippy.js/dist/tippy.css"; // 👈 tippy.jsのCSS（これが無いとエラーになることがあります）
import CommandsList from "./CommandsList.vue";

export const SlashCommands = Extension.create({
    name: "slashCommands",

    addOptions() {
        return {
            suggestion: {
                char: "/",
                command: ({ editor, range, props }) => {
                    props.command({ editor, range });
                },
            },
        };
    },

    addProseMirrorPlugins() {
        return [
            Suggestion({
                editor: this.editor,
                ...this.options.suggestion,
            }),
        ];
    },
});

export const getSuggestionItems =
    (openMediaModal) =>
    ({ query }) => {
        const items = [
            {
                title: "見出し 1 (H1)",
                description: "大見出しを作成",
                icon: "📝",
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .setNode("heading", { level: 1 })
                        .run();
                },
            },
            {
                title: "見出し 2 (H2)",
                description: "中見出しを作成",
                icon: "📌",
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .setNode("heading", { level: 2 })
                        .run();
                },
            },
            {
                title: "見出し 3 (H3)",
                description: "小見出しを作成",
                icon: "🔹",
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .setNode("heading", { level: 3 })
                        .run();
                },
            },
            {
                title: "太字",
                description: "テキストを太字にする",
                icon: "B",
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleBold()
                        .run();
                },
            },
            {
                title: "箇条書きリスト",
                description: "シンプルなリストを作成",
                icon: "•☰",
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleBulletList()
                        .run();
                },
            },
            {
                title: "番号付きリスト",
                description: "数字付きリストを作成",
                icon: "1.☰",
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleOrderedList()
                        .run();
                },
            },
            {
                title: "画像挿入",
                description: "メディアライブラリを開く",
                icon: "🖼️",
                command: ({ editor, range }) => {
                    editor.chain().focus().deleteRange(range).run();
                    if (openMediaModal) openMediaModal();
                },
            },
        ];

        return items.filter(
            (item) =>
                item.title.toLowerCase().includes(query.toLowerCase()) ||
                item.description.toLowerCase().includes(query.toLowerCase()),
        );
    };

export const renderItems = () => {
    let component;
    let popup;

    return {
        onStart: (props) => {
            component = new VueRenderer(CommandsList, {
                props,
                editor: props.editor,
            });

            if (!props.clientRect) return;

            popup = tippy("body", {
                getReferenceClientRect: props.clientRect,
                appendTo: () => document.body,
                content: component.element,
                showOnCreate: true,
                interactive: true,
                trigger: "manual",
                placement: "bottom-start",
            });
        },

        onUpdate(props) {
            component?.updateProps(props);

            if (!props.clientRect) return;

            popup[0]?.setProps({
                getReferenceClientRect: props.clientRect,
            });
        },

        onKeyDown(props) {
            if (props.event.key === "Escape") {
                popup[0]?.hide();
                return true;
            }

            return component?.ref?.onKeyDown(props);
        },

        onExit() {
            popup[0]?.destroy();
            component?.destroy();
        },
    };
};
