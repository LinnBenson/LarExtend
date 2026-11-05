/**
 * 创建 WebGL 能量隧道
 * 使用极坐标程序纹理绘制旋涡与光束，不支持 WebGL 时保留 CSS 和粒子场景。
 * @param {HTMLCanvasElement} canvas 背景画布
 * @returns {{resize: (width: number, height: number) => void, render: (time: number, pointer: {x: number, y: number}) => void, setColor: (color: number[]) => void, destroy: () => void}|null} 渲染器
 */
function createEnergyField( canvas ) {
    const gl = canvas.getContext( 'webgl', { alpha: true, antialias: false, depth: false, premultipliedAlpha: false, powerPreference: 'low-power' } );
    if ( !gl ) { return null; }
    const vertexSource = 'attribute vec2 position; void main() { gl_Position = vec4(position, 0.0, 1.0); }';
    const fragmentSource = `
        precision mediump float;
        uniform vec2 resolution;
        uniform vec2 pointer;
        uniform float time;
        uniform vec3 accent;
        void main() {
            vec2 uv = (gl_FragCoord.xy * 2.0 - resolution) / resolution.y;
            uv -= pointer * 0.12;
            float r = max(length(uv), 0.06);
            float a = atan(uv.y, uv.x);
            float t = time * 0.55;
            float twist = a + 0.8 * log(r + 0.1) - t * 0.2;
            float turbulence = sin(a * 5.0 + t) * 0.04 + sin(a * 11.0 - t * 1.4) * 0.02;
            float corona = 0.012 / (abs(r - 0.76 - turbulence) + 0.012);
            float filament = pow(0.5 + 0.5 * sin(twist * 12.0 + sin(r * 8.0 - t) * 2.0), 10.0);
            float rays = pow(0.5 + 0.5 * sin(a * 34.0 + sin(a * 7.0 + t) * 2.0 + t * 0.4), 22.0);
            float depth = 1.0 / (r + 0.2);
            float tunnel = pow(0.5 + 0.5 * sin(depth * 15.0 - t * 7.0 + a * 3.0), 16.0);
            float stream = filament * (0.3 + tunnel) * 0.65;
            float halo = exp(-abs(r - 0.8) * 3.0);
            float light = corona * 0.75 + stream * halo + rays * halo * 0.45 + tunnel * halo * 0.12;
            vec3 secondary = mix(accent.brg, vec3(0.6, 0.8, 1.0), 0.2);
            vec3 color = mix(accent, secondary, 0.5 + 0.5 * sin(twist * 2.0 + t));
            color = mix(color, vec3(1.0), min(corona * 0.5, 0.6));
            float fade = smoothstep(0.15, 0.5, r) * (1.0 - smoothstep(1.5, 2.8, r));
            gl_FragColor = vec4(color, clamp(light * fade, 0.0, 0.85));
        }
    `;
    const shaders = [];
    for ( const [type, source] of [[gl.VERTEX_SHADER, vertexSource], [gl.FRAGMENT_SHADER, fragmentSource]] ) {
        const shader = gl.createShader( type );
        if ( !shader ) { shaders.forEach( item => gl.deleteShader( item ) ); return null; }
        gl.shaderSource( shader, source );
        gl.compileShader( shader );
        shaders.push( shader );
        if ( !gl.getShaderParameter( shader, gl.COMPILE_STATUS ) ) {
            shaders.forEach( item => gl.deleteShader( item ) );
            return null;
        }
    }
    const program = gl.createProgram();
    if ( !program ) { shaders.forEach( item => gl.deleteShader( item ) ); return null; }
    shaders.forEach( shader => gl.attachShader( program, shader ) );
    gl.linkProgram( program );
    shaders.forEach( shader => gl.deleteShader( shader ) );
    if ( !gl.getProgramParameter( program, gl.LINK_STATUS ) ) { gl.deleteProgram( program ); return null; }
    const buffer = gl.createBuffer();
    if ( !buffer ) { gl.deleteProgram( program ); return null; }
    gl.bindBuffer( gl.ARRAY_BUFFER, buffer );
    gl.bufferData( gl.ARRAY_BUFFER, new Float32Array( [-1, -1, 1, -1, -1, 1, -1, 1, 1, -1, 1, 1] ), gl.STATIC_DRAW );
    gl.useProgram( program );
    const position = gl.getAttribLocation( program, 'position' );
    gl.enableVertexAttribArray( position );
    gl.vertexAttribPointer( position, 2, gl.FLOAT, false, 0, 0 );
    const uniforms = Object.fromEntries( ['resolution', 'pointer', 'time', 'accent'].map( name => [name, gl.getUniformLocation( program, name )] ) );
    let lost = false;
    function onContextLost( event ) { event.preventDefault(); lost = true; canvas.style.opacity = '0'; }
    canvas.addEventListener( 'webglcontextlost', onContextLost );
    return {
        resize( width, height ) {
            const scale = width < 600 ? 0.65 : 0.8;
            canvas.width = Math.round( width * scale );
            canvas.height = Math.round( height * scale );
            gl.viewport( 0, 0, canvas.width, canvas.height );
        },
        setColor( color ) { if ( !lost ) { gl.uniform3fv( uniforms.accent, color ); } },
        render( time, pointer ) {
            if ( lost ) { return; }
            gl.uniform2f( uniforms.resolution, canvas.width, canvas.height );
            gl.uniform2f( uniforms.pointer, pointer.x, -pointer.y );
            gl.uniform1f( uniforms.time, time );
            gl.drawArrays( gl.TRIANGLES, 0, 6 );
        },
        destroy() {
            canvas.removeEventListener( 'webglcontextlost', onContextLost );
            gl.deleteBuffer( buffer );
            gl.deleteProgram( program );
            gl.getExtension( 'WEBGL_lose_context' )?.loseContext();
        }
    };
}

