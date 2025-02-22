<?php
class KBoardReportSettings {
    public static function registerSettings() {
        add_option('kboard_report_emails', '');
        
        register_setting('kboard_report_options', 'kboard_report_emails');
    }
    
    public static function renderSettingsPage() {
        ?>
        <div class="wrap">
            <h2>KBoard 신고 설정</h2>
            <form method="post" action="options.php">
                <?php settings_fields('kboard_report_options'); ?>
                <table class="form-table">
                    <tr>
                        <th>알림 이메일 주소</th>
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
        </div>
        <?php
    }
} 