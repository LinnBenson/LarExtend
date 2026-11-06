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

/**
 * 显示一个通知消息
 * @param {string} message 消息内容
 * @param {string} status 消息状态，可选值：error, info, success, warning
 * @returns {void} 无返回值
 */
const unitToast = function( message, status = 'info' ) {
    const statusMap = {
        error: 'danger',
        info: 'info',
        success: 'success',
        warning: 'warning'
    };
    status = Object.hasOwn( statusMap, status ) ? statusMap[status] : 'danger';
    const text = document.createElement( 'span' );
    text.textContent = message;
    new FilamentNotification().title( text.innerHTML )[status]().send();
};

/**
 * 创建后台请求构建对象
 * @param {string} link 请求链接
 * @returns {SendRequestBuild} 请求构建对象
 */
const sendRequest = function( link ) { return new SendRequestBuild( link ); };
class SendRequestBuild {
    constructor( link ) {
        this.link = link;
        this.headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector( 'meta[name="csrf-token"]' ).content
        };
        this.payload = {};
        this.timeoutMs = 15000;
        this.requestOptions = {};
        this.successCallback = null;
        this.errorCallback = null;
        this.loadingCallback = null;
        this.middlewareCallback = null;
    }
    /** 合并请求头。 */
    header( data ) { Object.assign( this.headers, data ); return this; }
    /** 设置超时时间，单位毫秒，0 表示不限时。 */
    timeout( milliseconds ) { this.timeoutMs = milliseconds; return this; }
    /** 合并普通请求参数，也可直接传入 FormData。 */
    data( data ) {
        this.payload = data instanceof FormData || this.payload instanceof FormData
            ? data : { ...this.payload, ...data };
        return this;
    }
    /** 合并 fetch 附加配置，请求方法、数据和超时由构建器管理。 */
    join( options ) { Object.assign( this.requestOptions, options ); return this; }
    /** 设置响应中间件，返回新响应对象或 false 以停止后续处理。 */
    middleware( callback = null ) { this.middlewareCallback = callback; return this; }
    /** 设置业务成功回调，接收完整响应对象。 */
    success( callback = null ) { this.successCallback = callback; return this; }
    /** 设置请求失败回调，接收异常对象。 */
    error( callback = null ) { this.errorCallback = callback; return this; }
    /** 设置加载状态回调，开始为 true，结束为 false。 */
    loading( callback = null ) { this.loadingCallback = callback; return this; }
    // 使用指定方法发送请求。
    get() { return this.send( 'GET' ); }
    post() { return this.send( 'POST' ); }
    put() { return this.send( 'PUT' ); }
    delete() { return this.send( 'DELETE' ); }
    patch() { return this.send( 'PATCH' ); }
    options() { return this.send( 'OPTIONS' ); }
    head() { return this.send( 'HEAD' ); }
    /** 将嵌套参数转换为 Laravel 支持的查询字符串。 */
    appendQuery( params, key, value ) {
        if ( value !== null && typeof value === 'object' ) {
            Object.entries( value ).forEach( ( [name, item] ) => this.appendQuery( params, `${key}[${name}]`, item ) );
            return;
        }
        params.append( key, value ?? '' );
    }
    /** 构建请求地址、请求头和请求体。 */
    buildRequest( method, signal ) {
        const url = new URL( this.link, window.location.href );
        const headers = new Headers( this.headers );
        if ( url.origin !== window.location.origin ) { headers.delete( 'X-CSRF-TOKEN' ); }
        const options = { ...this.requestOptions, method, headers, signal, credentials: 'same-origin' };
        delete options.body;
        if ( method === 'GET' || method === 'HEAD' ) {
            const entries = this.payload instanceof FormData ? this.payload.entries() : Object.entries( this.payload );
            for ( const [key, value] of entries ) { this.appendQuery( url.searchParams, key, value ); }
            return { url, options };
        }
        if ( this.payload instanceof FormData ) {
            headers.delete( 'Content-Type' );
            options.body = this.payload;
        }else {
            headers.set( 'Content-Type', 'application/json' );
            options.body = JSON.stringify( this.payload );
        }
        return { url, options };
    }
    /** 校验 echoRequest 的响应状态。 */
    validateResponse( resource ) {
        if ( !resource || !['success', 'info', 'error', 'warning'].includes( resource.status ) ) {
            throw new Error( 'Invalid response' );
        }
    }
    /** 分发业务结果；消息状态只显示通知，不执行成功回调。 */
    async handleResponse( response, resource ) {
        if ( this.middlewareCallback ) {
            resource = await this.middlewareCallback( { ref: this, response, resource } );
            if ( resource === false ) { return; }
            this.validateResponse( resource );
        }
        if ( resource.status === 'success' ) {
            if ( this.successCallback ) { await this.successCallback( resource ); }
            return resource;
        }
        const messages = Array.isArray( resource.message ) ? resource.message : [resource.message];
        messages.forEach( ( message ) => {
            if ( typeof message === 'string' ) { unitToast( message, resource.status ); }
        } );
    }
    /**
     * 发送请求
     * HTTP 错误中的 echoRequest 响应仍按业务状态处理，其余异常显示失败通知。
     * @param {string} method 请求方法
     * @returns {Promise<Object|undefined>} 成功响应对象
     */
    async send( method = 'GET' ) {
        const controller = new AbortController();
        let timer;
        try {
            if ( this.loadingCallback ) { await this.loadingCallback( true ); }
            let response;
            let resource;
            try {
                const { url, options } = this.buildRequest( method.toUpperCase(), controller.signal );
                if ( this.timeoutMs > 0 ) {
                    timer = setTimeout( () => controller.abort(), this.timeoutMs );
                }
                response = await fetch( url, options );
                resource = await response.json();
                this.validateResponse( resource );
            }catch ( exception ) {
                unitToast( 'Request failed', 'error' );
                if ( this.errorCallback ) { await this.errorCallback( exception ); }
                return;
            }finally {
                clearTimeout( timer );
            }
            return await this.handleResponse( response, resource );
        }finally {
            if ( this.loadingCallback ) { await this.loadingCallback( false ); }
        }
    }
}


// 暴露给动态插件视图中的 Alpine 表达式使用。
window.unitToast = unitToast;
window.sendRequest = sendRequest;
