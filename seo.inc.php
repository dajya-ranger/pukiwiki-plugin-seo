<?php

/**
 * seo.inc.php
 *
 * SEO対応プラグイン
 *
 * @author		オヤジ戦隊ダジャレンジャー <red@dajya-ranger.com>
 * @copyright	Copyright © 2019, dajya-ranger.com
 * @link		https://dajya-ranger.com/pukiwiki/seo-support-plugin/
 * @example		&seo(description){description};
 * @example		&seo(keywords){keyword,[keyword2],･･･[keyword-n]};
 * @example		&seo(tag){tag,[tag2],･･･[tag-n]};
 * @license		Apache License 2.0
 * @version		0.1.1
 * @since 		0.1.1 2019/11/04 Facebook及びTwitterのOGPタグ出力対応
 * @since 		0.1.0 2019/11/04 暫定初公開
 *
 */

// Facebook OGPタグ出力設定
define('PLUGIN_SEO_FACEBOOK_OGP', 1);	// 1:有効 0:無効

// Twitter OGPタグ出力設定
define('PLUGIN_SEO_TWITTER_OGP', 1);	// 1:有効 0:無効

// タグページ名（階層構造のトップページ名）
define('PLUGIN_SEO_TAG_PAGE', '');		// タグページを出力しない（初期値）
//define('PLUGIN_SEO_TAG_PAGE', 'Tag');	// 「Tag」ページ名で階層構造でタグページを出力する

function plugin_seo_create_tag_page($tags) {
										// タグページの存在確認
	if (! is_page(PLUGIN_SEO_TAG_PAGE) ) {
		// ページが存在しない場合はページを作成する
		page_write(PLUGIN_SEO_TAG_PAGE, '#related');
	}
										// タグページ名セット
	$pages = explode(",", $tags);
										// 作成するタグページ内容
	$postdata = '[[' . PLUGIN_SEO_TAG_PAGE . ']]' . "利用ページ一覧\n\n" . '#related';
										// タグページ作成
	foreach ($pages as $page) {
		// タグページの階層構造としてその配下にページを作成する
		$page = PLUGIN_SEO_TAG_PAGE . '/' . $page;
		if (! is_page($page) ) {
			// ページが存在しない場合はページを作成する
			page_write($page, $postdata);
		}
	}
	return '';
}

function plugin_seo_inline() {
	global $head_tags;
										// 引数セット
	$args = func_get_args();
	$name = array_shift($args);
	$content = array_pop($args);

	// 引数による分岐
	switch( $name ) {
	case 'description':					// ページ概要
	case 'keywords':					// ページキーワード
		if ( isset($content) && ($content != '') ) {
			// ページヘッダにページの概要タグ出力
			$head_tags[] = '<meta name="' . $name . '" content="' . $content . '" />';
										// Facebook OGPタグ出力
			if ( (PLUGIN_SEO_FACEBOOK_OGP != 0) && ($name == 'description') ) {
				// Facebook OGPタグ出力設定でページ概要の場合はタグを出力
				$head_tags[] = '<meta property="og:' . $name . '" content="' . $content . '" />';
			}
										// Twitter OGPタグ出力
			if ( (PLUGIN_SEO_TWITTER_OGP != 0) && ($name == 'description') ) {
				// Twitter OGPタグ出力設定でページ概要の場合はタグを出力
				$head_tags[] = '<meta property="twitter:' . $name . '" content="' . $content . '" />';
			}
			return '';
		} else {
			// エラーメッセージセット
			$error = $name . 'の内容がありません';
		}
		break;
	case 'tag':							// ページタグ
		if ( isset($content) && ($content != '') ) {
			// タグページを出力する
			// ※自ページでブラケットによるタグページのリンクが必要
			if (PLUGIN_SEO_TAG_PAGE != '') {
				// タグを出力するタグページ名の指定がある場合はページを出力する
				plugin_seo_create_tag_page($content);
				return '';
			} else {
				// タグ出力指定があっても、タグページ名の設定がない場合はスルー
				return '';
			}
		} else {
			// エラーメッセージセット
			$error = $name . 'の内容がありません';
		}
		break;
	default:
		// パラメータエラーがある場合
		$error = $name;
	}

	return '&seo 引数の指定にエラーがあります: ' . $error;
}

?>
