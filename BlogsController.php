<?php

class BlogsController {

    function __construct() {
        $this->model = new BlogsModel();
    }

    function index() {
        SmartySingleton::instance()->assign(array(
            'title' => blogs2,
            'description' => descriptionsiteblogs,
            'page' => 'blogs',
        ));
        $metatags = SmartySingleton::instance()->fetch(SMARTY_TEMPLATE_LOAD . '/templates/system/metatags.tpl');
        Cms::header($metatags);
        $this->model->index();
        Cms::footer();
    }

    function top() {
        SmartySingleton::instance()->assign(array(
            'title' => blogstoptitle,
            'description' => descriptionsiteblogstop,
            'page' => 'blogs',
        ));
        $metatags = SmartySingleton::instance()->fetch(SMARTY_TEMPLATE_LOAD . '/templates/system/metatags.tpl');
        Cms::header($metatags);
        $this->model->top();
        Cms::footer();
    }

    function moderation() {
        if (User::$user['level'] < 10) {
            Functions::redirect(Base::home());
        }
        SmartySingleton::instance()->assign(array(
            'title' => blogsmoderation,
            'page' => 'blogs',
        ));
        $metatags = SmartySingleton::instance()->fetch(SMARTY_TEMPLATE_LOAD . '/templates/system/metatags.tpl');
        Cms::header($metatags);
        $this->model->moderation();
        Cms::footer();
    }

    function add() {
        if (User::$user['id'] == null) {
            Functions::redirect(Base::home());
        }
        $this->model->add();
    }

    function my() {
        if (User::$user['id'] == null) {
            Functions::redirect(Base::home());
        }
        SmartySingleton::instance()->assign(array(
            'title' => blogsmy,
            'page' => 'blogs',
        ));
        $metatags = SmartySingleton::instance()->fetch(SMARTY_TEMPLATE_LOAD . '/templates/system/metatags.tpl');
        Cms::header($metatags);
        $this->model->my();
        Cms::footer();
    }

    function del($id) {
        if (User::$user['id'] == null) {
            Functions::redirect(Base::home());
        }
        if (User::$user['level'] < 10) {
            if (R::count("post", "id = ? AND user_id = ?", array($id, Cms::Int(User::$user['id']))) == 0) {
                Functions::redirect(Base::home());
            }
        } else if (User::$user['level'] == 10) {
            if (R::count("post", "id = ?", array($id)) == 0) {
                Functions::redirect(Base::home());
            }
        }
        $this->model->del($id);
    }

    function edit($id) {
        if (User::$user['id'] == null) {
            Functions::redirect(Base::home());
        }
        if (User::$user['level'] < 10) {
            if (R::count("post", "id = ? AND user_id = ?", array($id, Cms::Int(User::$user['id']))) == 0) {
                Functions::redirect(Base::home());
            }
        } else if (User::$user['level'] > 10) {
            if (R::count("post", "id = ?", array($id)) == 0) {
                Functions::redirect(Base::home());
            }
        }
        $row = R::getRow("SELECT * FROM post WHERE id = '" . Cms::Int($id) . "'");
        SmartySingleton::instance()->assign(array(
            'title' => Functions::esc($row['name_' . Base::locale()]),
            'page' => 'blogs',
        ));
        $metatags = SmartySingleton::instance()->fetch(SMARTY_TEMPLATE_LOAD . '/templates/system/metatags.tpl');
        Cms::header($metatags);
        $this->model->edit($id);
        Cms::footer();
    }

    function id($id, $tr) {
        if (R::count("post", "id = ? AND translate_" . Base::locale() . " = ?", array(Cms::Int($id), Cms::Input($tr))) == 0) {
            Functions::redirect(Base::home());
        }
        $row = R::getRow("SELECT * FROM post WHERE id = '" . Cms::Int($id) . "'");
        $user = R::getRow("SELECT * FROM users WHERE id = '" . Cms::Int($row['user_id']) . "'");

        if ($row['file']) {
            $img = Cms::setup('home') . '/files/user/' . $row['user_id'] . '/files/small-' . $row['file'];
            $i = getimagesize('files/user/' . $row['user_id'] . '/files/small-' . $row['file']);
        } else if ($row['youtubeimg']) {
            $img = Cms::setup('home') . '/files/user/' . $row['user_id'] . '/files/cover-' . $row['youtubeimg'];
            $i = getimagesize('files/user/' . $row['user_id'] . '/files/cover-' . $row['file']);
        }
        SmartySingleton::instance()->assign(array(
            'title' => Functions::esc($row['name_' . Base::locale()]) . ' | ' . Cms::setup('watermark'),
            'keywords' => Functions::seokeywords(Functions::esc($row['name_' . Base::locale()]), 5),
            'description' => Functions::truncate(strip_tags(Functions::esc($row['text_' . Base::locale()])), 1000),
            'time' => Functions::esc($row['time']),
            'author' => Functions::esc($user['firstname']) . ' ' . Functions::esc($user['lastname']),
            'img' => $img,
            'width' => $i[0] ? $i[0] : 640,
            'height' => $i[1] ? $i[1] : 360,
            'page' => 'blogs',
        ));
        $metatags = SmartySingleton::instance()->fetch(SMARTY_TEMPLATE_LOAD . '/templates/system/metatags.tpl');
        Cms::header($metatags);
        $this->model->id($id);
        Cms::footer();
    }

}
