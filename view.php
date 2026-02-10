<?php
// Include config
if (file_exists(__DIR__ . '/wp-admin/db_config.php')) {
    require_once __DIR__ . '/wp-admin/db_config.php';
} else {
    require_once 'db_config.php';
}

// Get slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    die("Page not found");
}

// Get page data
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'publish'");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    die("Page not found or not published");
}

// Custom Tracking Logic
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$today = date('Y-m-d');

// 1. Increment Page Views
$stmt = $pdo->prepare("UPDATE pages SET views = views + 1 WHERE id = ?");
$stmt->execute([$page['id']]);

// 2. Increment Daily Visitors/Views
// Check if entry exists for today
$stmt = $pdo->prepare("SELECT * FROM daily_visitors WHERE visit_date = ?");
$stmt->execute([$today]);
if ($stmt->fetch()) {
    $pdo->prepare("UPDATE daily_visitors SET page_views = page_views + 1 WHERE visit_date = ?")->execute([$today]);
} else {
    $pdo->prepare("INSERT INTO daily_visitors (visit_date, visitor_count, page_views) VALUES (?, 1, 1)")->execute([$today]);
}

// Simple unique visitor tracking (Session based)
if (!isset($_SESSION['visited_today'])) {
    $_SESSION['visited_today'] = true;
    $pdo->prepare("UPDATE daily_visitors SET visitor_count = visitor_count + 1 WHERE visit_date = ?")->execute([$today]);
}

// Parse content
$content = json_decode($page['content'], true);
$html = '';
$css = '';

if ($page['builder_type'] === 'grapesjs' && isset($content['pages'])) {
    // GrapesJS content
    foreach ($content['pages'] as $pageContent) {
        $html .= $pageContent['html'] ?? '';
        $css .= $pageContent['css'] ?? '';
    }
} elseif ($page['builder_type'] === 'grapesjs' && isset($content['grapesjs']['html'])) {
    // Fallback for pages saved without 'pages' array
    $html = $content['grapesjs']['html'] ?? '';
    $css = $content['grapesjs']['css'] ?? '';
} elseif ($page['builder_type'] === 'editorjs' && isset($content['editorjs'])) {
    // EditorJS content - convert to HTML
    $html = convertEditorJSToHTML($content['editorjs']);
}

