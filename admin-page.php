<?php $images = autopostgen_images(); ?>

<div class="wrap">

    <h1>自動投稿生成</h1>

    <?php if (isset($_GET['created'])) { ?>
        <div class="notice notice-success">
            <p><?php echo intval($_GET['created']); ?>件の投稿を生成しました。</p>
        </div>
    <?php } ?>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="autopostgenForm">

        <input type="hidden" name="action" value="autopostgen_create">
        <?php wp_nonce_field('autopostgen_create_nonce', 'autopostgen_nonce'); ?>


        <table class="form-table">
            <tr>
                <th><label for="post_count">生成する記事数</label></th>
                <td>
                    <input type="number" name="post_count" id="post_count" value="1" min="1" max="100" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="delete_old">以前の記事の削除</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="delete_old" id="delete_old" value="1">
                        以前の記事を全部削除する
                    </label>
                </td>
            </tr>
        </table>


        <h2>投稿カテゴリ</h2>
        <div class="category-section">
            <input type="text" id="catInput" placeholder="カテゴリ名を入力">
            <button type="button" class="button" onclick="addCat()">追加</button>
            <div id="catCandidates" style="margin-top: 10px;"></div>
            <div id="catSelect" style="margin-top: 10px;"></div>
        </div>


        <h2>投稿タグ</h2>
        <div class="tag-section">
            <input type="text" id="tagInput" placeholder="タグ名を入力">
            <button type="button" class="button" onclick="addTag()">追加</button>
            <div id="tagCandidates" style="margin-top: 10px;"></div>
            <div id="tagSelect" style="margin-top: 10px;"></div>
        </div>


        <h2>画像</h2>
        <label>
            <input type="checkbox" id="allImages" checked>
            すべて選択/解除
        </label>

        <div id="images">
            <?php foreach ($images as $img) { ?>
                <label class="img-label">
                    <input type="checkbox" name="images[]" class="imgCheck" checked value="<?php echo esc_url($img); ?>">
                    <img src="<?php echo esc_url($img); ?>" width="120">
                </label>
            <?php } ?>
        </div>


        <br><br>

        <button type="submit" class="button button-primary button-large">
            投稿生成
        </button>

    </form>

</div>