<?php
set_time_limit(0);
if (!isset($_GET['id'])) {
    echo "bookid呢叼毛?";
    exit();
}
$bookId = $_GET['id'];
$chapterApiUrl = "https://novel.snssdk.com/api/novel/book/directory/list/v1?book_id=$bookId";
$chapterApiResponse = file_get_contents($chapterApiUrl);
if (strlen($bookId) !== 19) {
    echo "这他妈不是正常的bookid，触发:key作废";
    exit();
}
if ($chapterApiResponse === false) {
    echo "api炸了";
    exit();
}
$chapterData = json_decode($chapterApiResponse, true);
if (!isset($chapterData['data']['item_list'])) {
    echo "没有找到";
    exit();
}
$chapterIds = $chapterData['data']['item_list'];
$book_name = $chapterData['data']['book_info']['book_name'];
$txtFile = $book_name . '.txt';
if (file_exists($txtFile)) {
    header("Location: $book_name.txt");
    exit();
}
$mergedContent = fopen("$book_name.txt", "w");
require('simple_html_dom.php');
foreach ($chapterIds as $chapterId) {
    $contentApiUrl = "http://novel.snssdk.com/api/novel/book/reader/full/v1/?aid=2329&item_id=$chapterId";
    $contentApiResponse = file_get_contents($contentApiUrl);
    if ($contentApiResponse !== false) {
        $contentData = json_decode($contentApiResponse, true);
        if (isset($contentData['data']['content'])) {
            $chapterContent = $contentData['data']['content'];
            $html = str_get_html($chapterContent);
            $chapterTitle = $html->find('header div.tt-title', 0)->plaintext;
            fwrite($mergedContent, $chapterTitle . "\n");
            foreach ($html->find('p') as $paragraph) {
                fwrite($mergedContent, $paragraph->plaintext . "\n");
            }
            $html->clear();
        }
    }
}
$fileContents = file_get_contents($txtFile);
$fileContents = str_replace('&#34;', '"', $fileContents);
file_put_contents($txtFile, $fileContents);
fclose($mergedContent);
echo "下载成功!，文件名 $book_name.txt.";
exit();
?>
