{{-- 管理员等级分组与可填写范围 --}}
<div class="admin-level-help">
    @php( $thresholds = array_values( $levels ) )
    <dl class="admin-level-help-groups">
        @foreach ( $levels as $name => $minimum )
            <div class="admin-level-help-group">
                <dt>{{ __( "lar::frame.levels.{$name}" ) }}</dt>
                <dd>{{ __( $loop->last ? 'lar::AdminControl.AdminUser.levelGroupMinimum' : 'lar::AdminControl.AdminUser.levelGroupRange', [
                    'min' => $minimum,
                    'max' => $loop->last ? null : $thresholds[$loop->index + 1] - 1,
                ] ) }}</dd>
            </div>
        @endforeach
    </dl>
    <p class="admin-level-help-limit">{{ __( 'lar::AdminControl.AdminUser.levelAllowedRange', ['max' => $maximum] ) }}</p>
</div>

<style>
    /* 等级参考说明，数值按列对齐并随容器宽度换行 */
    .admin-level-help { width: 100%; padding: .75rem; border: 1px solid var(--gray-200); border-radius: .5rem; background: var(--gray-50); color: var(--gray-500); font-size: .75rem; line-height: 1.5; }
    .admin-level-help .admin-level-help-groups { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 14rem), 1fr)); gap: .5rem 1.5rem; margin: 0; }
    .admin-level-help .admin-level-help-group { display: flex; align-items: baseline; justify-content: space-between; gap: .75rem; min-width: 0; }
    .admin-level-help .admin-level-help-group dt { min-width: 0; overflow-wrap: anywhere; }
    .admin-level-help .admin-level-help-group dd { flex-shrink: 0; margin: 0; color: var(--gray-700); font-variant-numeric: tabular-nums; white-space: nowrap; }
    .admin-level-help .admin-level-help-limit { margin: .625rem 0 0; padding-top: .625rem; border-top: 1px solid var(--gray-200); color: var(--primary-600); font-weight: 500; }
    .dark .admin-level-help { border-color: var(--gray-700); background: var(--gray-900); color: var(--gray-400); }
    .dark .admin-level-help .admin-level-help-group dd { color: var(--gray-300); }
    .dark .admin-level-help .admin-level-help-limit { border-color: var(--gray-700); color: var(--primary-400); }
</style>
