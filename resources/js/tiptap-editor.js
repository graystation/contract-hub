import { Editor, Extension } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';

/** Simple indent/outdent using margin-left on block nodes (Tab / Shift+Tab). */
const Indent = Extension.create({
    name: 'indent',
    addOptions() { return { step: 24, maxLevel: 8 }; },
    addGlobalAttributes() {
        return [{
            types: ['paragraph', 'heading'],
            attributes: {
                indent: {
                    default: 0,
                    parseHTML: el => Math.round((parseInt(el.style.marginLeft) || 0) / this.options.step),
                    renderHTML: attrs => attrs.indent > 0
                        ? { style: `margin-left: ${attrs.indent * this.options.step}px` }
                        : {},
                },
            },
        }];
    },
    addCommands() {
        return {
            indent: () => ({ state, commands }) => {
                const node = state.selection.$head.parent;
                return commands.updateAttributes(node.type.name, {
                    indent: Math.min((node.attrs.indent ?? 0) + 1, this.options.maxLevel),
                });
            },
            outdent: () => ({ state, commands }) => {
                const node = state.selection.$head.parent;
                return commands.updateAttributes(node.type.name, {
                    indent: Math.max((node.attrs.indent ?? 0) - 1, 0),
                });
            },
        };
    },
    addKeyboardShortcuts() {
        return {
            Tab:         () => this.editor.commands.indent(),
            'Shift-Tab': () => this.editor.commands.outdent(),
        };
    },
});

/** Shared Tiptap extensions list. */
function buildExtensions() {
    return [
        StarterKit.configure({ heading: { levels: [2, 3] } }),
        Underline,
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
        Indent,
    ];
}

/** Create a Tiptap Editor instance on the given element. */
function createEditor(editorEl, content, onUpdate) {
    return new Editor({
        element: editorEl,
        extensions: buildExtensions(),
        content: content || null,
        editorProps: {
            attributes: {
                class: 'prose prose-sm max-w-none px-4 py-3 focus:outline-none',
                style: 'min-height: 800px',
            },
        },
        onUpdate,
    });
}

// ---------------------------------------------------------------------------
// Alpine.js: contractForm — template selector + variable substitution
// ---------------------------------------------------------------------------
window.contractForm = (config = {}) => ({
    projectId:      config.projectId      || '',
    contractNumber: config.contractNumber || '',
    contractType:   config.contractType   || '',
    templateId:     config.templateId || '',

    applyTemplate() {
        if (!this.templateId) return;

        const templateMap = window.__contractTemplates__ || {};
        const projectMap  = window.__contractProjects__  || {};

        let body = templateMap[this.templateId] || '';
        if (!body) return;

        const proj  = projectMap[this.projectId] || {};
        const today = new Date().toLocaleDateString('ja-JP', {
            year: 'numeric', month: 'long', day: 'numeric',
        });

        const vars = {
            '{company_name}'    : proj.company_name   || '',
            '{contact_name}'    : proj.contact_name   || '',
            '{project_title}'   : proj.project_title  || '',
            '{start_date}'      : proj.start_date     || '',
            '{end_date}'        : proj.end_date       || '',
            '{contract_number}' : this.contractNumber || '（採番前）',
            '{contract_type}'   : this.contractType   || '',
            '{today}'           : today,
        };

        Object.entries(vars).forEach(([key, val]) => {
            body = body.replaceAll(key, val);
        });

        window.dispatchEvent(new CustomEvent('load-html', {
            detail: { target: 'body', html: body },
        }));
    },
});

// ---------------------------------------------------------------------------
// Alpine.js: tiptapEditor — rich text editor component
//
// The Tiptap Editor instance is kept in a closure variable (not in Alpine's
// reactive data) to prevent Alpine's Proxy from wrapping ProseMirror's
// internal objects, which causes "mismatched transaction" errors.
// ---------------------------------------------------------------------------
window.tiptapEditor = (config = {}) => {
    let editor = null;

    return {
        content: config.content || '',

        setup() {
            const editorEl = this.$el.querySelector('[data-tiptap]');
            const hiddenEl = this.$el.querySelector('[data-tiptap-value]');
            if (!editorEl) return;

            editor = createEditor(editorEl, this.content, ({ editor: e }) => {
                const html = e.getHTML();
                if (hiddenEl) hiddenEl.value = html;
                this.content = html;
            });

            if (hiddenEl) hiddenEl.value = this.content;
            window._tiptapEditor = this;
        },

        destroy() {
            editor?.destroy();
            editor = null;
        },

        /**
         * Load new HTML by destroying and recreating the editor.
         * Avoids ProseMirror "mismatched transaction" errors that occur
         * when setContent() is called inside an event dispatch cycle.
         */
        loadHtml(html) {
            const editorEl = this.$el.querySelector('[data-tiptap]');
            const hiddenEl = this.$el.querySelector('[data-tiptap-value]');

            if (editor) {
                editor.destroy();
                editor = null;
                if (editorEl) editorEl.innerHTML = '';
            }

            this.content = html || '';

            editor = createEditor(editorEl, html, ({ editor: e }) => {
                const h = e.getHTML();
                if (hiddenEl) hiddenEl.value = h;
                this.content = h;
            });

            if (hiddenEl) hiddenEl.value = html || '';
        },

        // ── State ────────────────────────────────────────────────────────────
        isActive(type, attrs) { return editor?.isActive(type, attrs ?? {}) ?? false; },

        // ── Block ────────────────────────────────────────────────────────────
        toggleH2()      { editor?.chain().focus().toggleHeading({ level: 2 }).run(); },
        toggleH3()      { editor?.chain().focus().toggleHeading({ level: 3 }).run(); },

        // ── Inline ───────────────────────────────────────────────────────────
        toggleBold()      { editor?.chain().focus().toggleBold().run(); },
        toggleItalic()    { editor?.chain().focus().toggleItalic().run(); },
        toggleUnderline() { editor?.chain().focus().toggleUnderline().run(); },

        // ── Alignment ────────────────────────────────────────────────────────
        alignLeft()   { editor?.chain().focus().setTextAlign('left').run(); },
        alignCenter() { editor?.chain().focus().setTextAlign('center').run(); },
        alignRight()  { editor?.chain().focus().setTextAlign('right').run(); },

        // ── Lists ────────────────────────────────────────────────────────────
        toggleBullet()  { editor?.chain().focus().toggleBulletList().run(); },
        toggleOrdered() { editor?.chain().focus().toggleOrderedList().run(); },

        // ── Indent ───────────────────────────────────────────────────────────
        indent()  { editor?.chain().focus().indent().run(); },
        outdent() { editor?.chain().focus().outdent().run(); },

        // ── Insert ───────────────────────────────────────────────────────────
        insertHr()       { editor?.chain().focus().setHorizontalRule().run(); },
        insertText(text) { editor?.chain().focus().insertContent(text).run(); },

        // ── Misc ─────────────────────────────────────────────────────────────
        clearFormat() { editor?.chain().focus().clearNodes().unsetAllMarks().run(); },
    };
};
