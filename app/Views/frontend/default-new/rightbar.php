<style>
    .right-sidebar {
        position: fixed;
        top: 45%;
        right: 0;
        transform: translateY(-50%);
        background: #2c3e50;
        padding: 10px;
        border-radius: 8px 0 0 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        z-index: 9999;
    }

    .right-sidebar a {
        display: block;
        color: #fff;
        margin: 10px 0;
        font-size: 15px;
        text-align: center;
    }

    .right-sidebar a:hover {
        color: #18bc9c;
    }
</style>

<?php
$currentUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$urls = [
    [
        'href' => 'https://auth.hrchr.com/dashboard',
        'title' => 'HOME',
        'icon' => 'fas fa-house'
    ],
    [
        'href' => 'https://hrcsoft.com/',
        'title' => 'HUMAN RESOURCE MANAGEMENT',
        'icon' => 'fas fa-person-booth'
    ],
    [
        'href' => 'https://cloud.hrchr.com',
        'title' => 'CLOUD CABINET',
        'icon' => 'fas fa-cloud'
    ],
    [
        'href' => 'https://hrchr.com/',
        'title' => 'CUSTOMER RELATIONSHIP MANAGEMENT',
        'icon' => 'CRM'
    ],
	[
        'href' => 'https://jobs.hrchr.com/',
        'title' => 'JOBS',
        'icon' => 'fas fa-briefcase'
    ],
	[
        'href' => 'https://news.hrchr.com/',
        'title' => 'NEWS & MAGAZINE',
        'icon' => 'fas fa-newspaper'
    ],
];
?>

<div class="right-sidebar" title="Quick Links">
    <?php foreach ($urls as $url): ?>
        <?php if ($url['href'] !== $currentUrl):
			$hrefURL = $url['href'];
			$titleURL = $url['title'];
			
			switch($url['title']){
				case "HOME": $icon = $url['icon']; echo "<a href=\"$hrefURL\" title=\"$titleURL\"><i class=\"$icon\"></i></a>"; break;
				case "HUMAN RESOURCE MANAGEMENT": echo "<a href=\"$hrefURL\" style=\"text-decoration: none;\" title=\"$titleURL\">HRM</a>"; break;
				case "CLOUD STORAGE": echo "<a href=\"<$hrefURL\" style=\"text-decoration: none;\" title=\"$titleURL\">CSD</a>"; break;
				case "CUSTOMER RELATIONSHIP MANAGEMENT": echo "<a href=\"$hrefURL\" style=\"text-decoration: none;\" title=\"$titleURL\">CRM</a>"; break;
				case "JOBS": echo "<a href=\"$hrefURL\" style=\"text-decoration: none;\" title=\"$titleURL\">JOBS</a>"; break;
				case "NEWS & MAGAZINE": echo "<a href=\"$hrefURL\" style=\"text-decoration: none;\" title=\"$titleURL\">NEWS</a>"; break;
			}
		?>
            <!--<a href="<?//= $url['href']; ?>" title="<?//= $url['title']; ?>"><i class="<?//= $url['icon']; ?>"></i></a>-->
        <?php endif; ?>
    <?php endforeach; ?>
</div>

