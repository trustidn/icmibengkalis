<?php

namespace App\Livewire\Public\Contact;

use App\Services\Content\ContactService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Form extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public bool $sent = false;

    public function submit(ContactService $contact): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $key = 'contact-form:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('message', 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.');

            return;
        }

        RateLimiter::hit($key, 300);

        $contact->submit($validated);

        $this->reset(['name', 'email', 'subject', 'message']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.public.contact.form')
            ->layout('components.layouts.public', [
                'metaTitle' => 'Kontak — '.config('app.name'),
            ]);
    }
}
