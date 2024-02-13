<?PHP
function generateMetaTags() {
    global $conf, $metatags;
    if (!$metatags) { $metatags = []; }
    $merged_tags = array_merge($conf['metadata'], $metatags);
    $meta_tags = [
        '<meta name="description" content="' . htmlspecialchars($merged_tags['description']) . '">',
        '<meta property="og:site_name" content="' . htmlspecialchars($merged_tags['og_site_name']) . '">',
        '<meta property="og:title" content="' . htmlspecialchars($merged_tags['og_title']) . '">',
        '<meta property="og:url" content="' . htmlspecialchars($merged_tags['og_url']) . '">',
        '<meta property="og:type" content="' . htmlspecialchars($merged_tags['og_type']) . '">',
        '<meta property="og:description" content="' . htmlspecialchars($merged_tags['og_description']) . '">',
        '<meta property="og:image" content="' . htmlspecialchars($merged_tags['og_image']) . '">',
        '<meta name="twitter:card" content="' . htmlspecialchars($merged_tags['twitter_card']) . '">',
        '<meta name="twitter:site" content="' . htmlspecialchars($merged_tags['twitter_site']) . '">',
        '<meta name="twitter:title" content="' . htmlspecialchars($merged_tags['twitter_title']) . '">',
        '<meta name="twitter:description" content="' . htmlspecialchars($merged_tags['twitter_description']) . '">',
        '<meta name="twitter:image" content="' . htmlspecialchars($merged_tags['twitter_image']) . '">',
    ];
    echo implode("\n", $meta_tags);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="description" content="<?=$tpl['head.title']?>">
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title><?=$tpl['head.title']?></title>
	<?=generateMetaTags()?>
	<link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
	<link rel="manifest" href="/assets/icons/site.webmanifest">
	<link rel="mask-icon" href="/assets/icons/safari-pinned-tab.svg" color="#5bbad5">
	<link rel="shortcut icon" href="/assets/icons/favicon.ico">

	<meta name="msapplication-TileColor" content="#da532c">
	<meta name="msapplication-config" content="/assets/icons/browserconfig.xml">
	<meta name="theme-color" content="#ffffff">

	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.1/css/bootstrap.min.css" integrity="sha512-siwe/oXMhSjGCwLn+scraPOWrJxHlUgMBMZXdPe2Tnk3I0x3ESCoLz7WZ5NTH6SZrywMY+PB1cjyqJ5jAluCOg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.4/font/bootstrap-icons.min.css" integrity="sha512-yU7+yXTc4VUanLSjkZq+buQN3afNA4j2ap/mxvdr440P5aW9np9vIr2JMZ2E5DuYeC9bAoH9CuCR7SJlXAa4pg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/axios@0.27.2/dist/axios.min.js" integrity="sha256-43O3ClFnSFxzomVCG8/NH93brknJxRYF5tKRij3krg0=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/web3@1.8.0/dist/web3.min.js" integrity="sha256-1TLx50r3wQ52OlAm5jSfpTwbN9MJ51NysfLmsXmUPDk=" crossorigin="anonymous"></script>	
	<script src="https://cdn.jsdelivr.net/npm/web3modal@1.9.7/dist/index.js" integrity="sha256-3wjBHN9eOlwWngj4P0pLTdksADMscLP9psYXwLvQfrw=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/@walletconnect/web3-provider@1.8.0/dist/umd/index.min.js" integrity="sha256-OfIB2zuUgSYcwWYMPC+YgPsJ70TB5f5dni/Z3rgYvRA=" crossorigin="anonymous"></script>

	<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.2.0/dist/style.min.css" rel="stylesheet" type="text/css">
	<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.2.0/dist/umd/simple-datatables.min.js" type="text/javascript"></script>

	<link rel="stylesheet" href="/assets/css/style.css">
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/inc/jsconfig.php'); ?>
</head>
<body>
