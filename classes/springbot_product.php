<?php

if ( ! class_exists( 'Springbot_Product' ) ) {

    class Springbot_Product {

        /**
         * Check that we are on the springbot product endpoint
         *
         * @param $permalink
         *
         * @return mixed
         */
        public function handle_product_endpoint( $permalink ) {
            $uri   = $_SERVER['REQUEST_URI'];
            $parts = parse_url( $uri );
            $path  = trim( str_replace( 'index.php', '', $parts['path'] ), '/' );

            if ( $path === 'springbot/main/product' ) {

                header( "Content-Type: application/json" );
                header( "X-Robots-Tag: noindex" );
                try {

                    if ( ! isset( $parts['query'] ) ) {
                        throw new Exception( 'query param not set' );
                    }

                    parse_str( $parts['query'], $queryParts );
                    if ( ! isset( $queryParts['product_id'] ) ) {
                        throw new Exception( 'product_id not set' );
                    }
                    if ( ! is_numeric( $queryParts['product_id'] ) ) {
                        throw new Exception( 'invalid product_id' );
                    }

                    $pf      = new WC_Product_Factory();
                    $product = $pf->get_product( $queryParts['product_id'] );

                    if ( ! is_object( $product ) || ! ( $product instanceof WC_Product ) ) {
                        throw new Exception( 'product not found' );
                    }

                    $wooChildren = $product->get_children();
                    $children    = array();

                    // we want to ensure that there is at least one child representing the product
                    if ( count( $wooChildren ) === 0 ) {
                        $children[] = array(
                            'name'       => $product->get_name(),
                            'product_id' => $product->get_id(),
                            'sku'        => $product->get_sku()
                        );
                    } else {
                        foreach ( $wooChildren as $wooChild ) {
                            $child      = $pf->get_product( $wooChild );
                            $children[] = array(
                                'name'       => $child->get_name(),
                                'product_id' => $child->get_id(),
                                'sku'        => $child->get_sku()
                            );
                        }
                    }

                    echo json_encode( array(
                        'product_id' => $product->get_id(),
                        'sku'        => $product->get_sku(),
                        'children'   => $children
                    ) );

                } catch ( Exception $e ) {
                    echo json_encode( array( 'successful' => false, 'message' => $e->getMessage() ) );
                }
                exit;
            }

            return $permalink;
        }


    }

}