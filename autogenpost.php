<?php
/*
Plugin Name: 自動投稿生成
Description: カテゴリ・タグ・画像を選択して投稿を生成
Version: 0.1
Author: Yamanaka Takaya
*/

if (!defined('ABSPATH')) exit;


/* CSS/JS 読み込み */

add_action('admin_enqueue_scripts', 'autopostgen_enqueue_scripts');

function autopostgen_enqueue_scripts($hook)
{
    // このプラグインの管理画面でのみ読み込む
    if ($hook !== 'toplevel_page_autopostgen') {
        return;
    }

    // CSS
    wp_enqueue_style(
        'autopostgen-style',
        plugin_dir_url(__FILE__) . 'css/style.css',
        [],
        '1.0.0'
    );

    // JS
    wp_enqueue_script(
        'autopostgen-script',
        plugin_dir_url(__FILE__) . 'js/autopostgen.js',
        [],
        '1.0.0',
        true
    );
}


/* 管理メニュー */

add_action('admin_menu', 'autopostgen_menu');

function autopostgen_menu()
{

    add_menu_page(
        '自動投稿生成',
        '自動投稿生成',
        'manage_options',
        'autopostgen',
        'autopostgen_page',
        'dashicons-edit'
    );
}


/* 管理画面 */

function autopostgen_page()
{

    include plugin_dir_path(__FILE__) . 'admin-page.php';
}


/* 投稿生成 */

add_action('admin_post_autopostgen_create', 'autopostgen_create');

function autopostgen_create()
{

    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }

    // Nonce検証
    if (!isset($_POST['autopostgen_nonce']) || !wp_verify_nonce($_POST['autopostgen_nonce'], 'autopostgen_create_nonce')) {
        wp_die('セキュリティチェックに失敗しました');
    }


    /* 以前の記事削除 */

    if (isset($_POST['delete_old']) && $_POST['delete_old'] == '1') {

        $posts = get_posts([
            'post_type' => 'post',
            'numberposts' => -1,
            'post_status' => 'any'
        ]);

        foreach ($posts as $p) {
            wp_delete_post($p->ID, true);
        }
    }


    /* 投稿数取得 */

    $post_count = isset($_POST['post_count']) ? intval($_POST['post_count']) : 1;
    $post_count = max(1, min(100, $post_count)); // 1~100の範囲


    /* カテゴリ・タグ・画像取得 */

    $cats = isset($_POST['cats']) ? $_POST['cats'] : [];
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];
    $images = isset($_POST['images']) ? $_POST['images'] : [];


    /* カテゴリIDを取得または作成 */

    $cat_ids = [];
    if (!empty($cats)) {
        foreach ($cats as $cat_name) {
            $term = term_exists($cat_name, 'category');
            if (!$term) {
                $term = wp_insert_term($cat_name, 'category');
            }
            if (!is_wp_error($term)) {
                $cat_ids[] = is_array($term) ? $term['term_id'] : $term['term_id'];
            }
        }
    }


    /* 複数投稿を生成 */

    $created_count = 0;

    for ($i = 0; $i < $post_count; $i++) {

        // タイトルと本文を生成
        $title = '自動生成記事 ' . date('Y-m-d H:i:s');
        $content = autopostgen_generate_content();

        // 投稿作成
        $post_id = wp_insert_post([
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ]);

        if ($post_id && !is_wp_error($post_id)) {

            // カテゴリ設定
            if (!empty($cat_ids)) {
                wp_set_post_categories($post_id, $cat_ids);
            }

            // タグ設定
            if (!empty($tags)) {
                wp_set_post_tags($post_id, $tags);
            }

            // 画像をランダムに1つ選択してアイキャッチ画像に設定
            if (!empty($images)) {
                $random_image = $images[array_rand($images)];
                autopostgen_set_featured_image($post_id, $random_image);
            }

            $created_count++;
        }

        // サーバー負荷軽減のため少し待機
        if ($i < $post_count - 1) {
            usleep(100000); // 0.1秒
        }
    }


    wp_redirect(admin_url('admin.php?page=autopostgen&created=' . $created_count));
    exit;
}


/* コンテンツ生成 */

function autopostgen_generate_content()
{

    $paragraphs = [
        'これは自動生成された記事です。WordPress プラグインによって作成されました。',
        'この記事には様々なカテゴリやタグが設定されています。',
        '画像も自動的に選択され、アイキャッチ画像として設定されます。',
        '投稿の自動生成により、テストコンテンツの作成が簡単になります。',
        'プラグインは複数の投稿を一度に生成することができます。',
        '各投稿には異なる画像がランダムに割り当てられます。',
        'カテゴリとタグは管理画面で簡単に設定できます。',
        'このプラグインを使用することで、サイトのテストが効率的に行えます。'
    ];

    shuffle($paragraphs);
    $selected = array_slice($paragraphs, 0, rand(3, 5));

    return '<p>' . implode('</p><p>', $selected) . '</p>';
}


/* 画像をアイキャッチに設定 */

function autopostgen_set_featured_image($post_id, $image_url)
{

    // 画像URLからファイルパスを取得
    $plugin_url = plugin_dir_url(__FILE__);
    $plugin_path = plugin_dir_path(__FILE__);

    if (strpos($image_url, $plugin_url) === 0) {

        $relative_path = str_replace($plugin_url, '', $image_url);
        $file_path = $plugin_path . $relative_path;

        if (file_exists($file_path)) {

            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $filename = basename($file_path);
            $upload_file = wp_upload_bits($filename, null, file_get_contents($file_path));

            if (!$upload_file['error']) {

                $wp_filetype = wp_check_filetype($filename, null);

                $attachment = [
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name($filename),
                    'post_content' => '',
                    'post_status' => 'inherit'
                ];

                $attach_id = wp_insert_attachment($attachment, $upload_file['file'], $post_id);

                if ($attach_id) {
                    $attach_data = wp_generate_attachment_metadata($attach_id, $upload_file['file']);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    set_post_thumbnail($post_id, $attach_id);
                }
            }
        }
    }
}


/* images取得 */

function autopostgen_images()
{

    $dir = plugin_dir_path(__FILE__) . 'images/';

    if (!is_dir($dir)) {
        return [];
    }

    $list = scandir($dir);

    $imgs = [];

    foreach ($list as $f) {

        if (preg_match('/\.(jpg|png|jpeg|gif|webp)$/i', $f)) {

            $imgs[] = plugin_dir_url(__FILE__) . 'images/' . $f;
        }
    }

    return $imgs;
}
