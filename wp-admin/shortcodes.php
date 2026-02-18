<?php
/**
 * Shortcode Processor
 *
 * Processes shortcodes in content and replaces them with dynamic HTML.
 * Supported shortcodes:
 * - [menu id="1"]               - Render menu by ID
 * - [snippet id="portfolio"]    - Render snippet by slug
 * - [posts limit="3"]           - Render latest posts
 * - [posts limit="3" category="news"]
 */

function process_shortcodes($content, $pdo) {
    // 1. Process menu shortcodes: [menu id="1"]
    $content = preg_replace_callback(
        '/\[menu id="([^"]+)"\]/',
        function($matches) use ($pdo) {
            return render_menu_shortcode($matches[1], $pdo);
        },
        $content
    );

    // 2. Process snippet shortcodes: [snippet id="portfolio-grid"]
    $content = preg_replace_callback(
        '/\[snippet id="([^"]+)"\]/',
        function($matches) use ($pdo) {
            return render_snippet_shortcode($matches[1], $pdo);
        },
        $content
    );

    // 3. Process posts shortcodes: [posts limit="3"] or [posts limit="3" category="news"]
    $content = preg_replace_callback(
        '/\[posts([^\]]*)\]/',
        function($matches) use ($pdo) {
            return render_posts_shortcode($matches[1], $pdo);
        },
        $content
    );

    return $content;
}

/**
 * Render menu by ID
 */
function render_menu_shortcode($menu_id, $pdo) {
    try {
        // Check if menu_items table exists
        $tables = $pdo->query("SHOW TABLES LIKE 'menu_items'")->fetchAll();
        if (empty($tables)) {
            return '<!-- Menu system not available -->';
        }

        $stmt = $pdo->prepare("
            SELECT mi.* FROM menu_items mi
            WHERE mi.menu_id = ?
            ORDER BY mi.position ASC
        ");
        $stmt->execute([$menu_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) {
            return '<!-- Menu not found: ' . htmlspecialchars($menu_id) . ' -->';
        }

        $html  = '<nav class="dynamic-menu" data-menu-id="' . htmlspecialchars($menu_id) . '">';
        $html .= '<ul>';

        foreach ($items as $item) {
            $html .= '<li>';
            $html .= '<a href="' . htmlspecialchars($item['url'] ?? '#') . '">';
            $html .= htmlspecialchars($item['title'] ?? '');
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;

    } catch (Exception $e) {
        return '<!-- Error rendering menu: ' . htmlspecialchars($e->getMessage()) . ' -->';
    }
}

/**
 * Render snippet by slug
 */
function render_snippet_shortcode($slug, $pdo) {
    try {
        // Check if snippets table exists
        $tables = $pdo->query("SHOW TABLES LIKE 'snippets'")->fetchAll();
        if (empty($tables)) {
            return '<!-- Snippets system not available -->';
        }

        $stmt = $pdo->prepare("
            SELECT * FROM snippets
            WHERE slug = ? AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $snippet = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$snippet) {
            return '<!-- Snippet not found or inactive: ' . htmlspecialchars($slug) . ' -->';
        }

        $content = $snippet['content'];

        // Execute PHP snippets using output buffering
        if ($snippet['type'] === 'php') {
            ob_start();
            try {
                // Make $pdo available to snippet
                eval('?>' . $content);
            } catch (Throwable $e) {
                ob_end_clean();
                return '<!-- PHP snippet error: ' . htmlspecialchars($e->getMessage()) . ' -->';
            }
            $content = ob_get_clean();
        } elseif ($snippet['type'] === 'css') {
            $content = '<style>' . $content . '</style>';
        } elseif ($snippet['type'] === 'js') {
            $content = '<script>' . $content . '</script>';
        }

        return $content;

    } catch (Exception $e) {
        return '<!-- Error rendering snippet: ' . htmlspecialchars($e->getMessage()) . ' -->';
    }
}

/**
 * Render latest posts
 *
 * Attributes:
 * - limit: Number of posts (default: 3)
 * - category: Filter by category slug (optional)
 */
function render_posts_shortcode($attributes, $pdo) {
    $attrs    = shortcode_parse_atts($attributes);
    $limit    = intval($attrs['limit'] ?? 3);
    $category = $attrs['category'] ?? null;

    if ($limit < 1) $limit = 3;
    if ($limit > 50) $limit = 50;

    try {
        $sql    = "SELECT * FROM posts WHERE status = 'publish'";
        $params = [];

        if ($category) {
            $sql     .= " AND category = ?";
            $params[] = $category;
        }

        $sql     .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($posts)) {
            return '<!-- No posts found -->';
        }

        $html = '<div class="posts-grid shortcode-posts">';

        foreach ($posts as $post) {
            $excerpt = $post['excerpt'] ?? '';
            if (empty($excerpt) && !empty($post['content'])) {
                // Auto-generate excerpt from content
                $plainText = strip_tags($post['content']);
                $excerpt   = mb_substr($plainText, 0, 150) . (mb_strlen($plainText) > 150 ? '...' : '');
            }

            $featuredImg = '';
            if (!empty($post['featured_image'])) {
                $imgSrc      = htmlspecialchars($post['featured_image']);
                $featuredImg = '<img src="' . $imgSrc . '" alt="' . htmlspecialchars($post['title']) . '" class="post-thumbnail">';
            }

            $html .= '<article class="post-item">';
            if ($featuredImg) {
                $html .= '<a href="/read.php?id=' . $post['id'] . '" class="post-thumbnail-link">' . $featuredImg . '</a>';
            }
            $html .= '<div class="post-content">';
            $html .= '<h3><a href="/read.php?id=' . $post['id'] . '">' . htmlspecialchars($post['title']) . '</a></h3>';
            if ($excerpt) {
                $html .= '<p class="post-excerpt">' . htmlspecialchars($excerpt) . '</p>';
            }
            $html .= '<a href="/read.php?id=' . $post['id'] . '" class="read-more">Read More &rarr;</a>';
            $html .= '</div>';
            $html .= '</article>';
        }

        $html .= '</div>';

        return $html;

    } catch (Exception $e) {
        return '<!-- Error rendering posts: ' . htmlspecialchars($e->getMessage()) . ' -->';
    }
}

/**
 * Parse shortcode attributes
 *
 * Example: ' limit="3" category="news"' => ['limit' => '3', 'category' => 'news']
 */
function shortcode_parse_atts($text) {
    $atts    = [];
    $pattern = '/(\w+)="([^"]*)"/';

    if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $atts[$match[1]] = $match[2];
        }
    }

    return $atts;
}
