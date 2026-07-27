@props(['member', 'class' => 'aspect-[3/4] w-full rounded-xl'])

@if ($member->photoUrl())
    <img src="{{ $member->photoUrl() }}" alt="{{ $member->full_name }}" class="{{ $class }} object-cover" />
@else
    <x-public.image-placeholder icon="person" class="{{ $class }}" />
@endif
