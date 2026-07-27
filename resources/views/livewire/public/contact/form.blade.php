<div>
    <x-public.page-header eyebrow="Hubungi Kami" title="Kontak" subtitle="Sampaikan pesan atau pertanyaan Anda kepada pengurus ICMI Kabupaten Bengkalis." />

    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-gutter">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-primary text-on-primary rounded-xl p-8 md:p-10 space-y-6">
                    <h2 class="font-headline-md text-headline-md">Sekretariat</h2>
                    <p class="flex gap-3 font-body-md text-on-primary/90">
                        <span class="material-symbols-outlined shrink-0">location_on</span>
                        <span>{{ __('footer.address') }}</span>
                    </p>
                    <p class="flex gap-3 font-body-md text-on-primary/90">
                        <span class="material-symbols-outlined shrink-0">mail</span>
                        <span>{{ __('footer.email') }}</span>
                    </p>
                </div>
            </div>

            <div class="lg:col-span-3">
                <form wire:submit="submit" class="bg-white border border-outline-variant/30 rounded-xl p-8 md:p-10 space-y-6 card-shadow">
                    @if ($sent)
                        <div class="flex items-start gap-3 bg-primary-container/20 border border-primary-container/40 rounded-lg px-5 py-4">
                            <span class="material-symbols-outlined text-primary shrink-0">check_circle</span>
                            <p class="font-body-md text-on-surface">Pesan Anda telah terkirim. Terima kasih.</p>
                        </div>
                    @endif

                    <div>
                        <label for="name" class="block font-label-lg text-label-lg text-on-surface mb-2">Nama</label>
                        <input id="name" type="text" wire:model="name"
                               class="w-full bg-white border rounded-lg px-4 py-3 font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary {{ $errors->has('name') ? 'border-error' : 'border-outline-variant/40' }}" />
                        @error('name') <p class="mt-1 text-error text-label-lg font-label-lg">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block font-label-lg text-label-lg text-on-surface mb-2">Email</label>
                        <input id="email" type="email" wire:model="email"
                               class="w-full bg-white border rounded-lg px-4 py-3 font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary {{ $errors->has('email') ? 'border-error' : 'border-outline-variant/40' }}" />
                        @error('email') <p class="mt-1 text-error text-label-lg font-label-lg">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="subject" class="block font-label-lg text-label-lg text-on-surface mb-2">Subjek</label>
                        <input id="subject" type="text" wire:model="subject"
                               class="w-full bg-white border rounded-lg px-4 py-3 font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary {{ $errors->has('subject') ? 'border-error' : 'border-outline-variant/40' }}" />
                        @error('subject') <p class="mt-1 text-error text-label-lg font-label-lg">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="message" class="block font-label-lg text-label-lg text-on-surface mb-2">Pesan</label>
                        <textarea id="message" rows="6" wire:model="message"
                                  class="w-full bg-white border rounded-lg px-4 py-3 font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary {{ $errors->has('message') ? 'border-error' : 'border-outline-variant/40' }}"></textarea>
                        @error('message') <p class="mt-1 text-error text-label-lg font-label-lg">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                            class="bg-primary text-on-primary px-10 py-3.5 rounded-full font-label-lg text-label-lg hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 transition-all active:scale-95">
                        Kirim
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
