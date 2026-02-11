<?php

use function Breakdance\Util\WP\isAnyArchive;
use function Breakdance\WpQueryControl\getWpQueryArgumentsFromWpQueryControlProperties;

// -------------------------
// AJAX Request Handling
// -------------------------
if ( isset( $_GET['ajax_search'] ) && '1' === $_GET['ajax_search'] ) {

    // Ensure $propertiesData exists.
    if ( ! isset( $propertiesData ) || ! is_array( $propertiesData ) ) {
        $propertiesData = [];
    }
    $contentProps = $propertiesData['content'] ?? [];

    // Current page number.
    $paged = absint( get_query_var( 'paged' ) ) ?: 1;

    // Excerpt length (default 20).
    $excerptLength = ( isset( $contentProps['layout']['excerpt_length'] ) && intval( $contentProps['layout']['excerpt_length'] ) > 0 )
        ? intval( $contentProps['layout']['excerpt_length'] )
        : 20;

    // Determine whether to show all results.
    $show_all = ( isset( $_GET['show_all'] ) && '1' === $_GET['show_all'] );

    // Get search query and sanitise.
    $search_query = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';

    // Get selected category (if provided) and sanitise.
    $selected_cat = isset( $_GET['cat'] ) && '' !== $_GET['cat']
        ? sanitize_text_field( $_GET['cat'] )
        : '';

    // Build a unique cache key from all variables that affect the output.
    $cache_key = 'ajax_search_' . md5( $search_query . $selected_cat . $paged . $show_all );
    $cached_response = wp_cache_get( $cache_key, 'ajax_search' );
    if ( false !== $cached_response ) {
        header( 'Content-Type: application/json' );
        echo json_encode( $cached_response );
        exit;
    }

    // Build base query arguments using the query builder.
    $argsFromQuery = getWpQueryArgumentsFromWpQueryControlProperties(
        $contentProps['query']['query'] ?? [],
        [ 'paged' => $paged ]
    );

    // Explicitly override any pagination settings.
    $default_max = isset( $contentProps['options']['max_number_of_results_to_show'] )
        ? intval( $contentProps['options']['max_number_of_results_to_show'] )
        : 5;

    $argsFromQuery['posts_per_page'] = $show_all ? 9999 : $default_max;
    $argsFromQuery['paged'] = $paged;
    $argsFromQuery['nopaging'] = false;

    // Add search term and other query settings.
    $argsFromQuery['s']         = $search_query;
    $argsFromQuery['post_status'] = 'publish';
	
	// ─── Exclude “Hidden” products from AJAX search ───
if (
    isset( $argsFromQuery['post_type'] )
    && (
        'product' === $argsFromQuery['post_type']
        || (
            is_array( $argsFromQuery['post_type'] )
            && in_array( 'product', $argsFromQuery['post_type'], true )
        )
    )
) {
    // Ensure tax_query exists
    if ( ! isset( $argsFromQuery['tax_query'] ) || ! is_array( $argsFromQuery['tax_query'] ) ) {
        $argsFromQuery['tax_query'] = [];
    }

    // Exclude products with Catalogue visibility = Hidden
    $argsFromQuery['tax_query'][] = [
        'taxonomy' => 'product_visibility',
        'field'    => 'slug',
        'terms'    => [ 'exclude-from-search' ],
        'operator' => 'NOT IN',
    ];
}
// ────────────────────────────────────────────────


    // If a category is provided in the expected "taxonomy:term_id" format, add a tax_query.
    if ( $selected_cat ) {
        $parts = explode( ':', $selected_cat );
        if ( 2 === count( $parts ) ) {
            $argsFromQuery['tax_query'] = [
                [
                    'taxonomy' => $parts[0],
                    'field'    => 'term_id',
                    'terms'    => absint( $parts[1] ),
                ],
            ];
        }
    }

    // Create the WP_Query object.
    $loop = new WP_Query( $argsFromQuery );

    // If Relevanssi is active, add a filter to re‑apply custom query parts.
    if ( function_exists( 'relevanssi_do_query' ) ) {
        add_filter( 'relevanssi_modify_wp_query', 'my_relevanssi_modify_query' );
        // Let Relevanssi process the query.
        relevanssi_do_query( $loop );
    }

    $results = [];
    if ( $loop->have_posts() ) {
        while ( $loop->have_posts() ) {
            $loop->the_post();
            $results[] = [
                'title'          => get_the_title(),
                'permalink'      => get_permalink(),
                'excerpt'        => wp_trim_words( get_the_excerpt(), $excerptLength, '...' ),
                'featured_image' => has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : '',
            ];
        }
    }
    wp_reset_postdata();

    // Flag indicating if there are additional pages.
    $has_more = ( ! $show_all && ( $loop->max_num_pages > $paged ) );
    $response = [
        'results'  => $results,
        'has_more' => $has_more,
    ];

    // Cache the response for 10 minutes.
    wp_cache_set( $cache_key, $response, 'ajax_search', 600 );

    header( 'Content-Type: application/json' );
    echo json_encode( $response );
    exit;
}

