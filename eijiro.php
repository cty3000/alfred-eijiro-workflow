<?php

require_once('libs/workflows.php');
require_once('libs/simple_html_dom.php');

$wf = new Workflows();

// Alfredに入力されたクエリを取得
$query = $argv[1];

// クエリを用いてサーバへGETリクエスト
$ch = curl_init("http://eow.alc.co.jp/search?q=".urlencode($query));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
curl_setopt($ch, CURLOPT_USERAGENT, 'eijiro_alfred_workflow');
$html = curl_exec($ch);
curl_close($ch);

// HTMLをパース
$dom = str_get_html($html);
$item_number = $dom->find('#itemsNumber', 0)->first_child()->plaintext;
if ($item_number != '0') {
    // 検索結果が0件でないとき
    $child = $dom->find('#resultsList', 0)->first_child()->first_child();
    $int = 1;
    while ($child) {
        // 見出し語を取得
        $title = $child->find('.midashi', 0)->plaintext;
        // 文頭文末の空白を除去
        $title = trim($title);
        // 連続する空白を1つにする
        $title = preg_replace('/\s+/u', ' ', $title);
        // 説明文を取得
        $subtitle = $child->children(1)->plaintext;
        // Alfredの結果リストに追加
        $wf->result($int.'.'.time(), "$title", "$title", "$subtitle", null);
        $child = $child->next_sibling();
        $int++;
    }
}

$results = $wf->results();
if (count( $results ) == 0):
    // 結果が0件だったとき
    $wf->result('eijironotfound', "$query", 'No results', 'No results found for '.$query, 'icon.png');
endif;

echo $wf->toxml();

?>
