<?php

/**
 * Helper function to safely build and sanitize an HTML anchor tag.
 */
function buildLinkHTML(array $item, string $defaultLabel, string $activeSection): string {
    $url = $item['url'] ?? '#';
    $label = $item['label'] ?? $defaultLabel;
    $title = isset($item['title']) ? ' title="' . htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') . '"' : '';
    
    if ($defaultLabel <> $activeSection) {
        return '<a class="nav-link" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . $title . '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
    }
    else {
        return '<b>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</b>';
    }
}

/**
 * Main function to render the subnavigation.
 */
function writeSubnav(array $nav, string $topSection, string $thisSection): void {
    // If the section doesn't exist, exit early to save processing
    if (!isset($nav[$topSection])) {
        return;
    }

    echo '<div class="side-nav col-12 col-sm-6 col-md-4 col-lg-3 ms-sm-5 mb-5 card shadow-sm p-3" style="float:     right; ">' . PHP_EOL;
    echo '<h5>In this section</h5>' . PHP_EOL;
    echo buildLinkHTML($nav[$topSection], $topSection, $thisSection);
    echo '<ul>' . PHP_EOL;
    
    // 1. Output the parent link
    
    // 2. Output the children links if they exist
    if (isset($nav[$topSection]['children']) && is_array($nav[$topSection]['children'])) {
        foreach ($nav[$topSection]['children'] as $childKey => $childData) {
            echo '    <li>' . buildLinkHTML($childData, $childKey, $thisSection) . '</li>' . PHP_EOL;
        }
    }
    
    echo '</ul>' . PHP_EOL;
    echo '</div>' . PHP_EOL;

}
?>