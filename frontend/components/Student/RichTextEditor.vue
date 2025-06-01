<template>
  <div class="rich-text-editor">
    <editor-content :editor="editor" class="prose dark:prose-invert max-w-none editor-container" />

    <div
      class="editor-toolbar fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-1 md:p-2 flex gap-1 md:gap-2 border border-gray-200 dark:border-gray-700"
    >
      <button
        v-for="item in toolbarItems"
        :key="item.action"
        @click="item.action"
        :class="[
          'p-1 md:p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300',
          { 'bg-gray-100 dark:bg-gray-700': item.isActive?.() }
        ]"
      >
        <span class="material-icons text-xs md:text-base">{{ item.icon }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Image from '@tiptap/extension-image';
import Table from '@tiptap/extension-table';
import TableRow from '@tiptap/extension-table-row';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TextAlign from '@tiptap/extension-text-align';
import { onBeforeUnmount, watch } from 'vue';

const props = defineProps<{
  modelValue: string;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const editor = new Editor({
  extensions: [
    StarterKit,
    Underline,
    Image,
    Table,
    TableRow,
    TableCell,
    TableHeader,
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
  ],
  content: props.modelValue,
  onUpdate: () => {
    emit('update:modelValue', editor.getHTML());
  },
});

const toolbarItems = [
  {
    icon: 'format_bold',
    action: () => editor.chain().focus().toggleBold().run(),
    isActive: () => editor.isActive('bold'),
  },
  {
    icon: 'format_italic',
    action: () => editor.chain().focus().toggleItalic().run(),
    isActive: () => editor.isActive('italic'),
  },
  {
    icon: 'format_underlined',
    action: () => editor.chain().focus().toggleUnderline().run(),
    isActive: () => editor.isActive('underline'),
  },
  {
    icon: 'format_size',
    action: () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
    isActive: () => editor.isActive('heading', { level: 2 }),
  },
  {
    icon: 'format_list_bulleted',
    action: () => editor.chain().focus().toggleBulletList().run(),
    isActive: () => editor.isActive('bulletList'),
  },
  {
    icon: 'format_list_numbered',
    action: () => editor.chain().focus().toggleOrderedList().run(),
    isActive: () => editor.isActive('orderedList'),
  },
  {
    icon: 'format_align_left',
    action: () => editor.chain().focus().setTextAlign('left').run(),
    isActive: () => editor.isActive({ textAlign: 'left' }),
  },
  {
    icon: 'format_align_center',
    action: () => editor.chain().focus().setTextAlign('center').run(),
    isActive: () => editor.isActive({ textAlign: 'center' }),
  },
  {
    icon: 'format_align_right',
    action: () => editor.chain().focus().setTextAlign('right').run(),
    isActive: () => editor.isActive({ textAlign: 'right' }),
  },
  {
    icon: 'format_align_justify',
    action: () => editor.chain().focus().setTextAlign('justify').run(),
    isActive: () => editor.isActive({ textAlign: 'justify' }),
  },
];

onBeforeUnmount(() => {
  editor.destroy();
});

watch(
  () => props.modelValue,
  (newContent) => {
    if (newContent !== editor.getHTML()) {
      editor.commands.setContent(newContent, false);
    }
  }
);
</script>

<style>
@import 'material-icons/iconfont/material-icons.css';

.material-icons {
  font-size: 20px;
}

.ProseMirror {
  min-height: 300px;
  padding: 1rem;
  outline: none;
  max-width: 800px;
  margin: 0 auto;
}

.editor-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 1rem;
}

.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #64748b;
  pointer-events: none;
  height: 0;
}

.ProseMirror p {
  margin: 0.5em 0;
  line-height: 1.4;
}

/* Table styles */
.ProseMirror table {
  border-collapse: collapse;
  margin: 0;
  overflow: hidden;
  table-layout: fixed;
  width: 100%;
}

.ProseMirror td,
.ProseMirror th {
  border: 2px solid #ced4da;
  box-sizing: border-box;
  min-width: 1em;
  padding: 3px 5px;
  position: relative;
  vertical-align: top;
}

.ProseMirror th {
  background-color: #f8f9fa;
  font-weight: bold;
  text-align: left;
}

/* En modo oscuro */
.dark .ProseMirror th {
  background-color: #000;
  color: #fff;
  border-color: #4b5563;
}

.dark .ProseMirror td {
  border-color: #4b5563;
}
</style>