/**
 * Filter to modify the WP_Query before Relevanssi processes it.
 * This re‑applies custom conditions (such as tax_query) that might otherwise be dropped.
 */
if ( ! function_exists( 'my_relevanssi_modify_query' ) ) {
    function my_relevanssi_modify_query( $query ) {
        // Check if a category was passed.
        if ( isset( $_GET['cat'] ) && '' !== $_GET['cat'] ) {
            $selected_cat = sanitize_text_field( $_GET['cat'] );
            $parts = explode( ':', $selected_cat );
            if ( count( $parts ) === 2 ) {
                $query->set( 'tax_query', array(
                    array(
                        'taxonomy' => $parts[0],
                        'field'    => 'term_id',
                        'terms'    => absint( $parts[1] ),
                    ),
                ) );
            }
        }
        return $query;
    }
}


// -------------------------
// Non-AJAX: Render the Search Form Page
// -------------------------

// Ensure $propertiesData exists.
if ( ! isset( $propertiesData ) || ! is_array( $propertiesData ) ) {
    $propertiesData = [];
}
$contentProps = $propertiesData['content'] ?? [];



// Retrieve query builder arguments (without pagination) to determine the post type.
$argsForPostType = getWpQueryArgumentsFromWpQueryControlProperties(
    $contentProps['query']['query'] ?? [],
    []
);

// Determine the post type (default 'post').
$post_type = $argsForPostType['post_type'] ?? 'post';

// Retrieve categories based on whether multiple post types are queried.
if ( is_array( $post_type ) ) {
    // For multiple post types, merge categories from each post type's hierarchical taxonomies.
    $all_categories = [];
    foreach ( $post_type as $pt ) {
        $taxonomies = get_object_taxonomies( $pt, 'objects' );
        foreach ( $taxonomies as $tax ) {
            if ( $tax->hierarchical ) {
                $terms = get_categories( [
                    'taxonomy'   => $tax->name,
                    'hide_empty' => true,
                ] );
                foreach ( $terms as $term ) {
                    // Use a key that includes taxonomy to avoid duplicates.
                    $all_categories[ $term->term_id . ':' . $tax->name ] = $term;
                }
            }
        }
    }
    // Convert associative array to an indexed array and sort by name.
    $categories = array_values( $all_categories );
    if ( ! empty( $categories ) ) {
        usort( $categories, function ( $a, $b ) {
            return strcasecmp( $a->name, $b->name );
        } );
    }
    $display_label = 'Categories';
} else {
    // Single post type: retrieve its first hierarchical taxonomy.
    $taxonomies = get_object_taxonomies( $post_type, 'objects' );
    $hierarchical_taxs = array_filter( $taxonomies, function ( $tax ) {
        return $tax->hierarchical;
    } );
    if ( ! empty( $hierarchical_taxs ) ) {
        $first_tax     = reset( $hierarchical_taxs );
        $taxonomy_slug = $first_tax->name;
        $display_label = $first_tax->label;
    } else {
        $taxonomy_slug = 'category';
        $display_label = 'Categories';
    }
    $categories = get_categories( [
        'taxonomy'   => $taxonomy_slug,
        'hide_empty' => true,
    ] );
    // Set each option's value as "taxonomy:term_id".
    foreach ( $categories as &$term ) {
        $term->option_value = $taxonomy_slug . ':' . $term->term_id;
    }
    unset( $term );
}

// Dynamic texts from options.
$placeholder         = ! empty( $contentProps['options']['placeholder'] ) ? $contentProps['options']['placeholder'] : 'Search…';
$loading_text        = ! empty( $contentProps['options']['loading_text'] ) ? $contentProps['options']['loading_text'] : 'Loading…';
$all_categories_text = ! empty( $contentProps['options']['all_categories_text'] ) ? $contentProps['options']['all_categories_text'] : 'All Categories';
// Add dynamic text for the Show All Results button.
$show_all_results_text = ! empty( $contentProps['options']['show_all_results_text'] ) ? $contentProps['options']['show_all_results_text'] : 'Show All Results';

// Container class to control category dropdown visibility.
$showCategories = ! empty( $contentProps['options']['show_categories'] );
$containerClass = $showCategories ? 'show-categories' : 'hide-categories';

