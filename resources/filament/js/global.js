/* 后台弹窗打开后重置内容滚动位置，等待显示和焦点处理完成 */
document.addEventListener( 'x-modal-opened', ( event ) => {
    const modal = document.getElementById( event.detail.id );
    if ( !modal ) { return; }

    requestAnimationFrame( () => requestAnimationFrame( () => {
        const content = modal.querySelector( ':scope > .fi-modal-window-ctn > .fi-modal-window > .fi-modal-content' );
        if ( !content || !modal.isConnected ) { return; }
        content.scrollTo( { top: 0, left: 0, behavior: 'instant' } );
    } ) );
} );
