import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';

/**
 * Alpine.js component for Tiptap rich-text editor.
 * Usage: x-data="tiptapEditor({ content: '...' })"
 */
window.tiptapEditor = (config = {}) => ({
    editor: null,
    content: config.content || '',

    init() {
        const editorEl  = this.$el.querySelector('[data-tiptap]');
        const hiddenEl  = this.$el.querySelector('[data-tiptap-value]');

        this.editor = new Editor({
            element: editorEl,
            extensions: [
                StarterKit.configure({ heading: { levels: [2, 3] } }),
                Underline,
            ],
            content: this.content || null,
            editorProps: {
                attributes: {
                    class: 'prose prose-sm max-w-none px-4 py-3 focus:outline-none',
                    style: 'min-height: 800px',
                },
            },
            onUpdate: ({ editor }) => {
                const html = editor.getHTML();
                if (hiddenEl) hiddenEl.value = html;
                this.content = html;
            },
        });

        if (hiddenEl) hiddenEl.value = this.content;
    },

    destroy() {
        this.editor?.destroy();
    },

    // Toolbar helpers
    isActive(type, attrs)  { return this.editor?.isActive(type, attrs ?? {}) ?? false; },
    toggleBold()           { this.editor?.chain().focus().toggleBold().run(); },
    toggleUnderline()      { this.editor?.chain().focus().toggleUnderline().run(); },
    toggleH2()             { this.editor?.chain().focus().toggleHeading({ level: 2 }).run(); },
    toggleH3()             { this.editor?.chain().focus().toggleHeading({ level: 3 }).run(); },
    toggleBullet()         { this.editor?.chain().focus().toggleBulletList().run(); },
    toggleOrdered()        { this.editor?.chain().focus().toggleOrderedList().run(); },
    clearFormat()          { this.editor?.chain().focus().clearNodes().unsetAllMarks().run(); },

    /** Replace editor content from outside (e.g. template load). */
    loadHtml(html) {
        this.editor?.commands.setContent(html || '', true);
        const hiddenEl = this.$el.querySelector('[data-tiptap-value]');
        if (hiddenEl) hiddenEl.value = html || '';
        this.content = html || '';
    },
});
