{{-- 管理员名称与头像 --}}
<span style="display: inline-flex; align-items: center; gap: 0.75rem;">
    @if ( $avatarUrl )
        <img src="{{ $avatarUrl }}" alt="" width="24" height="24" style="width: 24px; height: 24px; flex-shrink: 0; border-radius: 50%; object-fit: cover;">
    @endif
    <span>{{ $name }}</span>
</span>
