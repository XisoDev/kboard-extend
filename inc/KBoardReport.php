<?php

class KBoardReport {
    private string $plugin_name = 'kboard-extend';
    private static $instance;

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('init', [$this, 'registerPostType']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_menu', [$this, 'addSettingsMenu']);
        add_action('admin_menu', function () {
            add_submenu_page('kboard_dashboard',
                '신고된 게시글 관리',
                __('신고된 게시글', 'kboard'),
                'manage_kboard',
                'edit.php?post_type=kboard_report');
        });

        add_action('wp_ajax_submit_report', [$this, 'handleReport']);
        add_action('wp_ajax_nopriv_submit_report', [$this, 'handleReport']);
        add_filter('kboard_content', [$this, 'addReportButton'], 10, 2);

        // 관리자 페이지 커스텀 컬럼 추가
        add_filter('manage_kboard_report_posts_columns', [$this, 'addCustomColumns']);
        add_action('manage_kboard_report_posts_custom_column', [$this, 'renderCustomColumns'], 10, 2);
    }

    // Custom Post Type 등록
    public function registerPostType() {
        register_post_type('kboard_report', [
            'labels' => [
                'name' => '신고된 게시글',
                'singular_name' => '신고된 게시글'
            ],
            'public' => false, // 메뉴바에 나타나지 않도록 비공개로 설정
            'show_ui' => true, // 관리 화면에서 사용할 수 있도록 설정
            'show_in_menu' => false,
            'has_archive' => true,
            'menu_icon' => 'dashicons-warning',
            'supports' => ['title', 'editor']
        ]);
    }

    public function enqueueScripts() {
        wp_enqueue_style('kboard-report', plugins_url($this->plugin_name . '/assets/css/report.css'));
        wp_enqueue_script('kboard-report', plugins_url($this->plugin_name . '/assets/js/report.js'), ['jquery'], '1.0.0', true);
        wp_localize_script('kboard-report', 'kboardReport', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kboard-report-nonce')
        ]);
    }

    public function addSettingsMenu() {
        add_options_page(
            'KBoard 신고 설정',
            'KBoard 신고',
            'manage_options',
            'kboard-report',
            [KBoardReportSettings::class, 'renderSettingsPage']
        );
    }

    public function addReportButton($content, $uid) {
        $button = '<div style="margin-top:20px; padding-top:20px; text-align: right; border-top:1px solid #eee;">
            <button class="report-button" data-uid="'.$uid.'" data-post-id="' . get_the_ID() . '" style="padding: 6px 10px; background-color: #d9534f; color: white; border: none; border-radius: 4px; font-size: 14px; cursor: pointer;">신고하기</button>
            </div>';
        return $content . $button;
    }

    public function handleReport() {
        check_ajax_referer('kboard-report-nonce', 'nonce');

        $post_id = intval($_POST['post_id']);
        $uid = intval($_POST['uid']);
        $content = sanitize_textarea_field($_POST['content']);
        $current_url = esc_url_raw($_POST['current_url']);

        // 신고된 게시글 생성
        $report_post = wp_insert_post([
            'post_type' => 'kboard_report',
            'post_title' => sprintf('[게시글 #%d] %s (신고됨)', $uid, get_the_title($post_id)),
            'post_content' => $content,
            'post_status' => 'publish'
        ]);

        // 메타 데이터 저장
        update_post_meta($report_post, 'reported_post_id', $post_id);
        update_post_meta($report_post, 'reported_post_url', $current_url);
        update_post_meta($report_post, 'kboard_uid', $uid);

        // 이메일 발송
        $this->sendReportEmail($post_id, $content);

        wp_send_json_success();
    }

    private function sendReportEmail($post_id, $content) {
        $emails = explode(',', get_option('kboard_report_emails'));
        $emails = array_map('trim', $emails);

        if (empty($emails)) return;

        $subject = '[게시글 신고] ' . get_the_title($post_id);
        $message = sprintf(
            "신고된 게시글: %s\n\n신고 내용:\n%s\n\n게시글 링크: %s",
            get_the_title($post_id),
            $content,
            get_permalink($post_id)
        );

        foreach ($emails as $email) {
            wp_mail($email, $subject, $message);
        }
    }

    /**
     * 관리자 페이지 커스텀 컬럼 추가
     */
    public function addCustomColumns($columns) {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            if ($key === 'title') {
                $new_columns[$key] = $value;
                $new_columns['kboard_uid'] = '게시글 번호';
                $new_columns['reported_url'] = '신고된 페이지';
            } else {
                $new_columns[$key] = $value;
            }
        }
        
        return $new_columns;
    }

    /**
     * 커스텀 컬럼 내용 렌더링
     */
    public function renderCustomColumns($column, $post_id) {
        switch ($column) {
            case 'kboard_uid':
                $uid = get_post_meta($post_id, 'kboard_uid', true);
                echo $uid ? '#' . esc_html($uid) : '-';
                break;
                
            case 'reported_url':
                $url = get_post_meta($post_id, 'reported_post_url', true);
                if ($url) {
                    printf(
                        '<a href="%s" target="_blank" class="button button-small">%s</a>',
                        esc_url($url),
                        '페이지 보기'
                    );
                } else {
                    echo '-';
                }
                break;
        }
    }
}