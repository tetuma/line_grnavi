<?php
/**
 * index.php
 *
 * @since 2016/08/04
 */
require_once './Hpepper.class.php';

/**
 * エスケープ
 * @param string $string
 * @return string
 */
function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
}

// 都道府県取得
$prefs = Hpepper::getPref();

// レストラン検索
$restaurants = Hpepper::getRestaurants();
?>
<!DOCTYPE HTML>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>ホットペッパーAPIテスト（アホでも設置するだけで動く）</title>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    </head>
    <body>

        <div class="container">

            <h1>ホットペッパーAPIサンプル</h1>

            <div class="row">
                <div class="col-md-6">
                    <form method="get">
                        <div class="form-group">
                            <label for="service_area">都道府県</label>
                            <select class="form-control" name="service_area" id="service_area">
                                <option value="">都道府県</option>
                                <?php foreach ($prefs->results->service_area as $pref) : ?>
                                    <?php if ($pref->code == filter_input(INPUT_GET, 'service_area')): ?>
                                        <option value="<?= h($pref->code); ?>" selected="selected">
                                            <?= h($pref->name); ?>
                                        </option>
                                    <?php else: ?>
                                        <option value="<?= h($pref->code); ?>">
                                            <?= h($pref->name); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name_kana">店名 or ふりがな</label>
                            <input class="form-control" type="text" name="name_kana" id="name_kana" value="<?= h(filter_input(INPUT_GET, 'name_kana')); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="keyword">フリーワード</label>
                            <input class="form-control" type="text" name="keyword" id="keyword" value="<?= h(filter_input(INPUT_GET, 'keyword')); ?>" />
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">検索</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php if (isset($restaurants->results)): ?>
                <?php if (isset($restaurants->results->error)): ?>
                    <div>
                        <?php foreach ($restaurants->results->error as $err) : ?>
                            <h2><?= $err->code; ?></h2>
                            <p><?= $err->message; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>name</th>
                                <th>category</th>
                                <th>address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($restaurants->results->shop as $rest) : ?>
                                <tr>
                                    <td><?= h($rest->id); ?></td>
                                    <td>
                                        <a href="<?= h($rest->urls->pc); ?>"><?= h($rest->name); ?></a>
                                    </td>
                                    <td><?= h($rest->genre->name); ?></td>
                                    <td><?= h($rest->address); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <ul class="pagination pagination-sm no-margin">
                        <?= Hpepper::pagination($restaurants->results->results_available); ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </body>
</html>
