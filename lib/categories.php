<?php

/**
 * Find category name based on category id
 */
function flowplayer_ovp_find_category_name( $category_id, $categories ) {
	$mapped_categories = array_column( $categories, 'name', 'id' );
	return array_key_exists( $category_id, $mapped_categories ) ? $mapped_categories[ $category_id ] : '';
};

/**
 * Transfer flat category structure into nested one
 */
function flowplayer_ovp_nest_categories( $categories ) {
	$parents = flowplayer_ovp_children_of( $categories, '' );

	$categorytree = flowplayer_ovp_recurse_child_categories( $categories, $parents );

	$flattree = flowplayer_ovp_flatten_category_tree( $categorytree );

	return $flattree;
}

/**
 * Recursively add children to parent categories
 */
function flowplayer_ovp_recurse_child_categories( $categories, $parents ) {
	foreach ( $parents as $parent ) {
		$children = flowplayer_ovp_children_of( $categories, $parent->id );

		if ( count( $children ) ) {
			$children = flowplayer_ovp_recurse_child_categories( $categories, $children );

			$parent->children = $children;
		}
	}

	return $parents;
}

/**
 * Pick categories that are children of given parentid
 */
function flowplayer_ovp_children_of( $categories, $parentid ) {
	return array_filter(
		$categories,
		function( $cat ) use ( $parentid ) {
			return $cat->parentid == $parentid;
		}
	);
}

/**
 * Sort category objects by name
 */
function flowplayer_ovp_category_name_sort( $a, $b ) {
	return $a->name > $b->name;
}

/**
 * Unstructure categories from tree
 */
function flowplayer_ovp_flatten_category_tree( $parents, $level = 0 ) {
	$flattened = [];
	foreach ( $parents as $parent ) {
		$parent->concatid = $parent->id;

		$parent->name = str_repeat( '- ', $level ) . $parent->name;

		if ( isset( $parent->children ) ) {
			$children = flowplayer_ovp_flatten_category_tree( $parent->children, $level + 1 );
			foreach ( $children as $child ) {
				$parent->concatid .= ',' . $child->id;
			}
			unset( $parent->children );
		} else {
			$children = [];
		}

		$flattened[] = $parent;
		$flattened   = array_merge( $flattened, $children );
	}
	return $flattened;
}