/**
 * 挂载登录页能量场
 * 绘制纵深星流与鼠标尾迹，并随页面生命周期释放渲染资源。
 * @param {HTMLElement} root 登录页面容器
 * @returns {() => void} 场景清理函数
 */
export function mountScene( root ) {
    const canvas = root.querySelector( '.login-v4-particles' );
    const context = canvas.getContext( '2d' );
    if ( !context ) { return () => {}; }

    const scene = root.querySelector( '.login-v4-scene' );
    const energy = createEnergyField( root.querySelector( '.login-v4-energy' ) );
    root.dataset.renderer = energy ? 'webgl' : 'canvas';
    energy?.setColor( [96 / 255, 165 / 255, 250 / 255] );
    const preference = window.matchMedia( '(prefers-reduced-motion: reduce)' );
    const finePointer = window.matchMedia( '(pointer: fine)' );
    const pointer = { x: 0, y: 0 };
    const trail = [];
    let stars = [];
    let width = 0;
    let height = 0;
    let frame = 0;
    let previousTime = 0;
    let disposed = false;
    const color = '#60a5fa';
    let elapsed = 0;
    let lastEnergyFrame = 0;
    let inputTimer = 0;
    const entranceTimer = window.setTimeout( () => { root.dataset.entered = 'true'; }, 1500 );

    const enabled = () => !preference.matches && !document.hidden;

    /**
     * 生成星点
     * 深度用于投影与飞行速度，起始深度均匀分布以避免集中出现。
     * @param {boolean} distant 是否从远处生成
     * @returns {{x: number, y: number, z: number}} 星点坐标
     */
    function createStar( distant = false ) {
        return { x: ( Math.random() - 0.5 ) * width * 3, y: ( Math.random() - 0.5 ) * height * 3, z: distant ? 1400 : 80 + Math.random() * 1320 };
    }

    /**
     * 绘制场景
     * 使用时间差控制速度，限制尾迹数量与帧间步长。
     * @param {number} time 当前帧时间
     * @returns {void}
     */
    function draw( time ) {
        frame = 0;
        if ( disposed ) { return; }
        const moving = enabled();
        const delta = moving && previousTime ? Math.min( ( time - previousTime ) / 16.67, 2 ) : 0;
        previousTime = time;
        elapsed += delta / 60;
        if ( time - lastEnergyFrame > 32 || !moving ) {
            energy?.render( elapsed, pointer );
            lastEnergyFrame = time;
        }
        context.clearRect( 0, 0, width, height );
        context.strokeStyle = color;
        context.fillStyle = color;
        const centerX = width / 2 + pointer.x * 22;
        const centerY = height * 0.47 + pointer.y * 16;
        const lens = Math.min( width, height ) * 0.7;

        for ( let index = 0; index < stars.length; index++ ) {
            let star = stars[index];
            const oldDepth = star.z;
            star.z -= delta * ( width < 600 ? 7 : 12 );
            const x = centerX + star.x * lens / star.z;
            const y = centerY + star.y * lens / star.z;
            if ( star.z < 50 || x < -50 || x > width + 50 || y < -50 || y > height + 50 ) {
                stars[index] = createStar( true );
                continue;
            }
            context.globalAlpha = Math.max( 0.08, ( 1 - star.z / 1500 ) * 0.95 );
            context.lineWidth = Math.max( 0.6, 1.7 - star.z / 1000 );
            context.beginPath();
            context.moveTo( centerX + star.x * lens / ( oldDepth + 45 ), centerY + star.y * lens / ( oldDepth + 45 ) );
            context.lineTo( x, y );
            context.stroke();
        }

        for ( let index = trail.length - 1; index >= 0; index-- ) {
            const particle = trail[index];
            particle.life -= delta * 0.025;
            if ( particle.life <= 0 ) { trail.splice( index, 1 ); continue; }
            particle.x += particle.vx * delta;
            particle.y += particle.vy * delta;
            context.globalAlpha = particle.life * 0.6;
            context.beginPath();
            context.arc( particle.x, particle.y, particle.life * 2, 0, Math.PI * 2 );
            context.fill();
            const next = trail[index - 1];
            if ( next && Math.hypot( next.x - particle.x, next.y - particle.y ) < 75 ) {
                context.globalAlpha = particle.life * 0.2;
                context.beginPath();
                context.moveTo( particle.x, particle.y );
                context.lineTo( next.x, next.y );
                context.stroke();
            }
        }
        context.globalAlpha = 1;
        if ( moving ) { frame = requestAnimationFrame( draw ); }
    }

    function restart() {
        cancelAnimationFrame( frame );
        previousTime = 0;
        if ( !enabled() ) {
            root.dataset.entered = 'true';
            pointer.x = 0;
            pointer.y = 0;
            trail.length = 0;
            root.style.setProperty( '--login-v4-pointer-x', '0px' );
            root.style.setProperty( '--login-v4-pointer-y', '0px' );
        }
        if ( !document.hidden && !disposed ) { frame = requestAnimationFrame( draw ); }
    }

    function resize() {
        const rect = scene.getBoundingClientRect();
        width = rect.width;
        height = rect.height;
        const ratio = Math.min( window.devicePixelRatio || 1, 1.5 );
        canvas.width = Math.round( width * ratio );
        canvas.height = Math.round( height * ratio );
        context.setTransform( ratio, 0, 0, ratio, 0, 0 );
        energy?.resize( width, height );
        stars = Array.from( { length: width < 600 ? 130 : 420 }, () => createStar() );
        restart();
    }

    function movePointer( event ) {
        if ( !enabled() || !finePointer.matches || event.pointerType === 'touch' ) { return; }
        const rect = root.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        pointer.x = x / width - 0.5;
        pointer.y = y / height - 0.5;
        root.style.setProperty( '--login-v4-pointer-x', `${pointer.x * 55}px` );
        root.style.setProperty( '--login-v4-pointer-y', `${pointer.y * 40}px` );
        if ( trail.length < 110 ) {
            trail.push( { x, y, vx: ( Math.random() - 0.5 ) * 1.5, vy: -0.5 - Math.random(), life: 1 } );
        }
    }

    function leavePointer() {
        pointer.x = 0;
        pointer.y = 0;
        root.style.setProperty( '--login-v4-pointer-x', '0px' );
        root.style.setProperty( '--login-v4-pointer-y', '0px' );
    }

    function onInput( event ) {
        if ( !enabled() || !event.target.matches( 'input' ) ) { return; }
        root.dataset.typing = 'on';
        clearTimeout( inputTimer );
        inputTimer = window.setTimeout( () => { delete root.dataset.typing; }, 450 );
    }

    const sizeObserver = new ResizeObserver( resize );
    sizeObserver.observe( scene );
    preference.addEventListener( 'change', restart );
    document.addEventListener( 'visibilitychange', restart );
    root.addEventListener( 'pointermove', movePointer, { passive: true } );
    root.addEventListener( 'pointerleave', leavePointer );
    root.addEventListener( 'input', onInput );
    resize();

    return () => {
        disposed = true;
        cancelAnimationFrame( frame );
        clearTimeout( inputTimer );
        clearTimeout( entranceTimer );
        energy?.destroy();
        sizeObserver.disconnect();
        preference.removeEventListener( 'change', restart );
        document.removeEventListener( 'visibilitychange', restart );
        root.removeEventListener( 'pointermove', movePointer );
        root.removeEventListener( 'pointerleave', leavePointer );
        root.removeEventListener( 'input', onInput );
    };
}
