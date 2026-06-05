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
    templateId:     '',

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
// ---------------------------------------------------------------------------
window.tiptapEditor = (config = {}) => ({
    editor:  null,
    content: config.content || '',

    init() {
        const editorEl = this.$el.querySelector('[data-tiptap]');
        const hiddenEl = this.$el.querySelector('[data-tiptap-value]');

        this.editor = createEditor(editorEl, this.content, ({ editor }) => {
            const html = editor.getHTML();
            if (hiddenEl) hiddenEl.value = html;
            this.content = html;
        });

        if (hiddenEl) hiddenEl.value = this.content;
    },

    destroy() {
        this.editor?.destroy();
    },

    /**
     * Load new HTML by destroying and recreating the editor.
     * This avoids ProseMirror "mismatched transaction" errors that occur
     * when setContent() is called inside an event dispatch cycle.
     */
    loadHtml(html) {
        const editorEl = this.$el.querySelector('[data-tiptap]');
        const hiddenEl = this.$el.querySelector('[data-tiptap-value]');

        if (this.editor) {
            this.editor.destroy();
            if (editorEl) editorEl.innerHTML = '';
        }

        this.content = html || '';

        this.editor = createEditor(editorEl, html, ({ editor }) => {
            const h = editor.getHTML();
            if (hiddenEl) hiddenEl.value = h;
            this.content = h;
        });

        if (hiddenEl) hiddenEl.value = html || '';
    },

    // ── State ────────────────────────────────────────────────────────────
    isActive(type, attrs) { return this.editor?.isActive(type, attrs ?? {}) ?? false; },

    // ── Block ────────────────────────────────────────────────────────────
    toggleH2()      { this.editor?.chain().focus().toggleHeading({ level: 2 }).run(); },
    toggleH3()      { this.editor?.chain().focus().toggleHeading({ level: 3 }).run(); },

    // ── Inline ───────────────────────────────────────────────────────────
    toggleBold()      { this.editor?.chain().focus().toggleBold().run(); },
    toggleItalic()    { this.editor?.chain().focus().toggleItalic().run(); },
    toggleUnderline() { this.editor?.chain().focus().toggleUnderline().run(); },

    // ── Alignment ────────────────────────────────────────────────────────
    alignLeft()   { this.editor?.chain().focus().setTextAlign('left').run(); },
    alignCenter() { this.editor?.chain().focus().setTextAlign('center').run(); },
    alignRight()  { this.editor?.chain().focus().setTextAlign('right').run(); },

    // ── Lists ────────────────────────────────────────────────────────────
    toggleBullet()  { this.editor?.chain().focus().toggleBulletList().run(); },
    toggleOrdered() { this.editor?.chain().focus().toggleOrderedList().run(); },

    // ── Indent ───────────────────────────────────────────────────────────
    indent()  { this.editor?.chain().focus().indent().run(); },
    outdent() { this.editor?.chain().focus().outdent().run(); },

    // ── Insert ───────────────────────────────────────────────────────────
    insertHr() { this.editor?.chain().focus().setHorizontalRule().run(); },

    // ── Misc ─────────────────────────────────────────────────────────────
    clearFormat() { this.editor?.chain().focus().clearNodes().unsetAllMarks().run(); },
});
