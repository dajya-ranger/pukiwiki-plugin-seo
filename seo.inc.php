<?php

/**
 * seo.inc.php
 *
 * SEO対応プラグイン
 *
 * @author		オヤジ戦隊ダジャレンジャー <red@dajya-ranger.com>
 * @copyright	Copyright © 2019-2020, dajya-ranger.com
 * @link		https://dajya-ranger.com/pukiwiki/seo-support-plugin/
 * @example		&seo(description){description};
 * @example		&seo(keywords){keyword,[keyword2],･･･[keyword-n]};
 * @example		&seo(tag){tag,[tag2],･･･[tag-n]};
 * @license		Apache License 2.0
 * @version		0.2.0
 * @since 		0.2.0 2020/10/18 ページキーワードの出力タグ変更、タグページ出力仕様変更
 * @since 		0.1.1 2019/11/04 Facebook及びTwitterのOGPタグ出力対応
 * @since 		0.1.0 2019/11/04 暫定初公開
 *
 */

// Facebook OGPタグ出力設定
define('PLUGIN_SEO_FACEBOOK_OGP', 1);	// 1:有効 0:無効
// Twitter OGPタグ出力設定
define('PLUGIN_SEO_TWITTER_OGP', 1);	// 1:有効 0:無効

// タグページ名（階層構造のトップページ名）
define('PLUGIN_SEO_TAG_PAGE', 'Tag');	// 「Tag」ページ名で階層ページ出力
define('PLUGIN_SEO_TAG_NAME', 'タグ');	// 「Tag」日本語名（キーワード出力）

function plugin_seo_create_tag_page($tags) {
	global $page_title;
										// タグページ上書き
	$contents  = '';
	$contents .= "#setlinebreak(off)\n";
	$contents .= "&seo(description){" . $page_title . PLUGIN_SEO_TAG_NAME
		. "一覧};\n";
	$contents .= "&seo(keywords){" . $page_title . ","
		. PLUGIN_SEO_TAG_PAGE . "," . PLUGIN_SEO_TAG_NAME. "};\n";
	$contents .= "#setlinebreak(on)\n\n";
	$contents .= '[[' . PLUGIN_SEO_TAG_PAGE . ']]' . "一覧\n\n#related";
	page_write(PLUGIN_SEO_TAG_PAGE, $contents);
										// タグページ名セット
	$pages = explode(",", $tags);
										// タグページ作成
	foreach ($pages as $page) {
		// ページ内容編集
		$contents  = '';
		$contents .= "#setlinebreak(off)\n";
		$contents .= "&seo(description){" . PLUGIN_SEO_TAG_NAME . '【'
			. trim($page) . "】利用ページ一覧};\n";
		$contents .= "&seo(keywords){" . $page_title . "," . trim($page) . ","
			. PLUGIN_SEO_TAG_PAGE . "," . PLUGIN_SEO_TAG_NAME. "};\n";
		$contents .= "#setlinebreak(on)\n\n";
		$contents .= '[[' . PLUGIN_SEO_TAG_PAGE . ']]'
			. "利用ページ一覧\n\n#related";
		// タグページの階層構造としてその配下にページを作成（上書き）する
		$page = PLUGIN_SEO_TAG_PAGE . '/' . trim($page);
		page_write($page, $contents);
	}
	return;
}

function plugin_seo_inline() {
	global $head_tags;
										// 引数セット
	$args = func_get_args();
	$name = array_shift($args);
	$content = array_pop($args);
	$error = '';

	// 引数による分岐
	switch( $name ) {
	case 'description':					// ページ概要
		if ( isset($content) && ($content != '') ) {
										// ページヘッダに概要タグ出力
			$head_tags[] = '<meta name="' . $name . '" content="'
				. $content . '" />';
										// Facebook OGPタグ出力
			if (PLUGIN_SEO_FACEBOOK_OGP != 0) {
				// Facebook OGPタグ出力設定でページ概要の場合はタグを出力
				$head_tags[] = '<meta property="og:' . $name
					. '" content="' . $content . '" />';
			}
										// Twitter OGPタグ出力
			if (PLUGIN_SEO_TWITTER_OGP != 0) {
				// Twitter OGPタグ出力設定でページ概要の場合はタグを出力
				$head_tags[] = '<meta name="twitter:' . $name
					. '" content="' . $content . '" />';
			}
		} else {
			// エラーメッセージセット
			$error = $name . 'の内容がありません';
		}
		break;
	case 'keywords':					// ページキーワード
		if ( isset($content) && ($content != '') ) {
			// ページヘッダにページのキーワード（article:tag）タグ出力
			$keywords = explode(",", $content);
			foreach ($keywords as $value) {
				$head_tags[] = '<meta property="article:tag" content="'
					. $value . '" />';
			}
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

	if ( isset($error) && ($error != '') ) {
		return '&seo 引数の指定にエラーがあります: ' . $error;
	}
}

