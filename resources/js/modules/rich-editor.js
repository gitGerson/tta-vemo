import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';

const createEditor = (wrapper) => {
    if (!(wrapper instanceof HTMLElement) || wrapper.dataset.richEditorInitialized === '1') {
        return;
    }

    const contentElement = wrapper.querySelector('[data-rich-editor-content]');
    const hiddenInput = wrapper.querySelector('input[data-rich-editor-input]');
    const imageInput = wrapper.querySelector('input[data-rich-editor-image-input]');

    if (!(contentElement instanceof HTMLElement) || !(hiddenInput instanceof HTMLInputElement)) {
        return;
    }

    const placeholder = String(wrapper.dataset.placeholder || 'Write here...');
    const minHeight = String(wrapper.dataset.minHeight || '180px');
    const imageUploadUrl = String(wrapper.dataset.imageUploadUrl || '');
    contentElement.style.minHeight = minHeight;

    const editor = new Editor({
        element: contentElement,
        extensions: [
            StarterKit,
            Underline,
            Link.configure({
                openOnClick: false,
                autolink: true,
                linkOnPaste: true,
            }),
            Image.configure({
                inline: false,
                allowBase64: imageUploadUrl === '',
            }),
            Placeholder.configure({
                placeholder,
            }),
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
        ],
        content: String(hiddenInput.value || ''),
        onUpdate: ({ editor: tiptap }) => {
            hiddenInput.value = tiptap.getHTML();
            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        },
    });

    const toolbarButtons = Array.from(wrapper.querySelectorAll('[data-editor-action]'));

    const uploadEditorImage = async (file) => {
        if (!(file instanceof File) || !file.type.startsWith('image/')) {
            return null;
        }

        if (!imageUploadUrl) {
            return await new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = () => { resolve(String(reader.result || '')); };
                reader.readAsDataURL(file);
            });
        }

        const formData = new FormData();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        formData.append('image', file);

        const response = await fetch(imageUploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to upload image.');
        }

        const payload = await response.json();
        return String(payload.url || '');
    };

    const updateActiveState = () => {
        toolbarButtons.forEach((button) => {
            if (!(button instanceof HTMLElement)) return;

            const action = button.dataset.editorAction || '';
            let isActive = false;

            if (action === 'bold') isActive = editor.isActive('bold');
            else if (action === 'paragraph') isActive = editor.isActive('paragraph');
            else if (action === 'heading1') isActive = editor.isActive('heading', { level: 1 });
            else if (action === 'heading2') isActive = editor.isActive('heading', { level: 2 });
            else if (action === 'heading3') isActive = editor.isActive('heading', { level: 3 });
            else if (action === 'italic') isActive = editor.isActive('italic');
            else if (action === 'underline') isActive = editor.isActive('underline');
            else if (action === 'strike') isActive = editor.isActive('strike');
            else if (action === 'bulletList') isActive = editor.isActive('bulletList');
            else if (action === 'orderedList') isActive = editor.isActive('orderedList');
            else if (action === 'alignLeft') isActive = editor.isActive({ textAlign: 'left' }) || (!editor.isActive({ textAlign: 'center' }) && !editor.isActive({ textAlign: 'right' }) && !editor.isActive({ textAlign: 'justify' }));
            else if (action === 'alignCenter') isActive = editor.isActive({ textAlign: 'center' });
            else if (action === 'alignRight') isActive = editor.isActive({ textAlign: 'right' });
            else if (action === 'alignJustify') isActive = editor.isActive({ textAlign: 'justify' });
            else if (action === 'blockquote') isActive = editor.isActive('blockquote');
            else if (action === 'codeBlock') isActive = editor.isActive('codeBlock');
            else if (action === 'link') isActive = editor.isActive('link');

            button.classList.toggle('is-active', isActive);
        });
    };

    toolbarButtons.forEach((button) => {
        if (!(button instanceof HTMLElement)) return;

        button.addEventListener('click', () => {
            const action = button.dataset.editorAction || '';
            const chain = editor.chain().focus();

            if (action === 'paragraph') chain.setParagraph().run();
            else if (action === 'heading1') chain.toggleHeading({ level: 1 }).run();
            else if (action === 'heading2') chain.toggleHeading({ level: 2 }).run();
            else if (action === 'heading3') chain.toggleHeading({ level: 3 }).run();
            else if (action === 'bold') chain.toggleBold().run();
            else if (action === 'italic') chain.toggleItalic().run();
            else if (action === 'underline') chain.toggleUnderline().run();
            else if (action === 'strike') chain.toggleStrike().run();
            else if (action === 'bulletList') chain.toggleBulletList().run();
            else if (action === 'orderedList') chain.toggleOrderedList().run();
            else if (action === 'alignLeft') chain.setTextAlign('left').run();
            else if (action === 'alignCenter') chain.setTextAlign('center').run();
            else if (action === 'alignRight') chain.setTextAlign('right').run();
            else if (action === 'alignJustify') chain.setTextAlign('justify').run();
            else if (action === 'blockquote') chain.toggleBlockquote().run();
            else if (action === 'codeBlock') chain.toggleCodeBlock().run();
            else if (action === 'horizontalRule') chain.setHorizontalRule().run();
            else if (action === 'undo') chain.undo().run();
            else if (action === 'redo') chain.redo().run();
            else if (action === 'link') {
                const currentHref = editor.getAttributes('link').href || '';
                const nextHref = window.prompt('URL', currentHref);

                if (nextHref === null) return;

                if (nextHref.trim() === '') {
                    editor.chain().focus().unsetLink().run();
                } else {
                    editor.chain().focus().setLink({ href: nextHref.trim() }).run();
                }
            } else if (action === 'image' && imageInput instanceof HTMLInputElement) {
                imageInput.click();
            }

            updateActiveState();
        });
    });

    if (imageInput instanceof HTMLInputElement) {
        imageInput.addEventListener('change', async () => {
            const [file] = Array.from(imageInput.files || []);

            if (!file || !file.type.startsWith('image/')) return;

            try {
                const source = await uploadEditorImage(file);
                if (source) editor.chain().focus().setImage({ src: source }).run();
            } catch {
                window.alert('Gagal upload gambar editor. Silakan coba lagi.');
            } finally {
                imageInput.value = '';
                updateActiveState();
            }
        });
    }

    contentElement.addEventListener('paste', async (event) => {
        const clipboardItems = Array.from(event.clipboardData?.items || []);
        const imageItem = clipboardItems.find((item) => item.type.startsWith('image/'));

        if (!imageItem) return;

        const file = imageItem.getAsFile();
        if (!file) return;

        event.preventDefault();

        try {
            const source = await uploadEditorImage(file);
            if (source) {
                editor.chain().focus().setImage({ src: source }).run();
                updateActiveState();
            }
        } catch {
            window.alert('Gagal upload gambar editor. Silakan coba lagi.');
        }
    });

    editor.on('selectionUpdate', updateActiveState);
    editor.on('transaction', updateActiveState);
    updateActiveState();

    wrapper.dataset.richEditorInitialized = '1';
    wrapper._tiptapEditor = editor;
};

const init = (scope = document) => {
    const root = scope instanceof Element || scope === document ? scope : document;
    root.querySelectorAll('[data-form-rich-editor="1"]').forEach((wrapper) => {
        createEditor(wrapper);
    });
};

window.FormRichEditor = window.FormRichEditor || {};
window.FormRichEditor.init = init;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => init(document));
} else {
    init(document);
}
