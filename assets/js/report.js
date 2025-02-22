
jQuery(document).ready(function($) {
    // 모달 HTML 추가
    $('body').append(`
        <div id="report-modal" style="display:none;" class="report-modal">
            <div class="report-modal-content">
                <h3>게시글 신고</h3>
                <textarea id="report-content" placeholder="신고 사유를 입력해주세요"></textarea>
                <button id="submit-report">신고하기</button>
                <button class="close-modal">닫기</button>
            </div>
        </div>
    `);

    // 신고 버튼 클릭 이벤트
    $(document).on('click', '.report-button', function(e) {
        e.preventDefault();
        const postId = $(this).data('post-id');
        const uid = $(this).data('uid');
        $('#report-modal')
            .data('post-id', postId)
            .data('uid', uid)
            .show();
    });

    // 신고 제출
    $('#submit-report').click(function() {
        const postId = $('#report-modal').data('post-id');
        const uid = $('#report-modal').data('uid');
        const content = $('#report-content').val();
        const currentUrl = window.location.href;

        // 신고 내용에 현재 URL 추가
        const fullContent = content + '\n\n신고된 페이지: ' + currentUrl;

        // Indicator 추가
        const $submitButton = $(this);
        $submitButton.prop('disabled', true).text('처리 중...');

        $.ajax({
            url: kboardReport.ajaxUrl,
            type: 'POST',
            data: {
                action: 'submit_report',
                post_id: postId,
                uid: uid,
                content: fullContent,
                current_url: currentUrl,
                nonce: kboardReport.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('신고가 접수되었습니다.');
                    $('#report-modal').hide();
                    $('#report-content').val('');
                }
            }
        });
    });

    // 모달 닫기 버튼 이벤트 추가
    $('.close-modal').click(function() {
        $('#report-modal').hide();
        $('#report-content').val('');
    });
}); 