// Determine the AJAX endpoint URL for this file.
$base_url = strtok( $_SERVER['REQUEST_URI'], '?' );
$sep      = ( strpos( $_SERVER['REQUEST_URI'], '?' ) === false ) ? '?' : '&';
$ajax_url = $base_url . $sep . 'ajax_search=1';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<body <?php body_class(); ?>>
    <div class="ajax-search-bar <?php echo esc_attr( $containerClass ); ?>">
        <!-- Category Dropdown (its visibility is controlled via CSS) -->
        <select id="ajax-search-category">
            <option value=""><?php echo esc_html( $all_categories_text ); ?></option>
            <?php foreach ( $categories as $term ) : 
                $option_val = isset( $term->option_value ) ? $term->option_value : $term->taxonomy . ':' . $term->term_id;
            ?>
                <option value="<?php echo esc_attr( $option_val ); ?>">
                    <?php echo esc_html( $term->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <!-- Search Input with dynamic placeholder -->
        <input type="text" id="ajax-search-input" placeholder="<?php echo esc_attr( $placeholder ); ?>">
        <!-- Results container -->
        <div id="ajax-search-results"></div>
    </div>
    
   <script type="text/javascript">
document.addEventListener("DOMContentLoaded", function () {
    var searchInput = document.getElementById("ajax-search-input");
    var categorySelect = document.getElementById("ajax-search-category");
    var resultsContainer = document.getElementById("ajax-search-results");
    var timeout = null;
    var loadingText = "<?php echo esc_js( $loading_text ); ?>";
    // Flag to indicate whether to show all results.
    var showAllResults = false;
    // Dynamic text for the "Show All Results" button.
    var showAllResultsText = "<?php echo esc_js( $show_all_results_text ); ?>";
    
    function doSearch() {
        var query = searchInput.value;
        var cat = categorySelect.value;
        clearTimeout(timeout);
        if (query.length < 3) {
            resultsContainer.innerHTML = "";
            resultsContainer.classList.remove("has-results");
            return;
        }
        // Show loading indicator.
        resultsContainer.innerHTML = "<div class='loading'>" + loadingText + "</div>";
        resultsContainer.classList.remove("has-results");
        timeout = setTimeout(function () {
            var xhr = new XMLHttpRequest();
            var url = "<?php echo esc_js( $ajax_url ); ?>" + "&q=" + encodeURIComponent(query);
            if (cat !== "") {
                url += "&cat=" + encodeURIComponent(cat);
            }
            if (showAllResults) {
                url += "&show_all=1";
            }
            xhr.open("GET", url, true); 
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        renderResults(data);
                    } catch (e) {
                        resultsContainer.innerHTML = "<p>Error parsing results.</p>";
                        resultsContainer.classList.remove("has-results");
                    }
                }
            };
            xhr.send();
        }, 300);
    }

    searchInput.addEventListener("keyup", doSearch);
    categorySelect.addEventListener("change", doSearch);

   function renderResults(data) {
    var results = data.results;
    var has_more = data.has_more;
    if (results.length === 0) {
        resultsContainer.innerHTML = "<p>No results found.</p>";
        resultsContainer.classList.remove("has-results");
        return;
    }
    var html = "<ul>";
    for (var i = 0; i < results.length; i++) {
        html += '<li>';
        html += '<a href="' + results[i].permalink + '" style="display:flex; align-items:flex-start; gap:10px; text-decoration:none; color:inherit;">';
        if (results[i].featured_image) {
            html += '<img class="stormasimage" src="' + results[i].featured_image + '" alt="' + results[i].title + '">';
        }
        html += '<div>';
        html += '<span style="text-decoration:none;">' + results[i].title + '</span>';
        html += '<p class="stormasexcerpt">' + results[i].excerpt + '</p>';
        html += '</div>';
        html += '</a>';
        html += '</li>';
    }
    html += "</ul>";
    // If there are more results, add a dynamic "Show All Results" button.
    if (has_more) {
        html += '<button id="show-all-results" style="margin-top:10px; padding:10px 15px; font-size:1rem;">' + showAllResultsText + '</button>';
    }
    resultsContainer.innerHTML = html;
    resultsContainer.classList.add("has-results");
    
    var btn = document.getElementById("show-all-results");
    if (btn) {
        btn.addEventListener("click", function () {
            showAllResults = true;
            doSearch();
        });
    }
}
    
    // Hide search results when clicking outside of the search bar or results area.
    document.addEventListener("click", function(event) {
        var searchBar = document.querySelector(".ajax-search-bar");
        if (searchBar && !searchBar.contains(event.target)) {
            resultsContainer.innerHTML = "";
            resultsContainer.classList.remove("has-results");
        }
    });
});
</script>

</body>
</html>