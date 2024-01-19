<?php

class BlogsModel extends Base {

    function index() {
        $filter = self::execute_filter($_REQUEST, 2);
        $sort = self::execute_sort($_REQUEST, 'all');
        $sort2 = $sort['sort'];

        $count = R::getCell("SELECT COUNT(*) FROM post WHERE status = '1'$filter");
        if ($count > 0) {
            $req = R::getAll("SELECT post.*, " . Base::user('post', 'user_id', 'u') . " FROM post WHERE status = '1'$filter ORDER BY id DESC LIMIT " . $this->message . " OFFSET " . $this->page);
            foreach ($req as $row) {
                $arrayrow[] = $row;
            }
        }

        if (User::$user['level'] > 10) {
            $moderation = R::getCell("SELECT COUNT(*) FROM post WHERE status = '0'");
        }

        SmartySingleton::instance()->assign(array(
            'count' => $count,
            'arrayrow' => $arrayrow,
            'moderation' => $moderation,
            'anetwork' => R::getAll("SELECT * FROM network ORDER BY id ASC"),
            'agameplatforms' => R::getAll("SELECT * FROM gameplatforms ORDER BY id ASC"),
            'acategory' => R::getAll("SELECT * FROM category ORDER BY name_" . Base::locale() . " ASC"),
            'pagenav' => Functions::pagination(Base::home() . '/blogs?' . Cms::page() . '&', $this->page, $count, $this->message)
        ));
        SmartySingleton::instance()->display(SMARTY_TEMPLATE_LOAD . '/templates/modules/blogs/index.tpl');
    }

    function top() {
        $count = R::getCell("SELECT COUNT(*) FROM post WHERE status = '1'$filter");
        if ($count > 0) {
            $req = R::getAll("SELECT post.*, " . Base::user('post', 'user_id', 'u') . " FROM post WHERE status = '1'$filter ORDER BY (views - 0.0) DESC LIMIT " . $this->message . " OFFSET " . $this->page);
            foreach ($req as $row) {
                $arrayrow[] = $row;
            }
        }

        if (User::$user['level'] > 10) {
            $moderation = R::getCell("SELECT COUNT(*) FROM post WHERE status = '0'");
        }

        SmartySingleton::instance()->assign(array(
            'count' => $count,
            'arrayrow' => $arrayrow,
            'moderation' => $moderation,
            'pagenav' => Functions::pagination(Base::home() . '/blogs/top?' . Cms::page() . '&', $this->page, $count, $this->message)
        ));
        SmartySingleton::instance()->display(SMARTY_TEMPLATE_LOAD . '/templates/modules/blogs/top.tpl');
    }

    function moderation() {
        $filter = self::execute_filter($_REQUEST, 2);
        $sort = self::execute_sort($_REQUEST, 'all');
        $sort2 = $sort['sort'];

        $count = R::getCell("SELECT COUNT(*) FROM post WHERE status = '0'$filter");
        if ($count > 0) {
            $req = R::getAll("SELECT post.*, " . Base::user('post', 'user_id', 'u') . " FROM post WHERE status = '0'$filter ORDER BY id DESC LIMIT " . $this->message . " OFFSET " . $this->page);
            foreach ($req as $row) {
                $arrayrow[] = $row;
            }
        }

        SmartySingleton::instance()->assign(array(
            'count' => $count,
            'arrayrow' => $arrayrow,
            'anetwork' => R::getAll("SELECT * FROM network ORDER BY id ASC"),
            'agameplatforms' => R::getAll("SELECT * FROM gameplatforms ORDER BY id ASC"),
            'acategory' => R::getAll("SELECT * FROM category ORDER BY name_" . Base::locale() . " ASC"),
            'pagenav' => Functions::pagination(Base::home() . '/blogs/moderation?' . Cms::page() . '&', $this->page, $count, $this->message)
        ));
        SmartySingleton::instance()->display(SMARTY_TEMPLATE_LOAD . '/templates/modules/blogs/moderation.tpl');
    }

