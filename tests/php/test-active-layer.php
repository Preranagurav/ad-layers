<?php

class Ad_Layers_Active_Layer_Tests extends Ad_Layers_UnitTestCase {
	protected $post_id, $page_id, $cat_id, $tag_id, $tax_id, $editor_id;

	public function setUp() {
		parent::setUp();

		register_taxonomy( 'test-taxonomy', 'post', array( 'label' => rand_str() ) );

		$this->editor_id = $this->factory->user->create( array( 'role' => 'editor' ) );

		$this->cat_id = $this->factory->term->create( array( 'taxonomy' => 'category', 'name' => 'cat-a' ) );
		$this->tag_id = $this->factory->term->create( array( 'taxonomy' => 'post_tag', 'name' => 'tag-b' ) );
		$this->tax_id = $this->factory->term->create( array( 'taxonomy' => 'test-taxonomy', 'name' => 'tax-c' ) );

		$this->post_id = $this->factory->post->create( array(
			'post_title' => 'hello-world',
			'post_author' => $this->editor_id,
			'post_category' => array( $this->cat_id ),
			'tax_input' => array( 'post_tag' => 'tag-b', 'test-taxonomy' => 'tax-c' ),
			'post_date' => '2015-03-14 12:34:56',
		) );
		$this->page_id = $this->factory->post->create( array( 'post_title' => 'hello-page', 'post_type' => 'page' ) );
	}

	public function tearDown() {
		self::delete_user( $this->editor_id );
		parent::tearDown();
	}

	protected function build_and_get_layer( $args ) {
		$layer = $this->factory->post->create( array( 'post_type' => 'ad-layer' ) );
		$args = wp_parse_args( $args, array(
			'post_types' => array(),
			'page_types' => array(),
			'taxonomies' => array(),
		) );
		add_post_meta( $layer, 'ad_layer_post_types', (array) $args['post_types'] );
		add_post_meta( $layer, 'ad_layer_page_types', (array) $args['page_types'] );
		add_post_meta( $layer, 'ad_layer_taxonomies', (array) $args['taxonomies'] );
		return $layer;
	}

	protected function get_active_ad_layer() {
		$layers = get_option( 'ad_layers', array() );
		usort( $layers, function( $a, $b ) {
			if ( $a['post_id'] == $b['post_id'] ) {
			    return 0;
			}
			return ( $a['post_id'] < $b['post_id'] ) ? 1 : -1;
		} );
		update_option( 'ad_layers', $layers );
		Ad_Layers::instance()->setup();
		Ad_Layers::instance()->set_active_ad_layer();
		$layer = Ad_Layers::instance()->ad_layer;
		return ( ! empty( $layer['post_id'] ) ? $layer['post_id'] : false );
	}

	public function test_active_layer_failed() {
		// Set this to something that won't match
		$layer = $this->build_and_get_layer( array( 'page_types' => 'search' ) );

		$this->go_to( '/' );
		$this->assertTrue( is_home() );
		$this->assertNotSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_home() {
		$layer = $this->build_and_get_layer( array( 'page_types' => 'home' ) );

		$this->go_to( '/' );
		$this->assertTrue( is_home() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_single_post() {
		$layer = $this->build_and_get_layer( array( 'page_types' => 'post' ) );

		$this->go_to( get_permalink( $this->post_id ) );
		$this->assertTrue( is_single() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_single_page() {
		$layer = $this->build_and_get_layer( array( 'page_types' => 'page' ) );

		$this->go_to( get_permalink( $this->page_id ) );
		$this->assertTrue( is_page() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_category() {
		$layer = $this->build_and_get_layer( array( 'page_types' => 'category' ) );

		$this->go_to( get_category_link( $this->cat_id ) );
		$this->assertTrue( is_category() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_tag() {
		$layer = $this->build_and_get_layer( array( 'page_types' => 'post_tag' ) );

		$this->go_to( get_tag_link( $this->tag_id ) );
		$this->assertTrue( is_tag() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_custom_taxonomy() {
		$layer = $this->build_and_get_layer( array( 'page_types' => 'test-taxonomy' ) );

		$this->go_to( get_term_link( $this->tax_id, 'test-taxonomy' ) );
		$this->assertTrue( is_tax() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_author() {
		$layer = $this->build_and_get_layer( array( 'page_types' => 'author' ) );

		$this->go_to( get_author_posts_url( $this->editor_id ) );
		$this->assertTrue( is_author() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_date() {
		$layer = $this->build_and_get_layer( array( 'page_types' => 'date' ) );

		$this->go_to( get_year_link( '2015' ) );
		$this->assertTrue( is_year() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );

		$this->go_to( get_month_link( '2015', '03' ) );
		$this->assertTrue( is_month() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );

		$this->go_to( get_day_link( '2015', '03', '14' ) );
		$this->assertTrue( is_day() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );

		$this->go_to( '/' );
		$this->assertNotSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_notfound() {
		$layer = $this->build_and_get_layer( array( 'page_types' => 'notfound' ) );

		$this->go_to( '?name=' . rand_str() );
		$this->assertTrue( is_404() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );

		$this->go_to( get_year_link( '2025' ) );
		$this->assertTrue( is_404() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );

		$this->go_to( '/' );
		$this->assertNotSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_search() {
		$layer = $this->build_and_get_layer( array( 'page_types' => 'search' ) );

		$this->go_to( '?s=hello' );
		$this->assertTrue( is_search() );
		$this->assertSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_cpt_without_archive() {
		$post_type = rand_str( 20 );
		register_post_type( $post_type, array( 'public' => true ) );
		$cpt_id = $this->factory->post->create( array( 'post_title' => 'hello-cpt', 'post_type' => $post_type, 'label' => rand_str() ) );
		$layer = $this->build_and_get_layer( array( 'page_types' => $post_type ) );

		$this->go_to( get_permalink( $cpt_id ) );
		$this->assertTrue( is_singular( $post_type ) );
		$this->assertSame( $layer, $this->get_active_ad_layer() );
	}

	public function test_active_layer_cpt_with_archive() {
		$post_type = rand_str( 20 );
		register_post_type( $post_type, array( 'public' => true, 'has_archive' => true ) );
		$cpt_id = $this->factory->post->create( array( 'post_title' => 'hello-cpt', 'post_type' => $post_type, 'label' => rand_str() ) );

		$layer_single = $this->build_and_get_layer( array( 'page_types' => $post_type ) );
		$layer_archive = $this->build_and_get_layer( array( 'page_types' => 'archive::' . $post_type ) );

		// Singular view
		$this->go_to( get_permalink( $cpt_id ) );
		$this->assertTrue( is_singular( $post_type ) );
		$this->assertSame( $layer_single, $this->get_active_ad_layer() );

		// Archive view
		$this->go_to( get_post_type_archive_link( $post_type ) );
		$this->assertTrue( is_post_type_archive( $post_type ) );
		$this->assertSame( $layer_archive, $this->get_active_ad_layer() );
	}
}
