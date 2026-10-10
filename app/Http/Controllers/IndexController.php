<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller {

    /**
     * 前端调试工具接口
     * @return mixed
     */
    public function debug( Request $request ): mixed {
        if ( !config( 'app.debug' ) ) { return echoJson( 2, ['base.error.prohibit'] ); }

        $plugin = plugin( 'Example' );

        print_r( $plugin ); exit();
    }
}