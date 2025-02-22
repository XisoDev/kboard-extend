<?php
class KBoardReportSettings {
    public static function registerSettings() {
        add_option('kboard_report_emails', '');

        register_setting('kboard_report_options', 'kboard_report_emails', array(
            'type' => 'string',
            'sanitize_callback' => ['KBoardReportSettings', 'sanitizeEmails'], // 수정된 부분
        ));
    }

    public static function sanitizeEmails($input) {
        $input = sanitize_text_field($input);
        $emails = array_map('trim', explode(',', $input));
        $valid_emails = array_filter($emails, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });
        return implode(',', $valid_emails);
    }

    public static function renderSettingsPage() {
        ?>
        <div class="wrap">
            <h2>KBoard 확장 플러그인 설정</h2>
            <section>
                <h3>신고하기 기능 설정</h3>
                <form method="post" action="options.php">
                    <?php settings_fields('kboard_report_options'); ?>
                    <table class="form-table">
                        <tr>
                            <th>게시글 신고알림 이메일 주소</th>
                            <td>
                                <input type="text" name="kboard_report_emails"
                                       value="<?php echo esc_attr(get_option('kboard_report_emails')); ?>"
                                       class="regular-text" />
                                <p class="description">여러 이메일은 쉼표(,)로 구분하여 입력하세요.</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </section>
        </div>
        <?php
    }
}
