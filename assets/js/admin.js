document.addEventListener('click', (event) => {
  const removeButton = event.target.closest('[data-remove-item]')
  if (removeButton) {
    removeButton.closest('[data-editor-item]')?.remove()
    return
  }

  const addButton = event.target.closest('[data-add-item]')
  if (!addButton) return

  const editor = addButton.closest('[data-editor]')
  const template = editor?.querySelector(':scope > [data-editor-template]')
  if (!editor || !template) return

  const index = Number(editor.dataset.nextIndex || 0)
  const wrapper = document.createElement('div')
  wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', index)
  editor.dataset.nextIndex = index + 1
  editor.insertBefore(wrapper.firstElementChild, template)
})
