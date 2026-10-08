{{-- 管理员头像与用户名 --}}
@php
    $record = $getRecord();
    $avatarUrl = $record->getFilamentAvatarUrl();
@endphp

<div style="display: inline-flex; align-items: center; gap: 0.5rem;">
    @if ( $avatarUrl )
        <img
            src="{{ $avatarUrl }}"
            alt="{{ $record->name }}"
            style="display: block; width: 1.25rem; height: 1.25rem; flex-shrink: 0; border-radius: 50%; object-fit: cover;"
        >
    @endif
    <span>{{ $record->name }}</span>
</div>