function convertEditorJSToHTML($editorData) {
    if (!isset($editorData['blocks']) || !is_array($editorData['blocks'])) {
        return '';
    }
    
    $html = '';
    
    foreach ($editorData['blocks'] as $block) {
        $type = $block['type'] ?? '';
        $data = $block['data'] ?? [];
        
        switch ($type) {
            case 'header':
                $level = $data['level'] ?? 2;
                $text = $data['text'] ?? '';
                $html .= "<h{$level}>{$text}</h{$level}>";
                break;
                
            case 'paragraph':
                $text = $data['text'] ?? '';
                $html .= "<p>{$text}</p>";
                break;
                
            case 'list':
                $style = $data['style'] ?? 'unordered';
                $items = $data['items'] ?? [];
                $tag = $style === 'ordered' ? 'ol' : 'ul';
                $html .= "<{$tag}>";
                foreach ($items as $item) {
                    if (is_array($item)) {
                        $content = $item['content'] ?? $item['text'] ?? '';
                        $subItems = $item['items'] ?? [];
                        $html .= "<li>{$content}";
                        
                        if (!empty($subItems)) {
                            $html .= "<{$tag}>";
                            foreach ($subItems as $subItem) {
                                $subContent = is_array($subItem) ? ($subItem['content'] ?? $subItem['text'] ?? '') : $subItem;
                                $html .= "<li>{$subContent}</li>";
                            }
                            $html .= "</{$tag}>";
                        }
                        
                        $html .= "</li>";
                    } else {
                        $html .= "<li>{$item}</li>";
                    }
                }
                $html .= "</{$tag}>";
                break;
                
            case 'quote':
                $text = $data['text'] ?? '';
                $caption = $data['caption'] ?? '';
                $html .= "<blockquote>";
                $html .= "<p>{$text}</p>";
                if ($caption) {
                    $html .= "<cite>{$caption}</cite>";
                }
                $html .= "</blockquote>";
                break;
                
            case 'code':
                $code = $data['code'] ?? '';
                $html .= "<pre><code>" . htmlspecialchars($code) . "</code></pre>";
                break;
                
            case 'table':
                $content = $data['content'] ?? [];
                $html .= "<table>";
                foreach ($content as $row) {
                    $html .= "<tr>";
                    foreach ($row as $cell) {
                        $html .= "<td>{$cell}</td>";
                    }
                    $html .= "</tr>";
                }
                $html .= "</table>";
                break;
                
            case 'image':
                // Support both @editorjs/image (file.url) and @editorjs/simple-image (url)
                $url = '';
                if (isset($data['file']['url'])) {
                    $url = $data['file']['url'];
                } elseif (isset($data['url'])) {
                    $url = $data['url'];
                }
                $caption = $data['caption'] ?? '';
                $classes = [];
                if (!empty($data['withBorder'])) $classes[] = 'img-border';
                if (!empty($data['stretched'])) $classes[] = 'img-stretched';
                if (!empty($data['withBackground'])) $classes[] = 'img-bg';
                $classStr = $classes ? ' class="' . implode(' ', $classes) . '"' : '';
                $html .= "<figure{$classStr}>";
                $html .= "<img src='" . htmlspecialchars($url) . "' alt='" . htmlspecialchars($caption) . "'>";
                if ($caption) {
                    $html .= "<figcaption>" . htmlspecialchars($caption) . "</figcaption>";
                }
                $html .= "</figure>";
                break;
                
            case 'raw':
                $html .= $data['html'] ?? '';
                break;
        }
    }
    
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page['title']); ?></title>
    
    <style>
        <?php if ($page['builder_type'] === 'grapesjs'): ?>
            <?php echo $css; ?>
        <?php else: ?>
            /* EditorJS default styles */
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                padding: 40px 20px;
            }
            
            h1, h2, h3, h4, h5, h6 {
                margin-top: 1.5em;
                margin-bottom: 0.5em;
                font-weight: 600;
            }
            
            h1 { font-size: 2.5em; }
            h2 { font-size: 2em; }
            h3 { font-size: 1.75em; }
            h4 { font-size: 1.5em; }
            h5 { font-size: 1.25em; }
            h6 { font-size: 1em; }
            
            p {
                margin-bottom: 1em;
            }
            
            ul, ol {
                margin-bottom: 1em;
                padding-left: 2em;
            }
            
            li {
                margin-bottom: 0.5em;
            }
            
            blockquote {
                border-left: 4px solid #e0e0e0;
                padding-left: 1em;
                margin: 1.5em 0;
                font-style: italic;
                color: #666;
            }
            
            blockquote cite {
                display: block;
                margin-top: 0.5em;
                font-size: 0.9em;
                color: #999;
            }
            
            pre {
                background: #f5f5f5;
                padding: 1em;
                border-radius: 4px;
                overflow-x: auto;
                margin-bottom: 1em;
            }
            
            code {
                font-family: 'Courier New', Courier, monospace;
                font-size: 0.9em;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 1em;
            }
            
            table td {
                border: 1px solid #ddd;
                padding: 8px;
            }
            
            figure {
                margin: 1.5em 0;
                text-align: center;
            }
            
            figure img {
                max-width: 100%;
                height: auto;
            }
            
            figcaption {
                margin-top: 0.5em;
                font-size: 0.9em;
                color: #666;
            }
            
            .img-border img {
                border: 1px solid #e0e0e0;
            }
            
            .img-stretched img {
                width: 100%;
            }
            
            .img-bg {
                background: #f5f5f5;
                padding: 15px;
                border-radius: 4px;
            }
        <?php endif; ?>
    </style>
</head>
<body>
    <?php echo $html; ?>
</body>
</html>