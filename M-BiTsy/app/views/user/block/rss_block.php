<?php
if ($_SESSION['loggedin'] == true) {
    // Enter Rss Feed Url eg http://localhost/M-BiTsy/rss?dllink=1
    $feedUrl = "";

    Style::block_begin(Lang::T("RSS"));
    if (!$feedUrl) { ?>
        <p class="text-center"><small>This would need editing with an rss feed of your choice.</small></p> <?php
    } else {
        $domOBJ = new DOMDocument();
        $domOBJ->load($feedUrl);
        $content = $domOBJ->getElementsByTagName("item");
        $i=0;
        foreach ($content as $data) {
            $title = $data->getElementsByTagName("title")->item(0)->nodeValue;
            $link = $data->getElementsByTagName("link")->item(0)->nodeValue;
            echo "<small>".CutName($title, 20)." <br> <a href='$link'>".CutName($link, 20)."</a></small><br><br>";
            // Limit 5 Results
            if (++$i == 5) break;
        }
    }

    Style::block_end();
}