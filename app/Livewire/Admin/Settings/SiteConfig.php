<?php

namespace App\Livewire\Admin\Settings;

use App\Models\SiteSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class SiteConfig extends Component
{
    use WithFileUploads;

    public string $site_name = '';

    public string $tagline = '';

    public string $address = '';

    public string $email = '';

    public string $phone = '';

    public string $facebook = '';

    public string $instagram = '';

    public string $youtube = '';

    public bool $registration_enabled = true;

    public $logo = null;

    public $hero = null;

    public $favicon = null;

    public bool $saved = false;

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->site_name = $settings->site_name;
        $this->tagline = (string) $settings->tagline;
        $this->address = (string) $settings->address;
        $this->email = (string) $settings->email;
        $this->phone = (string) $settings->phone;
        $this->facebook = (string) $settings->facebook;
        $this->instagram = (string) $settings->instagram;
        $this->youtube = (string) $settings->youtube;
        $this->registration_enabled = (bool) $settings->registration_enabled;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'registration_enabled' => ['boolean'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'hero' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            // Rule 'image' tidak mencakup .ico — pakai 'file' + mimes eksplisit.
            'favicon' => ['nullable', 'file', 'mimes:ico,png,svg', 'max:512'],
        ]);

        $settings = SiteSetting::current();
        $settings->update(collect($validated)->except(['logo', 'hero', 'favicon'])->map(
            fn ($value) => $value === '' ? null : $value
        )->all());

        if ($this->logo) {
            $settings->addMedia($this->logo->getRealPath())
                ->usingFileName('logo.'.$this->logo->getClientOriginalExtension())
                ->toMediaCollection('logo');
            $this->logo = null;
        }

        if ($this->hero) {
            $settings->addMedia($this->hero->getRealPath())
                ->usingFileName('hero.'.$this->hero->getClientOriginalExtension())
                ->toMediaCollection('hero');
            $this->hero = null;
        }

        if ($this->favicon) {
            $settings->addMedia($this->favicon->getRealPath())
                ->usingFileName('favicon.'.$this->favicon->getClientOriginalExtension())
                ->toMediaCollection('favicon');
            $this->favicon = null;
        }

        SiteSetting::forgetCurrent();
        $this->saved = true;
    }

    public function removeLogo(): void
    {
        SiteSetting::current()->clearMediaCollection('logo');
        SiteSetting::forgetCurrent();
    }

    public function removeHero(): void
    {
        SiteSetting::current()->clearMediaCollection('hero');
        SiteSetting::forgetCurrent();
    }

    public function removeFavicon(): void
    {
        SiteSetting::current()->clearMediaCollection('favicon');
        SiteSetting::forgetCurrent();
    }

    public function render()
    {
        return view('livewire.admin.settings.site-config', [
            'settings' => SiteSetting::current(),
        ]);
    }
}
