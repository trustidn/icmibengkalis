import Quill from 'quill';

/**
 * Komponen Alpine "richEditor" — Quill 2 terikat dua arah ke properti Livewire
 * (via entangle) dengan upload gambar ke endpoint internal dan embed video URL.
 */
export default function richEditor({ content, uploadUrl }) {
    return {
        content,

        init() {
            const quill = new Quill(this.$refs.editor, {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            [{ header: [2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['blockquote', 'link', 'image', 'video'],
                            ['clean'],
                        ],
                        handlers: {
                            image: () => this.pickImage(quill),
                        },
                    },
                },
            });

            this.quill = quill;
            quill.root.innerHTML = this.content ?? '';

            quill.on('text-change', (delta, oldDelta, source) => {
                if (source === 'user') {
                    this.content = quill.root.innerHTML;
                }
            });

            // Sinkron balik saat Livewire mengubah nilai (mis. reset form).
            this.$watch('content', (value) => {
                if ((value ?? '') !== quill.root.innerHTML) {
                    quill.root.innerHTML = value ?? '';
                }
            });
        },

        pickImage(quill) {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/png,image/jpeg,image/webp';
            input.onchange = async () => {
                const file = input.files?.[0];
                if (!file) return;

                const body = new FormData();
                body.append('image', file);

                try {
                    const response = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                ?? document.querySelector('input[name="_token"]')?.value,
                        },
                        body,
                    });

                    if (!response.ok) throw new Error(`Upload gagal (${response.status})`);

                    const { url } = await response.json();
                    const range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', url, 'user');
                    quill.setSelection(range.index + 1);
                    this.content = quill.root.innerHTML;
                } catch (error) {
                    alert('Gagal mengunggah gambar. Periksa ukuran (maks. 4 MB) dan format berkas.');
                    console.error(error);
                }
            };
            input.click();
        },
    };
}
