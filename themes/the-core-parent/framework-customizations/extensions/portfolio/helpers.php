<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}


if ( ! function_exists( 'fw_theme_ext_portfolio_get_gallery_images' ) ) :
	/**
	 * Get gallery images
	 *
	 * @param integer $post_id
	 */
	function fw_theme_ext_portfolio_get_gallery_images( $post_id = 0 ) {
		if ( 0 === $post_id && null === ( $post_id = get_the_ID() ) ) {
			return array();
		}

		$options = get_post_meta( $post_id, 'fw_options', true );

		return isset( $options['project-gallery'] ) ? $options['project-gallery'] : array();
	}
endif;


if ( ! function_exists( 'fw_theme_portfolio_post_taxonomies' ) ) :
	/**
	 * Return portfolio post taxonomies
	 *
	 * @param integer $post_id
	 * @param boolean $return
	 */
	function fw_theme_portfolio_post_taxonomies( $post_id, $return = false ) {

		$taxonomy = 'fw-portfolio-category';
		$terms    = wp_get_post_terms( $post_id, $taxonomy );
		$list     = '';
		$checked  = false;
		foreach ( $terms as $term ) {
			if ( $term->parent == 0 ) {
				// if is checked parent category
				$list .= $term->slug . ', ';
				$checked = true;
			} else {
				$list .= $term->slug . ', ';
				$parent_id = $term->parent;
			}
		}

		if ( ! $checked ) {
			// if is not checked parent category extract this parent
			if ( isset( $parent_id ) ) {
				$term = get_term_by( 'id', $parent_id, $taxonomy );
				$list .= $term->slug . ', ';
			}
		}

		if ( $return ) {
			return $list;
		} else {
			echo ($list);
		}
	}
endif;


if ( ! function_exists( 'fw_theme_portfolio_name_taxonomies' ) ) :
	/**
	 * Return portfolio post taxonomies names
	 *
	 * @param integer $post_id
	 * @param boolean $return
	 */
	function fw_theme_portfolio_name_taxonomies( $post_id, $return = false ) {
		$taxonomy  = 'fw-portfolio-category';
		$terms     = wp_get_post_terms( $post_id, $taxonomy );
		$separator = '<span class="portfolio-categories-sep">/</span>';
		foreach ( $terms as $term ) {
			$term_link = get_term_link( $term );
			$array[]   = '<a href="' . esc_url( $term_link ) . '">' . $term->name . '</a>';
		}
		$list = implode( $separator, $array );

		if ( $return ) {
			return $list;
		} else {
			echo ($list);
		}
	}
endif;


if ( ! function_exists( 'fw_theme_portfolio_filter' ) ) :
	/**
	 * Display portfolio filter
	 *
	 * @param boolean $filter_enabled
	 * @param string $uniqid
	 * @param boolean $isotope
	 */
	function fw_theme_portfolio_filter( $filter_enabled, $uniqid, $isotope = false, $posts = array() ) {
		if ( $filter_enabled == 'yes' ) {
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			if ( ! $term ) {
				return; // if term is false
			}
			$taxonomy = $term->taxonomy;
			$term_id  = $term->term_id;
			$children = get_term_children( $term_id, $taxonomy );
			if ( empty( $children ) && $isotope ) {
				return; // if current term hasn't children - don't show filter
			}
			$template_slug   = $term->slug;
			$template_parent = $term->parent;
			$args            = array( 'taxonomy' => $taxonomy );
			$terms           = get_terms( $taxonomy, $args );
			$the_core_count  = count( $terms );
			$i               = 0;

			if ( $template_parent == 0 ) {
				$template_parent = $term_id;
			}

			echo '<div class="fw-portfolio-filter">
                <ul id="fw-portfolio-filter-' . $uniqid . '" class="portfolio_filter" data-isotope="'.(int)$isotope.'" data-list-id="fw-portfolio-list-'.$uniqid.'">';
			if ( $the_core_count > 0 ) {
                $term_list = $term_view_all = '';
                $taxonomies_with_posts = the_core_return_taxonomies_with_posts( $posts, array( 'taxonomy' => $taxonomy ) );
				foreach ( $terms as $term ) {
					$i ++;
                    if( !empty($posts) && !in_array( $term->term_id, $taxonomies_with_posts ) ) {
                        // for exclude empty taxonomies from the filter
                        continue;
                    }

					if ( $template_parent != $term->parent ) {
						if ( $term->slug == $template_slug ) {
                            if( $isotope ) $the_core_permalink = '#';
							else $the_core_permalink = get_term_link( $term->slug, $taxonomy );
							$icon          = '';
							$category_icon = fw_get_db_term_option( $term->term_id, $taxonomy, 'category_icon', '' );
							if ( $category_icon != '' ) {
								$icon = '<i class="' . $category_icon . '"></i>';
							}
							$term_view_all .= '<li class="categories-item active" data-category="' . $template_slug . '"><a href="' . $the_core_permalink . '">' . $icon . the_core_translate($term->name) . '</a></li>';
						} elseif ( $template_parent == $term->term_id ) {
                            if( $isotope ) $the_core_permalink = '#';
							else $the_core_permalink = get_term_link( $term->slug, $taxonomy );
							$icon          = '';
							$category_icon = fw_get_db_term_option( $term->term_id, $taxonomy, 'category_icon', '' );
							if ( $category_icon != '' ) {
								$icon = '<i class="' . $category_icon . '"></i>';
							}
							$term_view_all .= '<li class="categories-item" data-category="' . $term->slug . '"><a href="' . $the_core_permalink . '">' . $icon . the_core_translate($term->name) . '</a></li>';
						}
					} elseif ( $template_parent == $term->parent ) {
						if ( $term->slug == $template_slug ) {
                            if( $isotope ) $the_core_permalink = '#';
							else $the_core_permalink = get_term_link( $term->slug, $taxonomy );
							$icon          = '';
							$category_icon = fw_get_db_term_option( $term->term_id, $taxonomy, 'category_icon', '' );
							if ( $category_icon != '' ) {
								$icon = '<i class="' . $category_icon . '"></i>';
							}
							$term_list .= '<li class="categories-item active" data-category="' . $template_slug . '"><a href="' . $the_core_permalink . '">' . $icon . the_core_translate($term->name) . '</a></li>';
						} else {
                            if( $isotope ) $the_core_permalink = '#';
							else $the_core_permalink = get_term_link( $term->slug, $taxonomy );
							$icon          = '';
							$category_icon = fw_get_db_term_option( $term->term_id, $taxonomy, 'category_icon', '' );
							if ( $category_icon != '' ) {
								$icon = '<i class="' . $category_icon . '"></i>';
							}
							$term_list .= '<li class="categories-item" data-category="' . $term->slug . '"><a href="' . $the_core_permalink . '">' . $icon . the_core_translate($term->name) . '</a></li>';
						}
					}
				}
				echo ($term_view_all . $term_list);
			}
			echo '</ul>
                <a class="prev" id="fw-portfolio-filter-' . esc_attr($uniqid) . '-prev" href="#"><i class="fa"></i></a>
                <a class="next" id="fw-portfolio-filter-' . esc_attr($uniqid) . '-next" href="#"><i class="fa"></i></a>
            </div>';
		}
	}
endif;