    function id($id) {
        $post = R::getRow("SELECT post.*, " . Base::user('post', 'user_id', 'u') . " FROM post WHERE id = '" . Cms::Int($id) . "'");
        $row = R::getRow("SELECT games.*, " . Base::mygames('games', 'game_id') . ", " . Base::countcart('games', 'game_id') . ", " . Base::countfavgames('games', 'game_id') . ", " . Base::countrequests('games', 'game_id') . " FROM games WHERE id = '" . Cms::Int($post['game_id']) . "' LIMIT 1");

        //пишем статистику        
        if (R::getCell("SELECT COUNT(*) FROM poststat WHERE post_id = '" . $post['id'] . "' AND ip = '" . Cms::Input(Recipe::getClientIP()) . "' AND time < '" . Cms::Int(Cms::realtime() + 3600 * 24) . "'") == 0) {
            //sypex geo
            require_once $_SERVER["DOCUMENT_ROOT"] . '/app/lib/sypex/SxGeo.php';
            $SxGeo = new SxGeo($_SERVER["DOCUMENT_ROOT"] . '/app/lib/sypex/SxGeoCity.dat');
            $stat = $SxGeo->getCityFull(Recipe::getClientIP());
            R::exec("INSERT INTO poststat (post_id, user_id, ip, country, city, browser, region, lat, lng, referer, time) VALUES 
                                        ('" . Cms::Int($post['id']) . "', '" . Cms::Int($post['user_id']) . "', '" . Cms::Input(Recipe::getClientIP()) . "', '" . Cms::Input($stat['country']['name_ru']) . "',
                                        '" . Cms::Input($stat['city']['name_ru']) . "', '" . Cms::Input(Recipe::getBrowser()) . "', '" . Cms::Input($stat['region']['name_ru']) . "',
                                        '" . Cms::Input($stat['city']['lat']) . "', '" . Cms::Input($stat['city']['lon']) . "', '" . Cms::Input(Recipe::getReferer()) . "',
                                        '" . Cms::realtime() . "')");

            R::exec("UPDATE post SET views = '" . Cms::Int($post['views'] + 1) . "' WHERE id = '" . $post['id'] . "'");
        }

        $count = R::getCell("SELECT COUNT(*) FROM comments WHERE refid = '" . Cms::Int($post['id']) . "' AND type = '2'$filter");
        if ($count > 0) {
            $req = R::getAll("SELECT comments.*, " . Base::user('comments', 'user_id') . " FROM comments WHERE refid = '" . Cms::Int($post['id']) . "' AND type = '2'$filter ORDER BY id DESC LIMIT " . $this->message . " OFFSET " . $this->page);
            foreach ($req as $rowss) {
                $arrayrow[] = $rowss;
            }
        }

        if ($row['publisher']) {
            $publisher = explode(',', $row['publisher']);
        }

        if ($row['developer']) {
            $developer = explode(',', $row['developer']);
        }

        SmartySingleton::instance()->assign(array(
            'row' => $row,
            'post' => $post,
            'count' => $count,
            'arrayrow' => $arrayrow,
            'apublisher' => $publisher,
            'adeveloper' => $developer,
            'agamecategory' => R::getAll("SELECT gamecategory.*, " . Base::category('gamecategory', 'refid') . " FROM gamecategory WHERE game_id = '" . $row['id'] . "'"),
            'pagenav' => Functions::pagination(Base::home() . '/blogs/' . $post['id'] . '/' . Functions::esc($post['translate_' . Base::locale()]) . '?', $this->page, $count, $this->message)
        ));
        SmartySingleton::instance()->display(SMARTY_TEMPLATE_LOAD . '/templates/modules/blogs/id.tpl');
    }

    function my() {
        $filter = self::execute_filter($_REQUEST, 2);

        $count = R::getCell("SELECT COUNT(*) FROM post WHERE user_id = '" . Cms::Int(User::$user['id']) . "'$filter");
        if ($count > 0) {
            $req = R::getAll("SELECT post.*, " . Base::user('post', 'user_id', 'u') . " FROM post WHERE user_id = '" . Cms::Int(User::$user['id']) . "'$filter ORDER BY id DESC LIMIT " . $this->message . " OFFSET " . $this->page);
            foreach ($req as $row) {
                $arrayrow2[] = $row['id'];
                $arrayrow[] = $row;
            }
        }

        if (User::$user['level'] > 10) {
            $moderation = R::getCell("SELECT COUNT(*) FROM post WHERE status = '0'");
        }

        SmartySingleton::instance()->assign(array(
            'count' => $count,
            'arrayrow' => $arrayrow,
            'moderation' => $moderation,
            'pagenav' => Functions::pagination(Base::home() . '/blogs/my?' . Cms::page() . '&', $this->page, $count, $this->message)
        ));
        SmartySingleton::instance()->display(SMARTY_TEMPLATE_LOAD . '/templates/modules/blogs/my.tpl');
    }

    function del($id) {
        $row = R::getRow("SELECT * FROM post WHERE id = '" . Cms::Int($id) . "' LIMIT 1");

        Cms::DelFile('files/user/' . $row['user_id'] . '/files/' . $row['youtubeimg']);
        Cms::DelFile('files/user/' . $row['user_id'] . '/files/cover-' . $row['youtubeimg']);
        Cms::DelFile('files/user/' . $row['user_id'] . '/files/' . $row['file']);
        Cms::DelFile('files/user/' . $row['user_id'] . '/files/small-' . $row['file']);
        Cms::DelFile('files/user/' . $row['user_id'] . '/files/cover-' . $row['file']);
        Cms::DelFile('files/user/' . $row['user_id'] . '/files/poster-' . $row['file']);
        Cms::DelFile('files/user/' . $row['user_id'] . '/files/' . $row['mp4']);
        Cms::DelFile('files/user/' . $row['user_id'] . '/files/' . $row['webm']);

        R::exec("DELETE FROM post WHERE id = '" . Cms::Int($row['id']) . "'");
        R::exec("DELETE FROM poststat WHERE post_id = '" . Cms::Int($row['id']) . "'");
        R::exec("DELETE FROM comments WHERE refid = '" . Cms::Int($row['id']) . "' AND type = '2'");

        Functions::redirect(Recipe::getReferer());
    }

    function edit($id) {
        $post = R::getRow("SELECT * FROM post WHERE id = '" . Cms::Int($id) . "' LIMIT 1");
        $row = R::getRow("SELECT games.*, " . Base::mygames('games', 'game_id') . ", " . Base::countcart('games', 'game_id') . ", " . Base::countfavgames('games', 'game_id') . ", " . Base::countrequests('games', 'game_id') . " FROM games WHERE id = '" . Cms::Int($post['game_id']) . "' LIMIT 1");

        SmartySingleton::instance()->assign(array(
            'row' => $row,
            'post' => $post,
        ));
        SmartySingleton::instance()->display(SMARTY_TEMPLATE_LOAD . '/templates/modules/blogs/edit.tpl');
    }

    function execute_filter() {
        
    }

    function execute_sort() {
        
    }

}
